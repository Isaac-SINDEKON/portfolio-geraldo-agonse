<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert les images stockees par le backend, a cote des pages.
 *
 * Les fichiers sont televerses par le backend (dossier "uploads" du disque
 * public), mais les pages sont rendues par le frontend. Sans ce relais, chaque
 * image du site pointait vers le backend lui-meme, sur un autre port : le
 * navigateur devait donc joindre le backend par son URL propre, et les images
 * disparaissaient silencieusement des que cette URL ne lui convenait plus
 * (site ouvert depuis un autre poste ou un telephone, backend arrete, ou page
 * en HTTPS servie avec une image en HTTP, bloquee par la regle de contenu
 * mixte).
 *
 * En servant les images depuis le meme site que les pages, l'image suit
 * toujours la page : meme origine, meme schema, aucun acces direct au backend
 * depuis le navigateur.
 */
class MediaController extends Controller
{
    /** Dossier unique ou le backend depose tous les televersements. */
    protected const DOSSIER = 'uploads/';

    public function __invoke(Request $request, string $path): Response
    {
        $chemin = $this->cheminAutorise($path);

        if ($chemin === null) {
            return $this->introuvable();
        }

        try {
            $reponse = Http::timeout(30)
                ->connectTimeout(5)
                ->get(ApiClient::storageBaseUrl().'/'.$chemin);
        } catch (\Throwable) {
            // Backend injoignable : l'image ne s'affichera pas, mais la page
            // public elle-meme reste servie normalement.
            return $this->introuvable();
        }

        if (! $reponse->successful()) {
            return $this->introuvable();
        }

        return response($reponse->body(), 200, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/octet-stream',
            // Les noms de fichiers sont generes par le backend et ne sont jamais
            // reutilises : le fichier affiche peut etre mis en cache longtemps.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Length' => (string) strlen($reponse->body()),
        ]);
    }

    /**
     * Ne retient que le nom d'un fichier reellement televerse par le backend.
     *
     * Le chemin vient de l'URL : il est donc saisi par le visiteur. Toute
     * tentative de remonter hors du dossier "uploads" est rejetee, et un chemin
     * inexistant ne provoque aucune requete vers le backend.
     */
    protected function cheminAutorise(string $path): ?string
    {
        $chemin = trim($path);

        if ($chemin === ''
            || str_contains($chemin, '..')
            || str_contains($chemin, "\0")
            || str_contains($chemin, '\\')) {
            return null;
        }

        $chemin = ltrim($chemin, '/');

        if (! str_starts_with($chemin, self::DOSSIER) || ! str_contains($chemin, '.')) {
            return null;
        }

        return $chemin;
    }

    /** Le navigateur recoit un 404 : il basculera alors sur son repli local. */
    protected function introuvable(): Response
    {
        return response('', 404, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}

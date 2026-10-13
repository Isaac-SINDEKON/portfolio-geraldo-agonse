<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert les images stockees sur le disque public, a cote des pages.
 *
 * Les fichiers sont televerses dans le dossier "uploads" du disque public,
 * mais les pages pointent vers la route /media plutot que directement vers
 * /storage. Servir l'image depuis la meme application que les pages garantit
 * la meme origine et le meme schema : un site en HTTPS ne se retrouve pas
 * avec une image en HTTP bloquee par la regle de contenu mixte.
 */
class MediaController extends Controller
{
    /** Dossier unique ou sont deposees toutes les images televersees. */
    protected const DOSSIER = 'uploads/';

    public function __invoke(Request $request, string $path): Response
    {
        $chemin = $this->cheminAutorise($path);

        if ($chemin === null) {
            return $this->introuvable();
        }

        $disque = Storage::disk('public');

        if (! $disque->exists($chemin)) {
            return $this->introuvable();
        }

        return response()->file($disque->path($chemin), [
            'Content-Type' => $disque->mimeType($chemin) ?: 'application/octet-stream',
            // Les noms de fichiers sont generes et ne sont jamais reutilises :
            // le fichier affiche peut donc etre mis en cache longtemps.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Ne retient que le nom d'un fichier reellement stocke.
     *
     * Le chemin vient de l'URL : il est donc saisi par le visiteur. Toute
     * tentative de remonter hors du dossier "uploads" est rejetee, et un chemin
     * inexistant ne peut pas sortir du stockage.
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

<?php

namespace App\Services;

/**
 * Utilitaire d'URL des images stockees.
 *
 * Le site, l'administration et le stockage vivent dans la meme application :
 * les images sont sur le disque public et servies par la route /media. Ce
 * service ne fait donc plus aucun appel HTTP, il se contente de traduire un
 * chemin (relatif ou ancienne URL de stockage) en URL de meme origine.
 *
 * Le nom historique est conserve : les vues et les tests s'y referent.
 */
class ApiClient
{
    /** Prefixe public sous lequel l'application sert les images stockees. */
    public const MEDIA_PREFIX = '/media/';

    /**
     * Racine publique des fichiers stockes.
     *
     * Sert de reference pour reconnaitre une URL deja complete et la reduire au
     * relais /media. En monolithique, seul le stockage local est concerne.
     */
    public static function storageBaseUrl(): string
    {
        return rtrim((string) config('services.storage.url'), '/');
    }

    /**
     * URL d'une image, servie par l'application et donc de meme origine que la page.
     *
     * L'API historique renvoyait les images sous deux formes : un chemin relatif
     * ("uploads/x.jpg") pour les reglages, et l'URL complete du stockage pour la
     * galerie et les photos de temoignages. Les deux sont reduites ici au meme
     * chemin. Une URL qui ne designe pas le stockage (CDN, autre hebergeur) est
     * en revanche respectee telle quelle.
     */
    public static function imageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        // Une image deja presente dans la page n'a besoin d'aucun relais.
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        if (self::estUrlAbsolue($path) && ! self::appartientAuStockage($path)) {
            return $path;
        }

        $chemin = self::cheminStocke($path);

        if ($chemin === null) {
            // URL absolue du stockage dont le prefixe n'a pas ete reconnu : on la
            // laisse en place plutot que de pointer vers une adresse devinee.
            return self::estUrlAbsolue($path) ? $path : null;
        }

        return url(self::MEDIA_PREFIX.$chemin);
    }

    /** Une URL absolue, quel que soit le schema. */
    protected static function estUrlAbsolue(string $path): bool
    {
        return preg_match('#^[a-z][a-z0-9+.-]*://#i', $path) === 1;
    }

    /** L'URL designe-t-elle le dossier de fichiers de l'application ? */
    protected static function appartientAuStockage(string $url): bool
    {
        $base = parse_url(self::storageBaseUrl()) ?: [];
        $cible = parse_url($url) ?: [];

        if (($base['scheme'] ?? null) !== ($cible['scheme'] ?? null)
            || ($base['host'] ?? null) !== ($cible['host'] ?? null)) {
            return false;
        }

        $prefixe = rtrim((string) ($base['path'] ?? ''), '/').'/';

        return $prefixe !== '/' && str_starts_with((string) ($cible['path'] ?? ''), $prefixe);
    }

    /**
     * Chemin du fichier stocke, a partir d'un chemin relatif ou d'une ancienne
     * URL complete. Renvoie null si le prefixe de stockage n'est pas reconnu.
     */
    protected static function cheminStocke(string $path): ?string
    {
        if (! self::estUrlAbsolue($path)) {
            return ltrim($path, '/');
        }

        $prefixe = rtrim((string) (parse_url(self::storageBaseUrl(), PHP_URL_PATH) ?: ''), '/').'/';
        $chemin = (string) (parse_url($path, PHP_URL_PATH) ?: '');

        if ($prefixe === '/' || $prefixe === '//') {
            // Si le prefixe de storage n'est pas identifiable (config manquante/prod),
            // on tente d'extraire le chemin apres /storage/ pour rester stable.
            if (preg_match('#/storage/(.+)$#', $chemin, $m)) {
                return ltrim($m[1], '/');
            }

            return ltrim($chemin, '/');
        }

        if (! str_starts_with($chemin, $prefixe)) {
            if (preg_match('#/storage/(.+)$#', $chemin, $m)) {
                return ltrim($m[1], '/');
            }

            return null;
        }

        return ltrim(substr($chemin, strlen($prefixe)), '/');
    }
}

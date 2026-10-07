<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ApiClient
{
    public function token(): ?string
    {
        return Session::get('admin_token');
    }

    public function isAuthenticated(): bool
    {
        return $this->token() !== null;
    }

    public function setToken(?string $token): void
    {
        if ($token === null) {
            Session::forget('admin_token');
            Session::forget('admin_user');

            return;
        }

        Session::put('admin_token', $token);
    }

    protected function request(): PendingRequest
    {
        $request = Http::baseUrl(config('services.backend.api_url'))
            ->timeout(30)
            ->connectTimeout(5)
            ->acceptJson();

        if ($token = $this->token()) {
            $request = $request->withToken($token);
        }

        return $request;
    }

    public function get(string $endpoint, array $query = []): ?array
    {
        try {
            $response = $this->request()->get(ltrim($endpoint, '/'), $query);
        } catch (\Throwable $e) {
            Log::error('API GET '.$endpoint.' : '.$e->getMessage());

            return null;
        }

        return $this->handle($response, $endpoint);
    }

    public function post(string $endpoint, array $payload = []): ?array
    {
        try {
            $response = $this->request()->post(ltrim($endpoint, '/'), $payload);
        } catch (\Throwable $e) {
            Log::error('API POST '.$endpoint.' : '.$e->getMessage());

            return null;
        }

        return $this->handle($response, $endpoint);
    }

    public function put(string $endpoint, array $payload = []): ?array
    {
        try {
            $response = $this->request()->put(ltrim($endpoint, '/'), $payload);
        } catch (\Throwable $e) {
            Log::error('API PUT '.$endpoint.' : '.$e->getMessage());

            return null;
        }

        return $this->handle($response, $endpoint);
    }

    public function delete(string $endpoint): ?array
    {
        try {
            $response = $this->request()->delete(ltrim($endpoint, '/'));
        } catch (\Throwable $e) {
            Log::error('API DELETE '.$endpoint.' : '.$e->getMessage());

            return null;
        }

        return $this->handle($response, $endpoint);
    }

/**
     * Envoi d'un fichier (upload d'images depuis l'administration).
     * Le fichier est lu sur le disque temporaire puis renvoye en multipart
     * vers l'API backend, qui le stocke dans storage/app/public/uploads.
     *
     * Les champs accompagnant le fichier partent dans le meme corps multipart :
     * ils sont passes a l'appel HTTP, et non empiles via post(), qui aurait
     * declenche une requete supplementaire vers la cle du champ.
     *
     * @param  array<string, mixed>  $extra  champs accompagnant le fichier
     */
    public function upload(string $endpoint, UploadedFile $file, string $field = 'image', array $extra = []): ?array
    {
        $path = $file->getRealPath();

        if (! $path || ! is_file($path)) {
            session()->flash('api_error', 'Le fichier sélectionné est introuvable.');

            return null;
        }

        try {
            $request = $this->request()
                ->timeout(60)
                ->connectTimeout(10)
                ->attach(
                    $field,
                    fopen($path, 'rb'),
                    $file->getClientOriginalName()
                );

            $endpoint = ltrim($endpoint, '/');

            $response = $request->post($endpoint, $extra);
        } catch (\Throwable $e) {
            Log::error('API UPLOAD '.$endpoint.' : '.$e->getMessage());

            return null;
        }

        return $this->handle($response, $endpoint);
    }

    /**
     * Traduit la reponse de l'API : retourne le tableau "data" ou null,
     * et conserve le message d'erreur pour l'affichage.
     */
    protected function handle(Response $response, string $endpoint): ?array
    {
        $data = $response->json();
        $wasAuthenticated = $this->isAuthenticated();

        if ($response->successful()) {
            return is_array($data) ? $data : ['data' => $data];
        }

        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        // Une erreur de validation est la plus parlante : le backend renvoie
        // parfois une cle de traduction brute ("validation.confirmed") parce
        // qu'il n'embarque pas de fichier de langue. On la traduit ici pour
        // que l'administrateur ne voie jamais un message technique.
        $message = $this->firstError($errors)
            ?? (is_array($data) && isset($data['message'])
                ? $this->readable($data['message'])
                : 'Erreur du serveur ('.$response->status().').');

        // 401 sur une requete authentifiee : le token est invalide ou revoque.
        // En revanche, un 401 sur la page de connexion (mauvais identifiants)
        // doit conserver le message de l'API.
        if ($response->status() === 401 && $wasAuthenticated) {
            $this->setToken(null);
            $message = 'Session expirée, veuillez vous reconnecter.';
        }

        session()->flash('api_error', $message);
        session()->flash('api_errors', $errors);

        Log::warning('API '.$response->status().' sur '.$endpoint.' : '.$message);

        return null;
    }

    /**
     * Premiere erreur de validation, traduite en francais.
     */
    protected function firstError(array $errors): ?string
    {
        foreach ($errors as $messages) {
            $first = is_array($messages) ? ($messages[0] ?? null) : $messages;

            if (is_string($first) && $first !== '') {
                return $this->readable($first);
            }
        }

        return null;
    }

    /**
     * L'API peut renvoyer une cle de traduction brute lorsqu'aucun fichier de
     * langue n'est installe cote backend. On la rend lisible.
     */
    protected function readable(string $message): string
    {
        $messages = [
            'validation.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'validation.required' => 'Un champ obligatoire est manquant.',
            'validation.min' => 'La valeur est trop courte.',
            'validation.email' => "L'adresse email n'est pas valide.",
            'validation.current_password' => 'Le mot de passe actuel est incorrect.',
            'validation.password' => 'Le mot de passe est incorrect.',
        ];

        return $messages[$message] ?? $message;
    }

    /**
     * Racine publique des fichiers stockes par le backend.
     *
     * C'est la seule source de verite sur l'emplacement reel des images : le
     * frontend s'en sert pour les relayer et pour reconnaitre les URL que
     * l'API renvoie deja completas (galerie, photos de temoignages).
     */
    public static function storageBaseUrl(): string
    {
        return rtrim((string) config('services.backend.storage_url'), '/');
    }

    /** Prefixe public sous lequel le frontend sert les images du backend. */
    public const MEDIA_PREFIX = '/media/';

    /**
     * URL d'une image, servie par le frontend et donc de meme origine que la page.
     *
     * Les images sont stockees par le backend, sur un autre port eteventuellement
     * un autre hote que les pages. Construire directement l'URL du backend
     * obligeait le navigateur a joindre le backend tout seul : le site perdait
     * alors ses images des que le navigateur ne pouvait plus y acceder (autre
     * appareil, backend arrete, ou page en HTTPS servie avec une image en HTTP,
     * bloquee par la regle de contenu mixte). Le frontend les sert donc
     * lui-meme, via la route /media, et l'image suit toujours la page.
     *
     * L'API renvoie les images sous deux formes : un chemin relatif
     * ("uploads/x.jpg") pour les reglages, et l'URL complete du backend pour la
     * galerie et les photos de temoignages. Les deux sont reduites ici au meme
     * chemin. Une URL qui ne designate pas le stockage du backend (CDN, autre
     * hebergeur) est en revanche respectee telle quelle.
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

        if (self::estUrlAbsolue($path) && ! self::appartientAuBackend($path)) {
            return $path;
        }

        $chemin = self::cheminStocke($path);

        if ($chemin === null) {
            // URL absolue du backend dont le prefixe n'a pas ete reconnu : on la
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

    /** L'URL designe-t-elle le dossier de fichiers du backend ? */
    protected static function appartientAuBackend(string $url): bool
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
     * Chemin du fichier chez le backend, a partir d'un chemin relatif ou d'une
     * URL complete du backend. Renvoie null si le prefixe de stockage n'est pas
     * reconnu.
     */
    protected static function cheminStocke(string $path): ?string
    {
        if (! self::estUrlAbsolue($path)) {
            return ltrim($path, '/');
        }

        $prefixe = rtrim((string) (parse_url(self::storageBaseUrl(), PHP_URL_PATH) ?: ''), '/').'/';
        $chemin = (string) (parse_url($path, PHP_URL_PATH) ?: '');

        if ($prefixe === '/' || ! str_starts_with($chemin, $prefixe)) {
            return null;
        }

        return ltrim(substr($chemin, strlen($prefixe)), '/');
    }
}

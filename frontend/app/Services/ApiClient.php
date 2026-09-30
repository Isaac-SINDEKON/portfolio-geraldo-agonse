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
            ->timeout(15)
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
     */
    public function upload(string $endpoint, UploadedFile $file, string $field = 'image', array $extra = []): ?array
    {
        $path = $file->getRealPath();

        if (! $path || ! is_file($path)) {
            session()->flash('api_error', 'Le fichier sélectionné est introuvable.');

            return null;
        }

        try {
            $request = $this->request()->attach(
                $field,
                fopen($path, 'rb'),
                $file->getClientOriginalName()
            );

            foreach ($extra as $key => $value) {
                $request = $request->post($key, $value);
            }

            $response = $request->post(ltrim($endpoint, '/'));
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

        $message = is_array($data) && isset($data['message'])
            ? $data['message']
            : 'Erreur du serveur ('.$response->status().').';

        // 401 sur une requete authentifiee : le token est invalide ou revoque.
        // En revanche, un 401 sur la page de connexion (mauvais identifiants)
        // doit conserver le message de l'API.
        if ($response->status() === 401 && $wasAuthenticated) {
            $this->setToken(null);
            $message = 'Session expirée, veuillez vous reconnecter.';
        }

        session()->flash('api_error', $message);
        session()->flash('api_errors', is_array($data['errors'] ?? null) ? $data['errors'] : []);

        Log::warning('API '.$response->status().' sur '.$endpoint.' : '.$message);

        return null;
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim(config('services.backend.storage_url'), '/').'/'.ltrim($path, '/');
    }
}

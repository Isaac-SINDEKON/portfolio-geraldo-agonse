<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API backend
    |--------------------------------------------------------------------------
    |
    | Cette application est separee du backend : elle consomme uniquement
    | l'API exposee par l'application Laravel situee dans le dossier backend/.
    |
    */

    'api' => [
        'url' => env('API_URL', 'http://127.0.0.1:8000'),
        'prefix' => env('API_PREFIX', 'api/v1'),
        'timeout' => env('API_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backend consomme par App\Services\ApiClient
    |--------------------------------------------------------------------------
    |
    | L'application consomme uniquement l'API exposee par l'application Laravel
    | du dossier backend/. API_URL est la racine de ce backend, SANS le prefixe
    | de version : ApiClient y ajoute /api/v1, et les fichiers stockes sont
    | servis sur /storage.
    |
    | En production, ces valeurs doivent pointer vers le backend en ligne
    | (ex : https://api.votre-domaine.com). Le defaut 127.0.0.1:8000 est celui
    | de la machine de developpement : oublier de le remplacer vide les textes
    | et casse les images sur le site en ligne.
    |
    */

    'backend' => [
        'api_url' => (str_ends_with((string) env('API_URL', ''), '/api/v1')
            ? env('API_URL')
            : env('API_URL', 'http://127.0.0.1:8000').'/api/v1'),
        'storage_url' => env('API_STORAGE_URL',
            rtrim(env('API_URL', 'http://127.0.0.1:8000'), '/').'/storage'),
    ],

];

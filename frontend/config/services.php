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
    | L'URL de l'API inclut deja le prefixe de version (API_URL), et l'URL des
    | fichiers stockes permet de construire les URL d'images renvoyees par
    | l'API.
    |
    */

    'backend' => [
        'api_url' => env('API_URL', 'http://127.0.0.1:8000/api/v1'),
        'storage_url' => env('API_STORAGE_URL', 'http://127.0.0.1:8000/storage'),
    ],

];

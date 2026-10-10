<?php

/*
|--------------------------------------------------------------------------
| Stockage des fichiers
|--------------------------------------------------------------------------
|
| Le site, l'administration et le stockage vivent dans la meme application
| Laravel, servie sur un seul port. Les images sont sur le disque public et
| servies par la route /media.
|
| services.storage.url sert uniquement de reference a App\Services\ApiClient
| pour reconnaitre une eventuelle URL de stockage deja complete et la reduire
| au relais /media.
|
*/

$storageBase = rtrim(
    (string) (env('API_STORAGE_URL') ?: env('APP_URL') ?: 'http://127.0.0.1:8000'),
    '/'
);

if (str_ends_with($storageBase, '/storage')) {
    $storageBase = substr($storageBase, 0, -strlen('/storage'));
}

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

    'storage' => [
        'url' => $storageBase.'/storage',
    ],

];

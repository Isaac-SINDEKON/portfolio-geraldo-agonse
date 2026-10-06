<?php

use Illuminate\Support\Facades\Route;

// Controle de sante (Render Health Check Path : /health).
Route::get('/health', fn () => response('ok', 200, ['Content-Type' => 'text/plain']));

Route::get('/', function () {
    return response()->json([
        'application' => config('app.name'),
        'role' => 'API backend du portfolio de Géraldo Perridys AGONSE',
        'version' => 'v1',
        'endpoints' => [
            'GET  /api/v1/site',
            'GET  /api/v1/formations',
            'GET  /api/v1/formations/{slug}',
            'POST /api/v1/leads',
            'POST /api/v1/auth/login',
            'POST /api/v1/auth/logout',
            'GET  /api/v1/auth/me',
            'POST /api/v1/auth/change-password',
            'GET  /api/v1/admin/content',
            'PUT  /api/v1/admin/settings',
            'POST /api/v1/admin/upload',
            'GET/POST/PUT/DELETE /api/v1/admin/{resource}',
            'GET/DELETE /api/v1/admin/leads',
        ],
    ]);
});

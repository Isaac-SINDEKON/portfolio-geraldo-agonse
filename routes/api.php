<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController as PublicLeadController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API publique
|--------------------------------------------------------------------------
|
| Le site et l'administration utilisent directement les modeles : ces routes
| ne servent donc plus la page web. Elles restent l'API publique de
| l'application (integrations externes, verification par script) et
| l'authentification des outils d'administration.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('site', [SiteController::class, 'site']);
    Route::get('formations', [SiteController::class, 'formations']);
    Route::get('formations/{slug}', [SiteController::class, 'formation']);

    Route::post('leads', [PublicLeadController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    });
});

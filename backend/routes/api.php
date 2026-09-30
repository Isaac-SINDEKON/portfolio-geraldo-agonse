<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController as PublicLeadController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

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

        Route::prefix('admin')->group(function () {
            Route::get('content', [ContentController::class, 'all']);
            Route::put('settings', [ContentController::class, 'updateSettings']);
            Route::post('upload', [UploadController::class, 'upload']);

            Route::get('leads', [LeadController::class, 'index']);
            Route::delete('leads/{id}', [LeadController::class, 'destroy']);

            Route::get('{resource}', [ContentController::class, 'index'])
                ->where('resource', 'domains|reasons|services|experiences|testimonials|formations|gallery');
            Route::post('{resource}', [ContentController::class, 'store'])
                ->where('resource', 'domains|reasons|services|experiences|testimonials|formations|gallery');
            Route::put('{resource}/{id}', [ContentController::class, 'update'])
                ->where('resource', 'domains|reasons|services|experiences|testimonials|formations|gallery');
            Route::delete('{resource}/{id}', [ContentController::class, 'destroy'])
                ->where('resource', 'domains|reasons|services|experiences|testimonials|formations|gallery');
        });
    });
});
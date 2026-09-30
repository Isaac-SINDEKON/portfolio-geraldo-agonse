<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Le backend est une API pure : aucune vue Blade publique n'est rendue ici.
     */
    public function boot(): void
    {
        //
    }
}

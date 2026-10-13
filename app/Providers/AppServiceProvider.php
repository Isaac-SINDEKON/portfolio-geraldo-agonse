<?php

namespace App\Providers;

use App\Services\SiteContent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // En production, toute URL generee est en HTTPS (CC §25). Le certificat
        // est fourni par l'hebergeur : cela evite les liens mixtes et les
        // redirections http:// vers la version securisee.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Le contenu public (parametres, formations, services, temoignages, galerie)
        // est lu dans la base et partage avec toutes les vues concernees.
        // SiteContent conserve un cache statique : une seule lecture par requete.
        View::composer(
            ['site.*', 'layouts.*', 'components.*', 'livewire.*', 'admin.*'],
            function ($view) {
                $content = app(SiteContent::class);
                $site = $content->all();

                $view->with('content', $content);
                $view->with('site', $site);
            }
        );
    }
}

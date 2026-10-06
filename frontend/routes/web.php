<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sante du service (Render Health Check Path : /health)
|--------------------------------------------------------------------------
|
| Reponse immediate, sans base, sans cache et sans appel a l'API : le
| controle de sante ne depend donc jamais du backend ni du contenu.
|
*/

Route::get('/health', fn () => response('ok', 200, ['Content-Type' => 'text/plain']));

/*
|--------------------------------------------------------------------------
| Pages publiques (arborescence du CC §6)
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/a-propos', [PageController::class, 'about'])->name('about');
Route::get('/formations', [PageController::class, 'formations'])->name('formations');
Route::get('/formations/{slug}', [PageController::class, 'formation'])->name('formations.show');
Route::get('/services-entreprises', [PageController::class, 'services'])->name('services');
Route::get('/experience-expertise', [PageController::class, 'experience'])->name('experience');
Route::get('/temoignages', [PageController::class, 'testimonials'])->name('testimonials');
Route::get('/galerie', [PageController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| Images (relais vers le backend)
|--------------------------------------------------------------------------
|
| Les images sont stockees par le backend mais servies par le frontend : le
| navigateur n'a ainsi jamais a joindre le backend lui-meme pour afficher une
| image. Voir App\Http\Controllers\MediaController.
|
*/

Route::get('/media/{path}', MediaController::class)
    ->where('path', 'uploads/.*')
    ->name('media');

/*
|--------------------------------------------------------------------------
| SEO (CC §23)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Administration (CC §22)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Connexion
    Route::middleware('guest.admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Une seule rubrique de premier niveau regroupe toutes les sections
        // editables (CC §22) : la navigation reste lisible sur petit ecran.
        Route::get('/contenu', [AdminContentController::class, 'index'])->name('content.index');

        // Contenu general : identite, coordonnees, SEO, photos
        Route::get('/contenu/reglages', [AdminSettingsController::class, 'edit'])->name('settings.edit');
        Route::post('/contenu/reglages', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/mot-de-passe', [AdminAuthController::class, 'updatePassword'])->name('password.update');

        // Formations
        Route::get('/formations', [AdminFormationController::class, 'index'])->name('formations.index');
        Route::get('/formations/{id}/modifier', [AdminFormationController::class, 'edit'])->name('formations.edit');
        Route::post('/formations', [AdminFormationController::class, 'store'])->name('formations.store');
        Route::put('/formations/{id}', [AdminFormationController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{id}', [AdminFormationController::class, 'destroy'])->name('formations.destroy');

        // Domaines, raisons, services, experiences (structure identique)
        Route::get('/sections/{resource}', [AdminResourceController::class, 'index'])
            ->where('resource', 'domains|reasons|services|experiences')->name('resources.index');
        Route::post('/sections/{resource}', [AdminResourceController::class, 'store'])
            ->where('resource', 'domains|reasons|services|experiences')->name('resources.store');
        Route::put('/sections/{resource}/{id}', [AdminResourceController::class, 'update'])
            ->where('resource', 'domains|reasons|services|experiences')->name('resources.update');
        Route::delete('/sections/{resource}/{id}', [AdminResourceController::class, 'destroy'])
            ->where('resource', 'domains|reasons|services|experiences')->name('resources.destroy');

        // Temoignages
        Route::get('/temoignages', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
        Route::post('/temoignages', [AdminTestimonialController::class, 'store'])->name('testimonials.store');
        Route::put('/temoignages/{id}', [AdminTestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/temoignages/{id}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

        // Galerie
        Route::get('/galerie', [AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::post('/galerie', [AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::put('/galerie/{id}', [AdminGalleryController::class, 'update'])->name('gallery.update');
        Route::delete('/galerie/{id}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');

        // Demandes recues
        Route::get('/demandes', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('/demandes/{id}', [AdminLeadController::class, 'show'])->name('leads.show');
        Route::delete('/demandes/{id}', [AdminLeadController::class, 'destroy'])->name('leads.destroy');
    });
});

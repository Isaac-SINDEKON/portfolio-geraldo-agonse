<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Experience;
use App\Models\Formation;
use App\Models\GalleryImage;
use App\Models\Reason;
use App\Models\Service;
use App\Models\Testimonial;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;

/**
 * Base de l'administration.
 *
 * L'administration ecrit et lit directement les modeles : les pages, l'API et
 * la base vivent dans la meme application, il n'y a donc plus d'appel HTTP
 * interne a faire.
 */
abstract class AdminController extends Controller
{
    /** Ressource d'administration -> modele Eloquent associe. */
    protected const MODELS = [
        'domains' => Domain::class,
        'reasons' => Reason::class,
        'services' => Service::class,
        'experiences' => Experience::class,
        'testimonials' => Testimonial::class,
        'formations' => Formation::class,
        'gallery' => GalleryImage::class,
    ];

    /** Ordres calcules une seule fois par ressource et par requete. */
    protected array $nextSortOrder = [];

    public function __construct(
        protected SiteContent $content
    ) {}

    /**
     * Place un nouvel element en fin de liste lorsque l'ordre n'est pas saisi.
     *
     * @param  array<int|string, mixed>|string|null  $sortOrder  Requete validee ou valeur brute.
     */
    protected function sortOrder(array|string|null $sortOrder, string $resource): int
    {
        $value = is_array($sortOrder) ? ($sortOrder['sort_order'] ?? null) : $sortOrder;

        if (filled($value)) {
            return (int) $value;
        }

        if (! isset($this->nextSortOrder[$resource])) {
            $model = self::MODELS[$resource] ?? null;

            $this->nextSortOrder[$resource] = $model ? ((int) $model::max('sort_order')) + 1 : 1;
        }

        return $this->nextSortOrder[$resource];
    }

    protected function back(string $message): RedirectResponse
    {
        $this->content->forget();

        return back()->with('success', $message);
    }

    /**
     * Retour au formulaire avec le message d'erreur.
     */
    protected function fail(string $fallback = 'Impossible d\'enregistrer les modifications.'): RedirectResponse
    {
        return back()
            ->with('error', $fallback)
            ->withInput();
    }
}

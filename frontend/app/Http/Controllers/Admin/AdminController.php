<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;

/**
 * Base de l'administration : la communication avec l'API backend passe
 * exclusivement par ApiClient (token Sanctum stocke en session).
 */
abstract class AdminController extends Controller
{
    /** Ordres calcules une seule fois par ressource et par requete. */
    protected array $nextSortOrder = [];

    public function __construct(
        protected ApiClient $api,
        protected SiteContent $content
    ) {
    }

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
            $max = 0;

            foreach ($this->api->get("admin/{$resource}") ?: [] as $item) {
                $max = max($max, (int) ($item['sort_order'] ?? 0));
            }

            $this->nextSortOrder[$resource] = $max + 1;
        }

        return $this->nextSortOrder[$resource];
    }

    protected function back(string $message): RedirectResponse
    {
        $this->content->forget();

        return back()->with('success', $message);
    }

    /**
     * Retour au formulaire avec le message d'erreur de l'API.
     * Si le token n'est plus valide, l'administrateur est renvoye vers la connexion.
     */
    protected function fail(string $fallback = 'Impossible d\'enregistrer les modifications.'): RedirectResponse
    {
        if (! $this->api->isAuthenticated()) {
            request()->session()->forget(['admin_token', 'admin_user']);
            request()->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Votre session a expiré, veuillez vous reconnecter.');
        }

        return back()
            ->with('error', session('api_error') ?: $fallback)
            ->withInput();
    }
}

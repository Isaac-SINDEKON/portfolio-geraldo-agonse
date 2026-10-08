<?php

namespace App\Http\Controllers\Admin;

use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Formations (CC §9) : contenu complet et modifiable, y compris la duree,
 * le programme et les modalites.
 */
class FormationController extends AdminController
{
    public function index()
    {
        return view('admin.formations', [
            'formations' => $this->api->get('admin/formations') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $formation = collect($this->api->get('admin/formations') ?: [])
            ->firstWhere('id', $id);

        abort_if(! $formation, 404, 'Formation introuvable.');

        return view('admin.formation-form', [
            'formation' => $formation,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->api->post('admin/formations', $data) === null) {
            return $this->fail('Impossible de créer la formation.');
        }

        return $this->back('La formation a été créée.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->api->put("admin/formations/{$id}", $data) === null) {
            return $this->fail('Impossible de mettre à jour la formation.');
        }

        return $this->back('La formation a été mise à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ($this->api->delete("admin/formations/{$id}") === null) {
            return $this->fail('Impossible de supprimer la formation.');
        }

        return $this->back('La formation a été supprimée.');
    }

    /**
     * Les listes (objectifs, programme) sont saisies ligne par ligne puis
     * converties en tableaux pour l'API.
     *
     * L'unicite du slug est controlee par l'API : la base du frontend ne
     * contient pas les tables de contenu.
     */
    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'objectives_text' => ['nullable', 'string'],
            'public_cible' => ['nullable', 'string', 'max:255'],
            'duree' => ['nullable', 'string', 'max:100'],
            'modalites' => ['nullable', 'string', 'max:255'],
            'programme_text' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
        ]);

        return [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'],
            'objectives' => SiteContent::linesToArray($validated['objectives_text'] ?? null),
            'public_cible' => $validated['public_cible'] ?? null,
            'duree' => $validated['duree'] ?? null,
            'modalites' => $validated['modalites'] ?? null,
            'programme' => SiteContent::linesToArray($validated['programme_text'] ?? null),
            'icon' => $validated['icon'] ?? 'target',
            'sort_order' => $this->sortOrder($validated, 'formations'),
            'active' => (bool) ($validated['active'] ?? false),
        ];
    }
}

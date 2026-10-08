<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Collections simples partageant la meme structure
 * (titre, description, icone, ordre) : domaines, raisons, services, experiences.
 */
class ResourceController extends AdminController
{
    public const RESOURCES = [
        'domains' => ['label' => 'Domaines d\'intervention', 'singular' => 'Domaine', 'icon' => 'target'],
        'reasons' => ['label' => 'Raisons de solliciter le formateur', 'singular' => 'Raison', 'icon' => 'sparkles'],
        'services' => ['label' => 'Services aux entreprises', 'singular' => 'Service', 'icon' => 'building'],
        'experiences' => ['label' => 'Expérience & expertise', 'singular' => 'Expérience', 'icon' => 'briefcase'],
    ];

    public function index(string $resource)
    {
        $config = self::RESOURCES[$resource] ?? abort(404);

        return view('admin.resources', [
            'items' => $this->api->get("admin/{$resource}") ?: [],
            'config' => $config,
            'resource' => $resource,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = self::RESOURCES[$resource] ?? abort(404);

        if ($this->api->post("admin/{$resource}", $this->validated($request)) === null) {
            return $this->fail("Impossible d'ajouter cet élément.");
        }

        return $this->back($config['singular'].' ajouté.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = self::RESOURCES[$resource] ?? abort(404);

        if ($this->api->put("admin/{$resource}/{$id}", $this->validated($request)) === null) {
            return $this->fail('Impossible de mettre à jour cet élément.');
        }

        return $this->back($config['singular'].' mis à jour.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = self::RESOURCES[$resource] ?? abort(404);

        if ($this->api->delete("admin/{$resource}/{$id}") === null) {
            return $this->fail('Impossible de supprimer cet élément.');
        }

        return $this->back($config['singular'].' supprimé.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
        ]);

        return [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'icon' => $validated['icon'] ?? $this->defaultIcon(),
            'sort_order' => $this->sortOrder($validated, request()->route('resource')),
        ];
    }

    protected function defaultIcon(): string
    {
        $config = self::RESOURCES[request()->route('resource')] ?? [];

        return $config['icon'] ?? 'target';
    }
}

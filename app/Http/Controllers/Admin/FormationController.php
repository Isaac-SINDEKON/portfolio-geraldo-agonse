<?php

namespace App\Http\Controllers\Admin;

use App\Models\Formation;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Formations (CC §9) : contenu complet et modifiable, y compris la duree,
 * le programme et les modalites.
 */
class FormationController extends AdminController
{
    public function index()
    {
        return view('admin.formations', [
            'formations' => Formation::orderBy('sort_order')->get()->toArray(),
        ]);
    }

    public function edit(int $id)
    {
        $formation = Formation::find($id);

        abort_if(! $formation, 404, 'Formation introuvable.');

        return view('admin.formation-form', [
            'formation' => $formation->toArray(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Formation::create($data);

        return $this->back('La formation a été créée.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $formation = Formation::findOrFail($id);

        $formation->update($this->validated($request, $id));

        return $this->back('La formation a été mise à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Formation::findOrFail($id)->delete();

        return $this->back('La formation a été supprimée.');
    }

    /**
     * Les listes (objectifs, programme) sont saisies ligne par ligne puis
     * converties en tableaux.
     */
    protected function validated(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::unique('formations', 'slug')->ignore($id)],
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

        $slug = $validated['slug'] ?? null;

        $data = [
            'title' => $validated['title'],
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

        // Un nouveau programme recoit un slug ; en modification, le slug
        // existant est conserve : changer la duree ou un texte ne doit pas
        // casser l'URL deja partagee.
        if ($id === null) {
            $data['slug'] = $slug ?: Str::slug($validated['title']).'-'.Str::lower(Str::random(4));
        } elseif (filled($slug)) {
            $data['slug'] = $slug;
        }

        return $data;
    }
}

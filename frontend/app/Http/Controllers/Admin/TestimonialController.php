<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Temoignages (CC §12) : citation, nom, fonction, photo facultative.
 */
class TestimonialController extends AdminController
{
    public function index()
    {
        return view('admin.testimonials', [
            'testimonials' => $this->api->get('admin/testimonials') ?: [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $photo = $this->uploadPhoto($request);
        if ($photo === false) {
            return $this->fail('Le téléversement de la photo a échoué.');
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        if ($this->api->post('admin/testimonials', $data) === null) {
            return $this->fail('Impossible d\'ajouter le témoignage.');
        }

        return $this->back('Le témoignage a été ajouté.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validated($request);

        $photo = $this->uploadPhoto($request);
        if ($photo === false) {
            return $this->fail('Le téléversement de la photo a échoué.');
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        if ($this->api->put("admin/testimonials/{$id}", $data) === null) {
            return $this->fail('Impossible de mettre à jour le témoignage.');
        }

        return $this->back('Le témoignage a été mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ($this->api->delete("admin/testimonials/{$id}") === null) {
            return $this->fail('Impossible de supprimer le témoignage.');
        }

        return $this->back('Le témoignage a été supprimé.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'author' => ['required', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
        ], [
            'author.required' => 'Le nom de la personne est obligatoire.',
            'content.required' => 'La citation est obligatoire.',
        ]);

        return [
            'author' => $validated['author'],
            'fonction' => $validated['fonction'] ?? null,
            'content' => $validated['content'],
            'sort_order' => $this->sortOrder($validated, 'testimonials'),
            'active' => (bool) ($validated['active'] ?? true),
        ];
    }

    /** @return string|false|null chemin de l'image, false en cas d'echec */
    protected function uploadPhoto(Request $request): string|false|null
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $upload = $this->api->upload('admin/upload', $request->file('photo'), 'image');

        return $upload === null ? false : ($upload['path'] ?? false);
    }
}

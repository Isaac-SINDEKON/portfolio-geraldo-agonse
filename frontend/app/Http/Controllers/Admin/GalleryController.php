<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Galerie photos (CC §13) : ajout, legende, ordre et suppression.
 * Le fichier est televerse vers l'API backend qui le stocke dans
 * storage/app/public/uploads ; le chemin renvoye est enregistre en base.
 */
class GalleryController extends AdminController
{
    public function index()
    {
        return view('admin.gallery', [
            'images' => $this->api->get('admin/gallery') ?: [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ], [
            'image.required' => 'Sélectionnez une image.',
            'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        $upload = $this->api->upload('admin/upload', $request->file('image'), 'image');

        if ($upload === null || empty($upload['path'])) {
            return $this->fail('Le téléversement de l\'image a échoué.');
        }

        if ($this->api->post('admin/gallery', [
            'image_path' => $upload['path'],
            'caption' => $request->input('caption'),
            'sort_order' => $this->sortOrder($request->input('sort_order'), 'gallery'),
        ]) === null) {
            return $this->fail('Impossible d\'enregistrer cette image.');
        }

        return $this->back('L\'image a été ajoutée à la galerie.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($this->api->put("admin/gallery/{$id}", [
            'caption' => $request->input('caption'),
            'sort_order' => $request->input('sort_order'),
        ]) === null) {
            return $this->fail('Impossible de mettre à jour cette image.');
        }

        return $this->back('L\'image a été mise à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ($this->api->delete("admin/gallery/{$id}") === null) {
            return $this->fail('Impossible de supprimer cette image.');
        }

        return $this->back('L\'image a été supprimée.');
    }
}

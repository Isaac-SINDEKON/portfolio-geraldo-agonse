<?php

namespace App\Http\Controllers\Admin;

use App\Models\GalleryImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Galerie photos (CC §13) : ajout, legende, ordre et suppression.
 * Le fichier est televerse sur le disque public ; le chemin renvoye est
 * enregistre en base.
 */
class GalleryController extends AdminController
{
    public function index()
    {
        return view('admin.gallery', [
            'images' => GalleryImage::orderBy('sort_order')->get()->toArray(),
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

        $path = $request->file('image')->store('uploads', 'public');

        if (! $path) {
            return $this->fail('Le téléversement de l\'image a échoué.');
        }

        GalleryImage::create([
            'image_path' => $path,
            'caption' => $request->input('caption'),
            'sort_order' => $this->sortOrder($request->input('sort_order'), 'gallery'),
        ]);

        return $this->back('L\'image a été ajoutée à la galerie.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $image = GalleryImage::findOrFail($id);

        // Les formulaires de modification sont nommes par identifiant
        // (caption[12]) : la valeur saisie appartient a une seule image et ne
        // se retrouve pas dans les autres cartes apres une erreur de validation.
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'caption.'.$id => ['nullable', 'string', 'max:255'],
            'sort_order.'.$id => ['nullable', 'integer'],
        ], [
            'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        $payload = [
            'caption' => $request->input('caption.'.$id),
            'sort_order' => $request->input('sort_order.'.$id),
        ];

        // Le fichier n'est remplace que si un nouveau est envoye : sans lui, la
        // photo courante est conservee.
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');

            if (! $path) {
                return $this->fail('Le téléversement de l\'image a échoué.');
            }

            $payload['image_path'] = $path;
        }

        $previous = $image->image_path;
        $image->update($payload);

        // Le fichier remplace n'est supprime qu'apres l'ecriture en base : si
        // la ligne est rejetee, l'ancienne photo reste la seule reference valide.
        if (isset($payload['image_path']) && $previous !== $payload['image_path']) {
            $this->forgetStoredFile($previous);
        }

        return $this->back('L\'image a été mise à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $image = GalleryImage::findOrFail($id);

        $this->forgetStoredFile($image->image_path);
        $image->delete();

        return $this->back('L\'image a été supprimée.');
    }

    /**
     * Efface un fichier stocke localement.
     *
     * Seuls les fichiers du dossier de stockage sont vises : une valeur vide
     * est ignoree, et une URL externe n'est jamais touchee.
     */
    protected function forgetStoredFile(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }
}

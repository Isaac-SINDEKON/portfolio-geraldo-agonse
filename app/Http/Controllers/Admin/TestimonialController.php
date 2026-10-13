<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
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
            'testimonials' => Testimonial::orderBy('sort_order')->get()->toArray(),
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

        Testimonial::create($data);

        return $this->back('Le témoignage a été ajouté.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $testimonial = Testimonial::findOrFail($id);

        $data = $this->validated($request);

        $photo = $this->uploadPhoto($request);
        if ($photo === false) {
            return $this->fail('Le téléversement de la photo a échoué.');
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        $testimonial->update($data);

        return $this->back('Le témoignage a été mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Testimonial::findOrFail($id)->delete();

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

        return $request->file('photo')->store('uploads', 'public');
    }
}

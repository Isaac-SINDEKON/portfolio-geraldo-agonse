@extends('layouts.admin')

@section('title', 'Témoignages')

@section('actions')
    <button type="button" class="btn-primary text-xs" x-on:click="window.dispatchEvent(new CustomEvent('ouvrir-ajout'))">
        <x-icon name="plus" class="h-4 w-4" />
        Ajouter un témoignage
    </button>
@endsection

@section('content')

    <div x-data="{ open: false }" x-on:ouvrir-ajout.window="open = true">
        <p class="text-sm text-slate-500">Citation, nom, fonction et photo facultative (CC §12).</p>

        <section class="card mt-6" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
            <h2 class="text-lg font-bold">Nouveau témoignage</h2>

            <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="new-author" class="field-label">Nom de la personne <span class="text-red-500">*</span></label>
                        <input type="text" id="new-author" name="author" value="{{ old('author') }}"
                               @class(['field', 'field-error' => $errors->has('author')]) required>
                        @error('author') <p class="field-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="new-fonction" class="field-label">Fonction</label>
                        <input type="text" id="new-fonction" name="fonction" value="{{ old('fonction') }}" class="field">
                    </div>
                </div>

                <div>
                    <label for="new-content" class="field-label">Citation <span class="text-red-500">*</span></label>
                    <textarea id="new-content" name="content" rows="4"
                              @class(['field', 'field-error' => $errors->has('content')]) required>{{ old('content') }}</textarea>
                    @error('content') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="new-photo" class="field-label">Photo (facultatif)</label>
                        <input type="file" id="new-photo" name="photo" accept="image/jpeg,image/png,image/webp" class="field">
                    </div>

                    <div>
                        <label for="new-order" class="field-label">Ordre d'affichage</label>
                        <input type="number" id="new-order" name="sort_order" value="{{ old('sort_order') }}" class="field">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="active" value="1" @checked(old('active', true)) class="h-4 w-4 rounded border-slate-300">
                    Publier ce témoignage
                </label>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Ajouter</button>
                    <a href="{{ route('admin.testimonials.index') }}" class="btn-ghost">Annuler</a>
                </div>
            </form>
        </section>

        <div class="mt-6 space-y-4">
            @forelse ($testimonials as $testimonial)
                <article class="card">
                    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial['id']) }}"
                          enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="flex items-start gap-4">
                            @if (! empty($testimonial['photo']))
                                <img src="{{ $content->imageUrl($testimonial['photo']) }}" alt="{{ $testimonial['author'] }}"
                                     class="h-16 w-16 shrink-0 rounded-full object-cover">
                            @else
                                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700">
                                    {{ mb_substr($testimonial['author'] ?? '?', 0, 2) }}
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div>
                                    <label for="author-{{ $testimonial['id'] }}" class="field-label">Nom</label>
                                    <input type="text" id="author-{{ $testimonial['id'] }}" name="author"
                                           value="{{ old('author', $testimonial['author']) }}"
                                           @class(['field', 'field-error' => $errors->has('author')]) required>
                                </div>

                                <div class="mt-3">
                                    <label for="fonction-{{ $testimonial['id'] }}" class="field-label">Fonction</label>
                                    <input type="text" id="fonction-{{ $testimonial['id'] }}" name="fonction"
                                           value="{{ old('fonction', $testimonial['fonction'] ?? '') }}" class="field">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="content-{{ $testimonial['id'] }}" class="field-label">Citation</label>
                            <textarea id="content-{{ $testimonial['id'] }}" name="content" rows="3"
                                      @class(['field', 'field-error' => $errors->has('content')]) required>{{ old('content', $testimonial['content']) }}</textarea>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="photo-{{ $testimonial['id'] }}" class="field-label">Remplacer la photo</label>
                                <input type="file" id="photo-{{ $testimonial['id'] }}" name="photo"
                                       accept="image/jpeg,image/png,image/webp" class="field">
                            </div>

                            <div>
                                <label for="order-{{ $testimonial['id'] }}" class="field-label">Ordre</label>
                                <input type="number" id="order-{{ $testimonial['id'] }}" name="sort_order"
                                       value="{{ old('sort_order', $testimonial['sort_order'] ?? '') }}" class="field">
                            </div>

                            <div class="flex items-end">
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input type="checkbox" name="active" value="1"
                                           @checked(old('active', $testimonial['active'] ?? true))
                                           class="h-4 w-4 rounded border-slate-300">
                                    Publié
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary px-4 py-2 text-xs">Enregistrer</button>
                    </form>

                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial['id']) }}"
                          class="mt-4 border-t border-slate-100 pt-4"
                          onsubmit="return confirm('Supprimer définitivement ce témoignage ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger px-3 py-2 text-xs">
                            <x-icon name="trash" class="h-4 w-4" />
                            Supprimer
                        </button>
                    </form>
                </article>
            @empty
                <p class="card text-center text-sm text-slate-500">Aucun témoignage enregistré.</p>
            @endforelse
        </div>
    </div>

@endsection

@extends('layouts.admin')

@section('title', $config['label'])

@section('actions')
    <button type="button" class="btn-primary text-xs" x-on:click="window.dispatchEvent(new CustomEvent('ouvrir-ajout'))">
        <x-icon name="plus" class="h-4 w-4" />
        Ajouter
    </button>
@endsection

@section('content')

    <div class="flex flex-wrap gap-2">
        @foreach (\App\Http\Controllers\Admin\ResourceController::RESOURCES as $key => $meta)
            <a href="{{ route('admin.resources.index', ['resource' => $key]) }}"
               @class([
                   'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                   'bg-primary-600 text-white' => $key === $resource,
                   'bg-surface text-copy ring-1 ring-line hover:bg-canvas' => $key !== $resource,
               ])>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    <div x-data="{ open: false }" x-on:ouvrir-ajout.window="open = true">
        <p class="mt-4 text-sm text-muted">{{ count($items) }} élément(s) enregistré(s).</p>

        <section class="card mt-6" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
        <h2 class="text-lg font-bold">Ajouter un élément</h2>

        <form method="POST" action="{{ route('admin.resources.store', ['resource' => $resource]) }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <label for="new-title" class="field-label">Titre <span class="text-red-500">*</span></label>
                <input type="text" id="new-title" name="title" value="{{ old('title') }}"
                       @class(['field', 'field-error' => $errors->has('title')]) required>
                @error('title') <p class="field-message">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new-description" class="field-label">Description <span class="text-red-500">*</span></label>
                <textarea id="new-description" name="description" rows="4"
                          @class(['field', 'field-error' => $errors->has('description')]) required>{{ old('description') }}</textarea>
                @error('description') <p class="field-message">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="new-icon" class="field-label">Icône</label>
                    <input type="text" id="new-icon" name="icon" value="{{ old('icon', $config['icon']) }}" class="field">
                </div>
                <div>
                    <label for="new-order" class="field-label">Ordre d'affichage</label>
                    <input type="number" id="new-order" name="sort_order" value="{{ old('sort_order') }}" class="field">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Ajouter</button>
                <a href="{{ route('admin.resources.index', ['resource' => $resource]) }}" class="btn-ghost">Annuler</a>
            </div>
            </form>
        </section>

        <div class="mt-6 space-y-4">
            @forelse ($items as $item)
            <article class="card">
                <form method="POST" action="{{ route('admin.resources.update', ['resource' => $resource, 'id' => $item['id']]) }}"
                      class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="title-{{ $item['id'] }}" class="field-label">Titre</label>
                        <input type="text" id="title-{{ $item['id'] }}" name="title"
                               value="{{ old('title', $item['title']) }}"
                               @class(['field', 'field-error' => $errors->has('title')]) required>
                    </div>

                    <div>
                        <label for="description-{{ $item['id'] }}" class="field-label">Description</label>
                        <textarea id="description-{{ $item['id'] }}" name="description" rows="3"
                                  @class(['field', 'field-error' => $errors->has('description')]) required>{{ old('description', $item['description']) }}</textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="icon-{{ $item['id'] }}" class="field-label">Icône</label>
                            <input type="text" id="icon-{{ $item['id'] }}" name="icon"
                                   value="{{ old('icon', $item['icon'] ?? $config['icon']) }}" class="field">
                        </div>
                        <div>
                            <label for="order-{{ $item['id'] }}" class="field-label">Ordre d'affichage</label>
                            <input type="number" id="order-{{ $item['id'] }}" name="sort_order"
                                   value="{{ old('sort_order', $item['sort_order'] ?? '') }}" class="field">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="btn-primary px-4 py-2 text-xs">Enregistrer</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.resources.destroy', ['resource' => $resource, 'id' => $item['id']]) }}"
                      class="mt-4 border-t border-line-soft pt-4"
                      onsubmit="return confirm('Supprimer définitivement cet élément ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger px-3 py-2 text-xs">
                        <x-icon name="trash" class="h-4 w-4" />
                        Supprimer
                    </button>
                </form>
            </article>
            @empty
                <p class="card text-center text-sm text-muted">Aucun élément enregistré pour cette section.</p>
            @endforelse
        </div>
    </div>

@endsection

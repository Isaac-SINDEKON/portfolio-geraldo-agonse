@extends('layouts.admin')

@section('title', 'Modifier la formation')

@section('content')

    <a href="{{ route('admin.formations.index') }}" class="text-sm font-semibold text-primary-600">
        <x-icon name="arrow-left" class="inline h-4 w-4" />
        Retour aux formations
    </a>

    <h1 class="mt-3 text-2xl font-extrabold">{{ $formation['title'] }}</h1>
    <p class="mt-1 text-sm text-slate-500">Tous les champs modifiables sont éditables ici (CC §9).</p>

    <form method="POST" action="{{ route('admin.formations.update', $formation['id']) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="card space-y-4">
            <h2 class="text-lg font-bold">Informations générales</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="title" class="field-label">Titre <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $formation['title']) }}"
                           @class(['field', 'field-error' => $errors->has('title')]) required>
                    @error('title') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="field-label">Identifiant d'URL (slug)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $formation['slug']) }}"
                           @class(['field', 'field-error' => $errors->has('slug')])>
                    <p class="mt-1 text-xs text-slate-500">Laisser vide pour générer automatiquement.</p>
                    @error('slug') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subtitle" class="field-label">Sous-titre</label>
                    <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle', $formation['subtitle'] ?? '') }}" class="field">
                </div>

                <div>
                    <label for="icon" class="field-label">Icône</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon', $formation['icon'] ?? '') }}" class="field">
                    <p class="mt-1 text-xs text-slate-500">Ex. clock, chart, rocket, heart, target, users.</p>
                </div>
            </div>

            <div>
                <label for="description" class="field-label">Description <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="4"
                          @class(['field', 'field-error' => $errors->has('description')]) required>{{ old('description', $formation['description']) }}</textarea>
                @error('description') <p class="field-message">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="card space-y-4">
            <h2 class="text-lg font-bold">Modalités</h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="duree" class="field-label">Durée</label>
                    <input type="text" id="duree" name="duree" value="{{ old('duree', $formation['duree'] ?? '') }}"
                           class="field" placeholder="Ex. 2 jours">
                </div>

                <div>
                    <label for="public_cible" class="field-label">Public cible</label>
                    <input type="text" id="public_cible" name="public_cible"
                           value="{{ old('public_cible', $formation['public_cible'] ?? '') }}" class="field">
                </div>

                <div>
                    <label for="modalites" class="field-label">Modalités</label>
                    <input type="text" id="modalites" name="modalites" value="{{ old('modalites', $formation['modalites'] ?? '') }}"
                           class="field" placeholder="Ex. Présentiel ou distanciel">
                </div>
            </div>

            <div>
                <label for="objectives_text" class="field-label">Objectifs (un par ligne)</label>
                <textarea id="objectives_text" name="objectives_text" rows="5" class="field">{{ old('objectives_text', implode("\n", $formation['objectives'] ?? [])) }}</textarea>
            </div>

            <div>
                <label for="programme_text" class="field-label">Programme détaillé (un module par ligne)</label>
                <textarea id="programme_text" name="programme_text" rows="8" class="field">{{ old('programme_text', implode("\n", $formation['programme'] ?? [])) }}</textarea>
            </div>
        </section>

        <section class="card space-y-4">
            <h2 class="text-lg font-bold">Publication</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sort_order" class="field-label">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order"
                           value="{{ old('sort_order', $formation['sort_order'] ?? '') }}" class="field">
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="active" value="1"
                               @checked(old('active', $formation['active'] ?? true))
                               class="h-4 w-4 rounded border-slate-300">
                        Formation visible sur le site
                    </label>
                </div>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.formations.index') }}" class="btn-ghost">Annuler</a>
        </div>
    </form>

@endsection

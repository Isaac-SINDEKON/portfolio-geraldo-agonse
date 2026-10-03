@extends('layouts.admin')

@section('title', 'Formations')

@section('actions')
    {{-- Le bouton est hors du conteneur Alpine du contenu : un evenement window
         garde l'etat «formulaire ouvert» dans un seul endroit. --}}
    <button type="button" class="btn-primary text-xs" x-on:click="window.dispatchEvent(new CustomEvent('ouvrir-formation'))">
        <x-icon name="plus" class="h-4 w-4" />
        Nouvelle formation
    </button>
@endsection

@section('content')

    <div x-data="{ open: false }" x-on:ouvrir-formation.window="open = true">
        <p class="text-sm text-muted">Durée, contenu et modalités restent modifiables (CC §9).</p>

        {{-- Formulaire de creation --}}
        <section class="card mt-6" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
        <h2 class="text-lg font-bold">Ajouter une formation</h2>

        <form method="POST" action="{{ route('admin.formations.store') }}" class="mt-4 space-y-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="new-title" class="field-label">Titre <span class="text-red-500">*</span></label>
                    <input type="text" id="new-title" name="title" value="{{ old('title') }}"
                           @class(['field', 'field-error' => $errors->has('title')]) required>
                    @error('title') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="new-subtitle" class="field-label">Sous-titre</label>
                    <input type="text" id="new-subtitle" name="subtitle" value="{{ old('subtitle') }}" class="field">
                </div>

                <div>
                    <label for="new-duree" class="field-label">Durée</label>
                    <input type="text" id="new-duree" name="duree" value="{{ old('duree') }}" class="field" placeholder="Ex. 2 jours">
                </div>

                <div>
                    <label for="new-public" class="field-label">Public cible</label>
                    <input type="text" id="new-public" name="public_cible" value="{{ old('public_cible') }}" class="field">
                </div>

                <div>
                    <label for="new-modalites" class="field-label">Modalités</label>
                    <input type="text" id="new-modalites" name="modalites" value="{{ old('modalites') }}" class="field" placeholder="Ex. Présentiel ou distanciel">
                </div>

                <div class="sm:col-span-2">
                    <label for="new-description" class="field-label">Description <span class="text-red-500">*</span></label>
                    <textarea id="new-description" name="description" rows="4"
                              @class(['field', 'field-error' => $errors->has('description')]) required>{{ old('description') }}</textarea>
                    @error('description') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="new-objectives" class="field-label">Objectifs (un par ligne)</label>
                    <textarea id="new-objectives" name="objectives_text" rows="4" class="field">{{ old('objectives_text') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="new-programme" class="field-label">Programme détaillé (un module par ligne)</label>
                    <textarea id="new-programme" name="programme_text" rows="5" class="field">{{ old('programme_text') }}</textarea>
                </div>

                <div>
                    <label for="new-order" class="field-label">Ordre d\'affichage</label>
                    <input type="number" id="new-order" name="sort_order" value="{{ old('sort_order') }}" class="field">
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" name="active" value="1" @checked(old('active', true)) class="h-4 w-4 rounded border-line">
                        Formation visible sur le site
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Créer la formation</button>
                <a href="{{ route('admin.formations.index') }}" class="btn-ghost">Annuler</a>
            </div>
        </form>
        </section>

        <div class="mt-6 space-y-4">
            @forelse ($formations as $formation)
            <article class="card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold">{{ $formation['title'] }}</h2>
                            @if (! ($formation['active'] ?? true))
                                <span class="rounded-full bg-canvas px-2.5 py-0.5 text-xs font-semibold text-copy">Masquée</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-muted">
                            {{ $formation['duree'] ?? 'Durée non définie' }}
                            @if (! empty($formation['public_cible']))
                                · {{ $formation['public_cible'] }}
                            @endif
                        </p>
                        <p class="mt-2 line-clamp-2 text-sm text-copy">{{ $formation['description'] ?? '' }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('admin.formations.edit', $formation['id']) }}" class="btn-outline px-3 py-2 text-xs">
                            <x-icon name="pencil" class="h-4 w-4" />
                            Modifier
                        </a>

                        <form method="POST" action="{{ route('admin.formations.destroy', $formation['id']) }}"
                              onsubmit="return confirm('Supprimer définitivement cette formation ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger px-3 py-2 text-xs">
                                <x-icon name="trash" class="h-4 w-4" />
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </article>
            @empty
                <p class="card text-center text-sm text-muted">Aucune formation enregistrée.</p>
            @endforelse
        </div>
    </div>

@endsection

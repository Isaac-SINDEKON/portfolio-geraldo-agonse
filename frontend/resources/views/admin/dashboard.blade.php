@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn-outline text-xs">
        <x-icon name="eye" class="h-4 w-4" />
        Voir le site
    </a>
@endsection

@section('content')

    <p class="text-sm text-slate-500">
        Connecté en tant que {{ $adminUser['email'] ?? session('admin_user.email') ?? 'administrateur' }}.
        Voici l’activité récente du site.
    </p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($stats as $stat)
            <div class="card">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <x-icon :name="$stat['icon']" class="h-5 w-5" />
                </span>
                <p class="mt-4 text-3xl font-extrabold text-slate-900">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        {{-- Dernieres demandes --}}
        <section class="card">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Dernières demandes</h2>
                <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-primary-600">Tout voir</a>
            </div>

            @if (count($recentLeads))
                <ul class="mt-4 divide-y divide-slate-100 text-sm">
                    @foreach ($recentLeads as $lead)
                        <li class="flex items-start justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('admin.leads.show', $lead['id']) }}"
                                   class="font-semibold text-slate-800 hover:text-primary-600">
                                    {{ $lead['data']['organisation'] ?? 'Organisation non précisée' }}
                                </a>
                                <p class="truncate text-slate-500">
                                    {{ $lead['data']['responsable'] ?? '' }}
                                    @if (! empty($lead['data']['theme']))
                                        · {{ $lead['data']['theme'] }}
                                    @endif
                                </p>
                            </div>
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-primary-50 text-primary-700' => ($lead['type'] ?? '') === 'formation',
                                'bg-accent-500/15 text-accent-600' => ($lead['type'] ?? '') === 'devis',
                            ])>
                                {{ ($lead['type'] ?? '') === 'devis' ? 'Devis' : 'Formation' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-4 rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                    Aucune demande reçue pour le moment.
                </p>
            @endif
        </section>

        {{-- Raccourcis et securite --}}
        <div class="space-y-6">
            <section class="card">
                <h2 class="text-lg font-bold">Raccourcis</h2>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <a href="{{ route('admin.content.index') }}" class="btn-outline justify-start text-xs">
                        <x-icon name="settings" class="h-4 w-4" /> Modifier le contenu
                    </a>
                    <a href="{{ route('admin.formations.index') }}" class="btn-outline justify-start text-xs">
                        <x-icon name="layers" class="h-4 w-4" /> Gérer les formations
                    </a>
                    <a href="{{ route('admin.resources.index', ['resource' => 'services']) }}" class="btn-outline justify-start text-xs">
                        <x-icon name="building" class="h-4 w-4" /> Services
                    </a>
                    <a href="{{ route('admin.resources.index', ['resource' => 'experiences']) }}" class="btn-outline justify-start text-xs">
                        <x-icon name="briefcase" class="h-4 w-4" /> Expérience
                    </a>
                    <a href="{{ route('admin.testimonials.index') }}" class="btn-outline justify-start text-xs">
                        <x-icon name="quote" class="h-4 w-4" /> Témoignages
                    </a>
                    <a href="{{ route('admin.gallery.index') }}" class="btn-outline justify-start text-xs">
                        <x-icon name="gallery" class="h-4 w-4" /> Galerie photos
                    </a>
                </div>
            </section>

            <section class="card">
                <h2 class="text-lg font-bold">Mot de passe</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Après un changement de mot de passe, toutes les sessions actives sont révoquées.
                </p>
                <form method="POST" action="{{ route('admin.password.update') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="current_password" class="field-label">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password"
                               @class(['field', 'field-error' => $errors->has('current_password')]) required>
                        @error('current_password') <p class="field-message">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="new_password" class="field-label">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="password"
                               @class(['field', 'field-error' => $errors->has('password')])
                               minlength="8" required>
                        @error('password') <p class="field-message">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="field-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="field" minlength="8" required>
                    </div>
                    <button type="submit" class="btn-primary w-full">Mettre à jour</button>
                </form>
            </section>
        </div>
    </div>

@endsection

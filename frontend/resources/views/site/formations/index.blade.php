@extends('layouts.site')

@section('title', 'Formations – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', 'Formations professionnelles : gestion du temps et des priorités, productivité professionnelle, vente et techniques commerciales, fidélisation de la clientèle. Pour entreprises et ONG.')
@section('canonical', route('formations'))

@section('content')

    @php $domaine = request('domaine'); @endphp

    <section class="bg-slate-900 py-16 sm:py-20">
        <div class="container-x">
            <p class="label-eyebrow text-primary-200">Formations</p>
            <h1 class="mt-3 max-w-3xl text-3xl font-extrabold text-white sm:text-4xl">
                Catalogue de formations professionnelles
            </h1>
            <p class="mt-4 max-w-2xl text-base text-primary-100">
                Quatre programmes clés pour développer les compétences, optimiser la performance et transformer les pratiques de vos équipes.
            </p>
        </div>
    </section>

    <section class="bg-slate-50 py-14 sm:py-20">
        <div class="container-x">
            @if ($domaine)
                <div class="mb-8 flex flex-wrap items-center gap-3">
                    <span class="text-sm text-slate-600">Filtrage par domaine :</span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-sm font-semibold text-primary-700">
                        {{ $domaine }}
                        <a href="{{ route('formations') }}" class="text-primary-500 hover:text-primary-800" aria-label="Retirer le filtre">
                            <x-icon name="close" class="h-4 w-4" />
                        </a>
                    </span>
                </div>
            @endif

            @php
                // Un domaine et sa formation ne partagent pas toujours le meme
                // libelle exact : la comparaison normalisee evite d'afficher une
                // liste vide pour un domaine qui a pourtant sa formation.
                $liste = $domaine
                    ? array_values(array_filter($formations, fn ($f) => \App\Services\SiteContent::memeDomaine($domaine, $f['title'] ?? '')))
                    : $formations;
            @endphp

            @if (count($liste))
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($liste as $formation)
                        <article class="card card-lift flex flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                    <x-icon :name="$formation['icon'] ?? 'target'" />
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    {{ $formation['duree'] }}
                                </span>
                            </div>

                            <h2 class="mt-5 text-xl font-bold leading-snug">{{ $formation['title'] }}</h2>
                            @if (! empty($formation['subtitle']))
                                <p class="mt-1 text-sm font-medium text-primary-600">{{ $formation['subtitle'] }}</p>
                            @endif
                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-600">{{ $formation['description'] }}</p>

                            <dl class="mt-5 space-y-2 border-t border-slate-100 pt-4 text-sm">
                                @if (! empty($formation['public_cible']))
                                    <div class="flex gap-2">
                                        <dt class="shrink-0 font-semibold text-slate-700">Public cible :</dt>
                                        <dd class="text-slate-600">{{ $formation['public_cible'] }}</dd>
                                    </div>
                                @endif
                                @if (! empty($formation['modalites']))
                                    <div class="flex gap-2">
                                        <dt class="shrink-0 font-semibold text-slate-700">Modalités :</dt>
                                        <dd class="text-slate-600">{{ $formation['modalites'] }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ route('formations.show', $formation['slug']) }}" class="btn-primary flex-1">
                                    Voir le programme
                                </a>
                                <a href="{{ route('contact') }}#formulaire-demande" class="btn-outline flex-1">
                                    Demander cette formation
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                {{-- Filtre sans resultat : le visiteur ne doit pas rester bloque
                     sur une page vide, on lui rend le catalogue complet. --}}
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="text-slate-500">
                        Aucune formation n'est rattachée à ce domaine pour le moment.
                    </p>
                    <a href="{{ route('formations') }}" class="btn-outline mt-6">
                        Voir toutes les formations
                    </a>
                </div>
            @endif
        </div>
    </section>

    <x-cta-section />

@endsection

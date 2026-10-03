@extends('layouts.admin')

@section('title', 'Contenu du site')

@section('content')

    @php
        // Point d'entrée du contenu. Le menu latéral offre déjà la même
        // navigation : cette page apporte les compteurs réels et un accès
        // direct, sans obliger à parcourir la barre latérale.
        $sections = [
            [
                'titre' => 'Domaines d’intervention',
                'route' => 'admin.resources.index',
                'params' => ['resource' => 'domains'],
                'icone' => 'target',
                'nombre' => count($site['domains'] ?? []),
                'texte' => 'Les domaines que vous couvrez sur l’accueil.',
            ],
            [
                'titre' => 'Raisons de solliciter',
                'route' => 'admin.resources.index',
                'params' => ['resource' => 'reasons'],
                'icone' => 'sparkles',
                'nombre' => count($site['reasons'] ?? []),
                'texte' => 'Les raisons de vous contacter, affichées sous les domaines.',
            ],
            [
                'titre' => 'Formations',
                'route' => 'admin.formations.index',
                'icone' => 'graduation',
                'nombre' => count($site['formations'] ?? []),
                'texte' => 'Le catalogue : programme, objectifs, publics, durées.',
            ],
            [
                'titre' => 'Services aux entreprises',
                'route' => 'admin.resources.index',
                'params' => ['resource' => 'services'],
                'icone' => 'building',
                'nombre' => count($site['services'] ?? []),
                'texte' => 'Intra-entreprise, ateliers et prestations sur mesure.',
            ],
            [
                'titre' => 'Expérience & expertise',
                'route' => 'admin.resources.index',
                'params' => ['resource' => 'experiences'],
                'icone' => 'briefcase',
                'nombre' => count($site['experiences'] ?? []),
                'texte' => 'Parcours professionnel et résultats mesurés.',
            ],
            [
                'titre' => 'Témoignages',
                'route' => 'admin.testimonials.index',
                'icone' => 'quote',
                'nombre' => count($site['testimonials'] ?? []),
                'texte' => 'Les avis clients, avec la fonction et l’autorisation.',
            ],
            [
                'titre' => 'Galerie photos',
                'route' => 'admin.gallery.index',
                'icone' => 'gallery',
                'nombre' => count($site['gallery'] ?? []),
                'texte' => 'Les photos de vos interventions.',
            ],
        ];
    @endphp

    <p class="max-w-3xl text-sm text-copy">
        Chaque rubrique se modifie sans toucher au code. Les changements sont appliqués
        immédiatement sur le site public.
    </p>

    {{-- Coordonnées : mise en avant car elle porte le numéro WhatsApp et les
         numéros de téléphone visibles par les visiteurs. --}}
    <a href="{{ route('admin.settings.edit') }}"
       class="group mt-6 flex flex-col gap-4 rounded-2xl border border-primary-200 bg-primary-50/60 p-5 transition hover:border-primary-400 hover:bg-primary-50 sm:flex-row sm:items-center">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white">
            <x-icon name="settings" class="h-6 w-6" />
        </span>

        <span class="min-w-0 flex-1">
            <span class="block text-base font-bold text-ink">Présentation &amp; coordonnées</span>
            <span class="mt-1 block text-sm text-copy">
                Nom, fonction, accroche, photos, textes de la page À propos, référencement
                et numéros de téléphone.
            </span>
        </span>

        <span class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary-700">
            Modifier
            <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
        </span>
    </a>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($sections as $section)
            @php $url = route($section['route'], $section['params'] ?? []); @endphp
            <a href="{{ $url }}"
               class="group flex flex-col rounded-2xl border border-line bg-surface p-5 shadow-soft transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">

                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 transition group-hover:bg-primary-600 group-hover:text-white">
                        <x-icon :name="$section['icone']" class="h-5 w-5" />
                    </span>

                    <span class="rounded-full bg-canvas px-2.5 py-1 text-xs font-bold text-copy">
                        {{ $section['nombre'] }} {{ $section['nombre'] > 1 ? 'éléments' : 'élément' }}
                    </span>
                </div>

                <h2 class="mt-4 text-base font-bold text-ink">{{ $section['titre'] }}</h2>
                <p class="mt-1.5 flex-1 text-sm leading-relaxed text-copy">{{ $section['texte'] }}</p>

                <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700">
                    Modifier
                    <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                </span>
            </a>
        @endforeach
    </div>

@endsection

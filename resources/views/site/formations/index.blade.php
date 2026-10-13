@extends('layouts.site')

@section('title', 'Formations – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', 'Formations professionnelles : gestion du temps et des priorités, productivité professionnelle, vente et techniques commerciales, fidélisation de la clientèle. Pour entreprises et ONG.')
@section('canonical', route('formations'))

@section('content')

    @php $domaine = request('domaine'); @endphp

    <section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="grille-technique absolute inset-0"></div>
            <div class="aurora aurora-2 -top-32 right-[8%] h-96 w-96 bg-primary-600/30"></div>
        </div>

        <div class="container-x relative">
            <x-section-title
                variante="sombre"
                align="gauche"
                eyebrow="Formations"
                title="Catalogue de formations professionnelles"
                text="Quatre programmes clés pour développer les compétences, optimiser la performance et transformer les pratiques de vos équipes." />
        </div>
    </section>

    <section class="bg-canvas py-14 sm:py-20">
        <div class="container-x">
            @if ($domaine)
                <div class="mb-8 flex flex-wrap items-center gap-3">
                    <span class="text-sm text-muted">Filtrage par domaine :</span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-sm font-semibold text-primary-700 ring-1 ring-primary-100 ring-inset">
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

// Bento : la premiere carte occupe les deux tiers de la largeur,
                // ce qui met en avant le programme le plus souvent consulte
                // sans avoir a ajouter une couleur supplementaire.
                $formes = [
                    'lg:col-span-7',
                    'lg:col-span-5',
                    'lg:col-span-6',
                    'lg:col-span-6',
                    'lg:col-span-6',
                    'lg:col-span-6',
                ];
            @endphp

            @if (count($liste))
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-12" data-reveal-group data-reveal-step="110">
                    @foreach ($liste as $index => $formation)
                        @php
                            $forme = $formes[$index] ?? 'lg:col-span-6';
                            $grande = $index === 0;
                        @endphp
                        {{-- `$forme` porte la largeur bento. Sans elle, la grille
                             `lg:grid-cols-12` ne recevrait aucune colonne spanned
                             et chaque carte tiendrait sur 1/12 de la largeur. --}}
                        <article class="card card-lift flex flex-col {{ $forme }}"
                                 data-glow>
                            <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                            <div class="relative flex items-start justify-between gap-4">
                                <span @class([
                                    'card-icon',
                                    'h-14 w-14 rounded-2xl' => $grande,
                                ])>
                                    <x-icon :name="$formation['icon'] ?? 'target'" @class(['h-6 w-6' => $grande, 'h-5 w-5' => ! $grande]) />
                                </span>
                                @if (! empty($formation['duree']))
                                    <span class="badge-brand">{{ $formation['duree'] }}</span>
                                @endif
                            </div>

                            <h2 @class([
                                'relative font-bold text-ink',
                                'mt-6 text-2xl leading-tight' => $grande,
                                'mt-5 text-lg leading-snug' => ! $grande,
                            ])>{{ $formation['title'] }}</h2>

                            @if (! empty($formation['subtitle']))
                                <p class="relative mt-2 text-sm font-medium text-primary-600">{{ $formation['subtitle'] }}</p>
                            @endif

                            <p @class([
                                'relative flex-1 leading-relaxed text-copy',
                                'mt-4 text-base' => $grande,
                                'mt-3 text-sm' => ! $grande,
                            ])>{{ $formation['description'] }}</p>

                            @if (! empty($formation['public_cible']) || ! empty($formation['modalites']))
                                <dl class="relative mt-6 space-y-2 border-t border-line-soft pt-5 text-sm">
                                    @if (! empty($formation['public_cible']))
                                        <div class="flex gap-2">
                                            <dt class="shrink-0 font-semibold text-ink">Public cible :</dt>
                                            <dd class="text-copy">{{ $formation['public_cible'] }}</dd>
                                        </div>
                                    @endif
                                    @if (! empty($formation['modalites']))
                                        <div class="flex gap-2">
                                            <dt class="shrink-0 font-semibold text-ink">Modalités :</dt>
                                            <dd class="text-copy">{{ $formation['modalites'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @endif

                            <div class="relative mt-7 flex flex-col gap-3 sm:flex-row">
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
                <div class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center" data-reveal="up">
                    <p class="text-muted">
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
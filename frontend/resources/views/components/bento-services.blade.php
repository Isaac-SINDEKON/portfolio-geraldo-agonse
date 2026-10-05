{{-- Bento Grid : les piliers d'intervention.

     Quatre cartes de tailles inégales qui occupent toute la largeur :

         ┌───────────────────────┬─────────────────────┐
         │  1. carte double      │  2. carte simple     │
         │     (2 lignes)       ├──────────┬──────────┤
         │                       │  3.       │  4.      │
         └───────────────────────┴──────────┴──────────┘

     La grille est explicite en `lg:col-span-*` : c'est le seul endroit du site
     où l'inégalité de taille est voulue, elle porte la lecture « bento ». Sur
     mobile et tablette, les quatre cartes s'empilent à largeur égale — un
     décalage de tailles n'y serait pas lisible. --}}
@props([
    'items' => [],
    'eyebrow' => 'Domaines d’intervention',
    'title' => null,
    'text' => null,
    'lien' => null,
    'libelleLien' => 'Voir toutes les formations',
])

@php
    $s = $site['settings'] ?? [];

    // Quatre cartes guarantees : si l'administration n'a rien saisi, les
    // domaine du catalogue prennent le relais plutôt que d'afficher un vide.
    $liste = array_slice(array_values(array_filter($items, fn ($i) => ! empty($i['title']))), 0, 4);

    if (count($liste) < 4) {
        $liste = array_merge($liste, array_slice($site['domains'] ?? [], 0, 4 - count($liste)));
    }

    // Formats d'animation : purement présentatifs, la grille reste inégale
    // quel que soit le nombre d'éléments réellement disponible.
    $formes = [
        'lg:col-span-6 lg:row-span-2',
        'lg:col-span-6',
        'lg:col-span-3',
        'lg:col-span-3',
    ];

    $reveaux = ['zoom', 'up', 'up', 'up'];
@endphp

<section class="bg-canvas py-20 sm:py-28">
    <div class="container-x">
        <x-section-title :eyebrow="$eyebrow" :title="$title" :text="$text" />

        @if ($lien)
            <div class="mt-6 flex justify-end">
                <a href="{{ $lien }}" class="lien-fleche inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
                    {{ $libelleLien }}
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        @endif

        @forelse ($liste as $index => $item)
            @php
                $forme = $formes[$index] ?? 'lg:col-span-6';
                $grande = $index === 0;
                $reveal = $reveaux[$index] ?? 'up';
            @endphp

            <a href="{{ route('formations', ['domaine' => $item['title'] ?? '']) }}"
               @class([
                   'card card-lift group flex flex-col',
                   $forme,
                   'lg:p-9' => $grande,
               ])
               data-glow
               data-reveal="{{ $reveal }}"
               @if ($index) style="--reveal-delay: {{ $index * 110 }}ms" @endif>

                <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                {{-- Pastille de la carte : dans la grande carte, elle est plus
                     grande et porte un filet d'accent à gauche. --}}
                <span @class([
                    'relative flex items-start justify-between',
                    'gap-6' => $grande,
                ])>
                    <span @class([
                        'card-icon',
                        'h-16 w-16 rounded-2xl' => $grande,
                        'group-hover:bg-primary-600 group-hover:text-on-brand' => true,
                    ])>
                        <x-icon :name="$item['icon'] ?? 'target'" @class(['h-7 w-7' => $grande, 'h-5 w-5' => ! $grande]) />
                    </span>

                    <span @class([
                        'relative font-serif font-bold leading-none tracking-tight',
                        'text-4xl text-line-soft transition-colors duration-500 group-hover:text-primary-400',
                        'hidden sm:block' => ! $grande,
                    ])>
                        0{{ $index + 1 }}
                    </span>
                </span>

                <h3 @class([
                    'relative font-sans font-bold text-ink',
                    'mt-8 text-2xl leading-tight' => $grande,
                    'mt-6 text-lg leading-snug' => ! $grande,
                ])>{{ $item['title'] ?? '' }}</h3>

                <p @class([
                    'relative text-copy',
                    'mt-4 flex-1 text-base leading-relaxed' => $grande,
                    'mt-3 flex-1 text-sm leading-relaxed' => ! $grande,
                ])>{{ $item['description'] ?? '' }}</p>

                @if ($grande)
                    {{-- Formats de livraison : des faits de modalités, pas des
                         promesses chiffrées. --}}
                    <ul class="relative mt-7 flex flex-wrap gap-2">
                        @foreach (['Sur site', 'En intra', 'À distance', 'Sur-mesure'] as $format)
                            <li class="badge-brand">{{ $format }}</li>
                        @endforeach
                    </ul>
                @endif

                <span class="relative mt-7 inline-flex items-center gap-1.5 border-t border-line-soft pt-5 text-sm font-semibold text-primary-600">
                    Voir la formation
                    <x-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                </span>
            </a>
        @empty
            <p class="col-span-full py-12 text-center text-muted">Domaines à venir.</p>
        @endforelse
    </div>
</section>
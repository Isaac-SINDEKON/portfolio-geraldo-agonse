{{-- En-tête de section partagé.

     `align` couvre trois besoins :
       - `duo` (défaut) : titre à gauche, argument à droite, sur deux colonnes.
         C'est ce qui supprime le vide en bas à droite des sections.
       - `gauche` : tout aligné à gauche, pour les sections étroites ou les
         pages éditoriales qui le refusent.
       - `centre` : bloc centré, réservé aux sections de faible largeur.

     `variante` bascule le texte sur fond sombre. Le conteneur `container-x`
     reste à l'appelant : le garder ici doublait les gouttières latérales.

     Dans la colonne de droite, un appel `x-section-title` peut ajouter un lien
     sous l'argument : c'est ce qui remplit cette colonne quand la section n'a
     pas de texte à opposer au titre. --}}
@props([
    'eyebrow' => '',
    'title' => '',
    'text' => '',
    'align' => 'duo',
    'variante' => 'clair',
])

@php
    $sombre = $variante === 'sombre';
    $duo = $align === 'duo';
    $centre = $align === 'centre';
@endphp

<div @class([
    'section-duo' => $duo,
    'text-center mx-auto max-w-2xl' => $centre,
    'max-w-2xl' => $align === 'gauche',
])>
    <div @class(['lg:col-span-7' => $duo, 'mx-auto' => $centre])>
        @if ($eyebrow)
            <p @class([
                'eyebrow inline-flex items-center gap-2',
                'justify-center' => $centre,
            ])>
                <span @class([
                    'h-px w-6',
                    'bg-primary-300' => ! $sombre,
                    'bg-primary-400/70' => $sombre,
                ])></span>
                {{ $eyebrow }}
                <span @class([
                    'h-px w-6',
                    'bg-primary-300' => ! $sombre,
                    'bg-primary-400/70' => $sombre,
                ])></span>
            </p>
        @endif

        <h2 @class([
            'text-3xl font-bold leading-[1.12] tracking-tight text-balance sm:text-4xl lg:text-[2.75rem]',
            'mt-4' => $eyebrow,
            'text-white' => $sombre,
        ])>{{ $title }}</h2>
    </div>

    {{-- Colonne de droite : l'argument, puis le lien éventuel. Les deux
         peuvent être absents : le titre occupe alors toute la largeur. --}}
    @if ($text || ! trim($slot))
        <div @class([
            'lg:col-span-5 lg:pb-1.5' => $duo,
            'lg:text-right' => $duo,
            'mt-4' => ! $duo,
        ])
             data-reveal="right">
            @if ($text)
                <p @class([
                    'text-base leading-relaxed',
                    'text-copy' => ! $sombre,
                    'text-slate-300' => $sombre,
                ])>{{ $text }}</p>
            @endif

            @if (! trim($slot))
                <div @class(['mt-5' => $text])>{{ $slot }}</div>
            @endif
        </div>
    @endif
</div>
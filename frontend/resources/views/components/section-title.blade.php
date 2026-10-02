{{-- Titre de section partagé.

     Le conteneur `container-x` est laissé à l'appelant : toutes les pages
     l'enveloppent déjà, le garder ici doublait les gouttières latérales.
     `align` couvre les sections éditoriales (galerie sombre, parcours) qui
     résistent au centrage, `variante` le texte sur fond sombre. --}}
@props([
    'eyebrow' => '',
    'title' => '',
    'text' => '',
    'align' => 'centre',
    'variante' => 'clair',
])

@php
    $sombre = $variante === 'sombre';
@endphp

<div @class([
    'max-w-2xl',
    'text-center' => $align === 'centre',
    'mx-auto' => $align === 'centre',
])>
    @if ($eyebrow)
        <p @class([
            'eyebrow inline-flex items-center gap-2',
            'justify-center' => $align === 'centre',
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
        'mt-4 text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl',
        'text-slate-900' => ! $sombre,
        'text-white' => $sombre,
    ])>{{ $title }}</h2>

    @if ($text)
        <p @class([
            'mt-4 text-base leading-relaxed',
            'text-slate-600' => ! $sombre,
            'text-slate-300' => $sombre,
        ])>{{ $text }}</p>
    @endif
</div>
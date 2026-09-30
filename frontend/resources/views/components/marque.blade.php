@props([
    'variante' => 'entete',
])

{{-- Marque du site : le logo televerse par l'administrateur, sinon les
     initiales "GA".

     Le meme logo est utilise partout (site public et administration) pour que
     l'identite reste identique quel que soit l'ecran. Chaque variante reprend
     les dimensions d'origine de son emplacement, afin que le repli sur les
     initiales soit strictement identique a ce qui etait affiche avant
     l'existence de ce reglage. --}}

@php
    $variantes = [
        'entete' => [
            'cadre' => 'h-10 w-10 rounded-xl',
            'initiales' => 'text-sm',
            'fond' => 'bg-primary-600 text-white',
        ],
        'navigation' => [
            'cadre' => 'h-9 w-9 rounded-xl',
            'initiales' => 'text-xs',
            'fond' => 'bg-primary-600 text-white',
        ],
        'connexion' => [
            'cadre' => 'mx-auto h-14 w-14 rounded-2xl',
            'initiales' => 'text-lg',
            'fond' => 'bg-white/15 text-white',
        ],
        'portrait-sombre' => [
            'cadre' => 'h-24 w-24 rounded-full',
            'initiales' => 'text-3xl',
            'fond' => 'bg-white/10 text-white',
        ],
        'portrait-clair' => [
            'cadre' => 'h-24 w-24 rounded-full',
            'initiales' => 'text-3xl',
            'fond' => 'bg-primary-100 text-primary-700',
        ],
    ];

    $variante = $variantes[$variante] ?? $variantes['entete'];

    $chemin = $site['settings']['logo'] ?? null;
    $url = $content->imageUrl(is_scalar($chemin) ? (string) $chemin : null);
    $nom = $site['settings']['name'] ?? config('app.name');
@endphp

@if ($url)
    {{-- object-contain : un logo n'est pas comme une photo, il ne doit jamais
         etre rogne par le cadre. --}}
    <img src="{{ $url }}" alt="{{ $nom }}"
         {{ $attributes->merge(['class' => trim($variante['cadre'].' shrink-0 object-contain')]) }}>
@else
    <span {{ $attributes->merge(['class' => trim('flex shrink-0 items-center justify-center font-extrabold '.$variante['fond'].' '.$variante['cadre'].' '.$variante['initiales'])]) }}>GA</span>
@endif

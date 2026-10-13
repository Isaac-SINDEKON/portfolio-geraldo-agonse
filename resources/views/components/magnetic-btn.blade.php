{{-- Bouton magnétique.

     L'attraction est écrite par app.js dans `--magnet-x` / `--magnet-y`. Le
     halo qui l'accompagne utilise les mêmes coordonnées (`--glow-x` / `--glow-y`)
     : un seul survol suffit donc à faire bouger le bouton et à allumer sa
     lueur dans le même mouvement.

     Le déplacement est porté par un élément conteneur, jamais par le lien
     lui-même : le lien garde ainsi ses propres états `:hover` et `:active`. --}}
@props([
    'href' => '#',
    'variante' => 'primaire',
    'icone' => null,
    'force' => 0.25,
    'externe' => false,
    'bloc' => false,
    'class' => '',
])

@php
    $classes = match ($variante) {
        'whatsapp' => 'btn-whatsapp',
        'accent' => 'btn-accent',
        'light' => 'btn-light',
        'outline' => 'btn-outline',
        default => 'btn-primary',
    };
@endphp

{{-- `bloc` occupe toute la largeur du conteneur. Sans lui, deux boutons
     magnétiques côte à côte gardent chacun leur largeur naturelle. --}}
<span @class([
    'relative',
    'inline-flex' => ! $bloc,
    'flex w-full' => $bloc,
])
      data-magnet="{{ $force }}"
      data-glow>
    {{-- Lueur : elle déborde volontairement le bouton pour lire comme un halo
         et non comme un simple fond. --}}
    <span class="halo-survol pointer-events-none absolute -inset-3 rounded-[1.75rem] blur-sm"
          aria-hidden="true"></span>

    <a href="{{ $href }}"
       @if ($externe) target="_blank" rel="noopener" @endif
       {{ $attributes->merge(['class' => trim($classes.' '.$class)]) }}>
        @if ($icone)
            <x-icon :name="$icone" class="h-4 w-4" />
        @endif
        {{ $slot }}
    </a>
</span>
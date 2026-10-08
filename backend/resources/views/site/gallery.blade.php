@extends('layouts.site', [
    'title' => 'Galerie – '.$site['settings']['name'],
    'description' => $site['settings']['gallery_text'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
    $items = array_values(array_filter(array_map(
        fn ($img) => [
            'url' => $content->imageUrl($img['image_path'] ?? $img['image_url'] ?? null),
            'caption' => $img['caption'] ?? '',
        ],
        $site['gallery'] ?? []
    ), fn ($img) => ! empty($img['url'])));

    // Grille reguliere : toutes les photos en carrés de meme taille, la
    // premiere n'est plus mise en avant en 2x2 (trop imposante, surtout
    // sur mobile ou elle occupait toute la largeur).
@endphp

{{-- EN-TÊTE (CC §13) --}}
<section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-2 -top-28 right-[8%] h-80 w-80 bg-primary-600/30"></div>
    </div>

    <div class="container-x relative">
        <nav aria-label="Fil d'Ariane" class="text-xs text-on-nuit-doux">
            <a href="{{ route('home') }}" class="transition hover:text-on-nuit-vif">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Galerie</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-12 lg:items-end lg:gap-10">
            <div class="lg:col-span-7">
                <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-[2.75rem]">
                    {{ $s['gallery_title'] ?? 'Galerie' }}
                </h1>
                <p class="mt-4 max-w-2xl text-base text-on-nuit-doux">
                    {{ $s['gallery_text'] ?? 'Un aperçu de mon univers professionnel : formations, ateliers et échanges.' }}
                </p>
            </div>

            <div class="lg:col-span-5 lg:text-right">
                <x-magnetic-btn :href="route('contact', ['form' => 'formation'])" variante="light" icone="arrow-right">
                    Organiser une session
                </x-magnetic-btn>
            </div>
        </div>
    </div>
</section>

{{-- GRILLE + VISIONNEUSE (Alpine.js) --}}
<section class="bg-canvas py-16 sm:py-20">
    <div class="container-x" x-data="galleryViewer(@js($items))" @keydown.escape="close()" @keydown.arrow-right="next()" @keydown.arrow-left="previous()">
        @if (count($items))
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($items as $index => $item)
                    <button type="button" @click="open({{ $index }})"
                            class="group relative aspect-square overflow-hidden rounded-2xl bg-surface ring-1 ring-line-soft transition hover:-translate-y-1 hover:ring-primary-400"
                            aria-label="Agrandir l'image {{ $index + 1 }}">
                        <img src="{{ $item['url'] }}" alt="{{ $item['caption'] ?: 'Image '.($index + 1) }}"
                             loading="lazy"
                             x-on:error="$el.closest('button')?.querySelector('[data-gallery-fallback]')?.classList.remove('hidden')"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <span data-gallery-fallback hidden
                              class="absolute inset-0 hidden flex-col items-center justify-center gap-2 bg-surface p-4 text-center text-xs text-muted">
                            <x-icon name="gallery" class="h-8 w-8 text-muted" />
                            Image indisponible
                        </span>
                        <span class="absolute inset-0 flex items-center justify-center bg-nuit/0 opacity-0 transition group-hover:bg-nuit/60 group-hover:opacity-100">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M21 21l-4.3-4.3M11 19a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </span>
                        @if ($item['caption'])
                            <span class="absolute inset-x-0 bottom-0 translate-y-1 bg-gradient-to-t from-nuit/90 via-nuit/60 to-transparent p-3 text-left text-xs font-medium text-white opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100">
                                {{ $item['caption'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        @else
            <p class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center text-muted">
                Les photos seront ajoutées prochainement depuis l'administration.
            </p>
        @endif

        {{-- Visionneuse plein écran --}}
        <div x-show="current" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-nuit/95 p-4"
             role="dialog" aria-modal="true" aria-label="Visionneuse d'images">
            <button type="button" @click="close()"
                    class="absolute top-4 right-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
                    aria-label="Fermer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>

            <button type="button" @click="previous()"
                    class="absolute left-3 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-6"
                    aria-label="Image précédente">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M15 6l-6 6 6 6" />
                </svg>
            </button>

            <button type="button" @click="next()"
                    class="absolute right-3 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-6"
                    aria-label="Image suivante">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 6l6 6-6 6" />
                </svg>
            </button>

            <figure class="max-h-full max-w-4xl text-center">
                <img :src="current?.url" :alt="current?.caption || 'Image'" class="mx-auto max-h-[75vh] w-auto rounded-2xl object-contain">
                <figcaption x-show="current?.caption" x-text="current?.caption" class="mt-4 text-sm text-slate-300"></figcaption>
            </figure>
        </div>
    </div>
</section>

<x-cta-section />
@endsection
@extends('layouts.site', [
    'title' => $site['settings']['seo_title'] ?? 'Géraldo Perridys AGONSE – Formateur professionnel',
    'description' => $site['settings']['seo_description'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
@endphp

{{-- HERO (CC §7) --}}
<section class="relative overflow-hidden bg-gradient-to-br from-primary-800 via-primary-700 to-primary-600">
    <div class="pointer-events-none absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-white"></div>
        <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-white"></div>
    </div>

    <div class="container-x relative py-16 sm:py-24 lg:py-28">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="animate-rise">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-semibold text-white">
                    <span class="h-2 w-2 rounded-full bg-whatsapp"></span>
                    Disponible pour vos formations
                </p>

                <h1 class="mt-6 text-4xl leading-tight font-extrabold text-white sm:text-5xl">
                    {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                </h1>

                <p class="mt-3 text-lg font-semibold text-primary-200 sm:text-xl">
                    {{ $s['role'] ?? 'Formateur' }}
                </p>

                <p class="mt-6 max-w-xl text-lg leading-relaxed font-medium text-white sm:text-xl">
                    {{ $s['tagline'] ?? '' }}
                </p>

                @if (! empty($s['hero_text']))
                    <p class="mt-5 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        {{ $s['hero_text'] }}
                    </p>
                @endif

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('contact', ['form' => 'formation']) }}" class="btn bg-white text-primary-700 hover:bg-primary-50">
                        Demander une formation
                    </a>
                    <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-whatsapp">
                        Me contacter sur WhatsApp
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-primary-100">
                    <a href="{{ $content->telUrl() }}" class="flex items-center gap-2 transition hover:text-white">
                        <x-icon name="phone" class="h-4 w-4" />
                        {{ $s['phone_display'] ?? $s['phone'] ?? '' }}
                    </a>
                    <a href="mailto:{{ $s['email'] ?? '' }}" class="flex items-center gap-2 break-all transition hover:text-white">
                        <x-icon name="mail" class="h-4 w-4" />
                        {{ $s['email'] ?? '' }}
                    </a>
                </div>
            </div>

            <div class="mx-auto w-full max-w-md animate-rise">
                @php $heroPhoto = $content->imageUrl($s['hero_photo'] ?? null); @endphp
                <div class="aspect-4/5 overflow-hidden rounded-3xl border-8 border-white/20 bg-primary-900/40 shadow-2xl"
                     x-data="{ failed: false }">
                    <img src="{{ $heroPhoto }}"
                         alt="{{ $s['name'] ?? '' }}"
                         class="h-full w-full object-cover"
                         fetchpriority="high"
                         x-show="! failed"
                         x-on:error="failed = true">

                    {{-- Repli : initiales, si aucune photo n'est téléversée ou si le fichier est introuvable --}}
                    <div class="flex h-full w-full flex-col items-center justify-center gap-4 p-6 text-center text-white"
                         x-show="failed || ! @js((bool) $heroPhoto)">
                        <span class="flex h-24 w-24 items-center justify-center rounded-full bg-white/10 text-3xl font-extrabold">GA</span>
                        <p class="text-sm text-primary-100">{{ $s['name'] ?? '' }}</p>
                        <p class="text-xs tracking-widest text-primary-300 uppercase">{{ $s['role'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- DOMAINES D'INTERVENTION (CC §7) --}}
<section class="bg-white py-16 sm:py-20">
    <div class="container-x">
        <x-section-title
            eyebrow="Domaines d’intervention"
            title="Quatre expertises pour transformer vos pratiques"
            text="Des programmes conçus pour répondre aux enjeux concrets de votre organisation." />

        <div class="stagger mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($domains as $domain)
                <a href="{{ route('formations', ['domaine' => $domain['title']]) }}"
                   class="card group transition hover:-translate-y-1 hover:border-primary-300">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                        <x-icon :name="$domain['icon'] ?? 'target'" />
                    </span>
                    <h3 class="mt-5 text-lg leading-snug font-bold">{{ $domain['title'] }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $domain['description'] }}</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                        Voir la formation
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </span>
                </a>
            @empty
                <p class="col-span-full text-center text-slate-500">Domaines à venir.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- RAISONS DE SOLLICITER LE FORMATEUR (CC §7) --}}
<section class="bg-slate-50 py-16 sm:py-20">
    <div class="container-x">
        <x-section-title
            eyebrow="Pourquoi me choisir"
            title="Un formateur opérationnel, pas seulement théorique"
            text="Une approche concrète, adaptée à votre réalité, avec des résultats visibles." />

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @foreach ($reasons as $reason)
                <div class="flex gap-5 rounded-2xl bg-white p-6 shadow-soft">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white">
                        <x-icon :name="$reason['icon'] ?? 'sparkles'" />
                    </span>
                    <div>
                        <h3 class="text-lg font-bold">{{ $reason['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $reason['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- APERÇU DES FORMATIONS --}}
@if (count($formations))
    <section class="bg-white py-16 sm:py-20">
        <div class="container-x">
            <x-section-title
                eyebrow="Catalogue de formations"
                title="Les formations les plus demandées"
                text="Quatre programmes clés pour renforcer la performance de vos équipes." />

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                @foreach ($formations as $formation)
                    <a href="{{ route('formations.show', $formation['slug']) }}"
                       class="card group flex flex-col transition hover:-translate-y-1 hover:border-primary-300">
                        <div class="flex items-start justify-between gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                                <x-icon :name="$formation['icon'] ?? 'target'" />
                            </span>
                            @if (! empty($formation['duree']))
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $formation['duree'] }}</span>
                            @endif
                        </div>
                        <h3 class="mt-5 text-xl leading-snug font-bold">{{ $formation['title'] }}</h3>
                        @if (! empty($formation['subtitle']))
                            <p class="mt-1 text-sm font-medium text-primary-600">{{ $formation['subtitle'] }}</p>
                        @endif
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $formation['description'] }}</p>
                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                            Détail de la formation
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('formations') }}" class="btn-outline">Voir toutes les formations</a>
            </div>
        </div>
    </section>
@endif

{{-- TÉMOIGNAGES --}}
@if (count($testimonials))
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="container-x">
            <x-section-title eyebrow="Ils me font confiance" title="Témoignages de clients satisfaits" />

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="flex h-full flex-col rounded-2xl bg-white p-6 shadow-soft">
                        <x-icon name="quote" class="h-8 w-8 text-primary-200" />
                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">
                            « {{ $t['content'] }} »
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                            @if (! empty($t['photo_url']))
                                <img src="{{ $t['photo_url'] }}" alt="{{ $t['author'] }}" loading="lazy"
                                     class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700">
                                    {{ \Illuminate\Support\Str::limit($t['author'], 2, '') }}
                                </span>
                            @endif
                            <span>
                                <span class="block text-sm font-bold text-slate-900">{{ $t['author'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $t['fonction'] }}</span>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('testimonials') }}" class="btn-outline">Lire tous les témoignages</a>
            </div>
        </div>
    </section>
@endif

{{-- APPEL À L'ACTION FINAL (CC §7) --}}
<x-cta-section />
@endsection

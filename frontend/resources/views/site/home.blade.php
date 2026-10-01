@extends('layouts.site', [
    'title' => $site['settings']['seo_title'] ?? 'Géraldo Perridys AGONSE – Formateur professionnel',
    'description' => $site['settings']['seo_description'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
@endphp

{{-- HERO (CC §7) — signature asymétrique Biztar --}}
<section class="relative overflow-hidden bg-white">
    {{-- Formes d'arrière-plan très discrètes, propres à la section --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-primary-50"></div>
    </div>

    <div class="container-x relative py-20 lg:py-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
            <div class="animate-rise">
                <p class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-100 ring-inset">
                    <span class="h-2 w-2 rounded-full bg-primary-600"></span>
                    {{ $s['role'] ?? 'Formateur Professionnel' }}
                </p>

                <h1 class="mt-6 text-4xl leading-[1.1] font-extrabold tracking-tight text-slate-900 lg:text-6xl">
                    {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                </h1>

                @if (! empty($s['tagline']))
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                        {{ $s['tagline'] }}
                    </p>
                @endif

                @if (! empty($s['hero_text']))
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-500">
                        {{ $s['hero_text'] }}
                    </p>
                @endif

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('contact', ['form' => 'formation']) }}" class="btn-primary">
                        Demander une formation
                    </a>
                    <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                       class="btn-outline hover:border-whatsapp hover:text-whatsapp">
                        <x-icon name="whatsapp" class="h-4 w-4 text-whatsapp" />
                        Me contacter sur WhatsApp
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                    <a href="{{ $content->telUrl() }}" class="flex items-center gap-2 transition-colors hover:text-primary-600">
                        <x-icon name="phone" class="h-4 w-4 text-slate-400" />
                        {{ $s['phone_display'] ?? $s['phone'] ?? '' }}
                    </a>
                    <a href="mailto:{{ $s['email'] ?? '' }}" class="flex items-center gap-2 break-all transition-colors hover:text-primary-600">
                        <x-icon name="mail" class="h-4 w-4 text-slate-400" />
                        {{ $s['email'] ?? '' }}
                    </a>
                </div>
            </div>

            <div class="animate-rise mx-auto w-full max-w-md lg:max-w-none">
                @php $heroPhoto = $content->imageUrl($s['hero_photo'] ?? null); @endphp

                <div class="relative">
                    {{-- Forme géométrique d'accentuation, placée juste derrière
                         la photo pour créer la profondeur Biztar. --}}
                    <div class="absolute -top-5 -right-5 h-28 w-28 rounded-2xl bg-saffron-400/70 lg:h-32 lg:w-32"
                         aria-hidden="true"></div>
                    <div class="absolute -bottom-8 -left-8 h-44 w-44 rounded-3xl bg-primary-600/10 lg:h-56 lg:w-56"
                         aria-hidden="true"></div>

                    <div class="relative aspect-4/5 overflow-hidden rounded-2xl bg-slate-100 shadow-xl"
                         x-data="{ failed: false }">
                        <img src="{{ $heroPhoto }}"
                             alt="{{ $s['name'] ?? '' }}"
                             class="h-full w-full object-cover"
                             fetchpriority="high"
                             x-show="! failed"
                             x-on:error="failed = true">

                        {{-- Repli : initiales, si aucune photo n'est téléversée ou si le fichier est introuvable --}}
                        <div class="flex h-full w-full flex-col items-center justify-center gap-4 bg-slate-50 p-6 text-center"
                             x-show="failed || ! @js((bool) $heroPhoto)">
                            <x-marque variante="portrait-clair" />
                            <p class="text-sm font-semibold text-slate-900">{{ $s['name'] ?? '' }}</p>
                            <p class="text-xs tracking-widest text-slate-500 uppercase">{{ $s['role'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BANDEAU DE STATISTIQUES --}}
<section class="bg-white pb-16 sm:pb-20">
    <div class="container-x">
        <div class="rounded-2xl bg-slate-900 px-6 py-10 text-white sm:px-10">
            <dl class="grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
                <div>
                    <dt class="stat-label">{{ $s['stat_1_label'] ?? 'Années d\'expérience' }}</dt>
                    <dd class="stat-nombre mt-2">{{ $s['stat_1_value'] ?? '10+' }}</dd>
                </div>
                <div>
                    <dt class="stat-label">{{ $s['stat_2_label'] ?? 'Formations animées' }}</dt>
                    <dd class="stat-nombre mt-2">{{ $s['stat_2_value'] ?? '120+' }}</dd>
                </div>
                <div>
                    <dt class="stat-label">{{ $s['stat_3_label'] ?? 'Professionnels formés' }}</dt>
                    <dd class="stat-nombre mt-2">{{ $s['stat_3_value'] ?? '2 000+' }}</dd>
                </div>
                <div>
                    <dt class="stat-label">{{ $s['stat_4_label'] ?? 'Organisations accompagnées' }}</dt>
                    <dd class="stat-nombre mt-2">{{ $s['stat_4_value'] ?? '50+' }}</dd>
                </div>
            </dl>
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

        <div class="stagger mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            @forelse ($domains as $domain)
                <a href="{{ route('formations', ['domaine' => $domain['title']]) }}"
                   class="card card-lift group flex flex-col">
                    <span class="card-icon group-hover:bg-primary-600 group-hover:text-white">
                        <x-icon :name="$domain['icon'] ?? 'target'" />
                    </span>
                    <h3 class="mt-5 text-lg leading-snug font-bold">{{ $domain['title'] }}</h3>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $domain['description'] }}</p>
                    <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                        Voir la formation
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

        <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2">
            @foreach ($reasons as $reason)
                <div class="card flex gap-5">
                    <span class="card-icon-solid">
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

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($formations as $formation)
                    <a href="{{ route('formations.show', $formation['slug']) }}"
                       class="card card-lift group flex flex-col">
                        <div class="flex items-start justify-between gap-4">
                            <span class="card-icon group-hover:bg-primary-600 group-hover:text-white">
                                <x-icon :name="$formation['icon'] ?? 'target'" />
                            </span>
                            @if (! empty($formation['duree']))
                                <span class="badge-saffron">{{ $formation['duree'] }}</span>
                            @endif
                        </div>
                        <h3 class="mt-5 text-lg leading-snug font-bold">{{ $formation['title'] }}</h3>
                        @if (! empty($formation['subtitle']))
                            <p class="mt-2 text-sm font-medium text-primary-600">{{ $formation['subtitle'] }}</p>
                        @endif
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $formation['description'] }}</p>
                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                            Détail de la formation
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="card flex h-full flex-col">
                        <x-icon name="quote" class="h-8 w-8 text-primary-200" />
                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                            « {{ $t['content'] }} »
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                            @if (! empty($t['photo_url']))
                                <img src="{{ $t['photo_url'] }}" alt="{{ $t['author'] }}" loading="lazy"
                                     class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 text-sm font-bold text-primary-700">
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

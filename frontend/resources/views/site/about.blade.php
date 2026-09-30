@extends('layouts.site', [
    'title' => 'À propos – '.($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'),
    'description' => $site['settings']['about_approche'] ?? null,
    'image' => $site['settings']['about_photo'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
    $aboutBlocks = array_filter([
        ['title' => 'Mon approche', 'icon' => 'sparkles', 'text' => $s['about_approche'] ?? ''],
        ['title' => 'Mon expertise', 'icon' => 'target', 'text' => $s['about_expertise'] ?? ''],
        ['title' => 'Mon parcours', 'icon' => 'graduation', 'text' => $s['about_parcours'] ?? ''],
    ]);
    $qualifications = App\Services\SiteContent::linesToArray($s['about_qualifications'] ?? '');
@endphp

{{-- EN-TÊTE DE PAGE --}}
<section class="bg-slate-50 py-14 sm:py-20">
    <div class="container-x">
        <nav aria-label="Fil d'Ariane" class="text-xs text-slate-500">
            <a href="{{ route('home') }}" class="transition hover:text-primary-600">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">À propos</span>
        </nav>

        <div class="mt-6 max-w-3xl">
            <p class="eyebrow">À propos</p>
            <h1 class="mt-3 text-3xl font-extrabold sm:text-4xl">
                {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
            </h1>
            <p class="mt-2 text-lg font-semibold text-primary-600">{{ $s['role'] ?? 'Formateur' }}</p>
            <p class="mt-5 text-base leading-relaxed text-slate-600">{{ $s['tagline'] ?? '' }}</p>
        </div>
    </div>
</section>

{{-- PRÉSENTATION + PHOTO (CC §8) --}}
<section class="bg-white py-16 sm:py-20">
    <div class="container-x grid items-center gap-12 lg:grid-cols-2">
        <div class="order-2 lg:order-1">
            @foreach ($aboutBlocks as $block)
                <div class="mb-10 last:mb-0">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                            <x-icon :name="$block['icon']" class="h-5 w-5" />
                        </span>
                        <h2 class="text-xl font-bold">{{ $block['title'] }}</h2>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $block['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="order-1 lg:order-2">
            @php $aboutPhoto = $content->imageUrl($s['about_photo'] ?? null); @endphp
            {{-- Cadre fixe : reserve la place de la photo avant son chargement,
                 ce qui evite tout saut de mise en page (CC §24). --}}
            <div class="aspect-4/5 overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-soft"
                 x-data="{ failed: false }">
                <img src="{{ $aboutPhoto }}"
                     alt="{{ $s['name'] ?? '' }}"
                     class="h-full w-full object-cover"
                     loading="lazy"
                     decoding="async"
                     x-show="! failed"
                     x-on:error="failed = true">

                {{-- Repli : initiales, si aucune photo de profil n'est téléversée (modifiable en administration) --}}
                <div class="flex aspect-4/5 flex-col items-center justify-center gap-4 p-6 text-center"
                     x-show="failed || ! @js((bool) $aboutPhoto)">
                    <x-marque variante="portrait-clair" />
                    <p class="text-sm font-semibold text-slate-700">{{ $s['name'] ?? '' }}</p>
                    <p class="text-xs tracking-widest text-slate-400 uppercase">{{ $s['role'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if (count($qualifications))
    {{-- DIPLÔMES ET CERTIFICATIONS (CC §8) --}}
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="container-x">
            <x-section-title
                eyebrow="Diplômes et certifications"
                title="Mes qualifications"
                text="Un socle théorique solide, complété par une expérience terrain." />

            <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($qualifications as $qualification)
                    <li class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-soft">
                        <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-accent-600" />
                        <span class="text-sm font-medium text-slate-700">{{ $qualification }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

{{-- RAISONS DE SOLLICITER (rappel) --}}
@if (count($site['reasons'] ?? []))
    <section class="bg-white py-16 sm:py-20">
        <div class="container-x">
            <x-section-title eyebrow="Pourquoi me solliciter" title="Ce que je brings à vos équipes" />

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                @foreach ($site['reasons'] as $reason)
                    <div class="flex gap-5 rounded-2xl border border-slate-200 p-6">
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
@endif

<x-cta-section />
@endsection

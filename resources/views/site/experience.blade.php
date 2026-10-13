@extends('layouts.site', [
    'title' => 'Expérience et expertise – '.$site['settings']['name'],
    'description' => 'Parcours, expérience terrain et domaines d\'expertise du formateur.',
])

@section('content')
{{-- EN-TÊTE (CC §9) --}}
<section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-3 -top-28 right-[10%] h-80 w-80 bg-primary-600/25"></div>
    </div>

    <div class="container-x relative">
        <nav aria-label="Fil d'Ariane" class="text-xs text-on-nuit-doux">
            <a href="{{ route('home') }}" class="transition hover:text-on-nuit-vif">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Expérience &amp; expertise</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-12 lg:items-end lg:gap-10">
            <div class="lg:col-span-7">
                <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-[2.75rem]">Expérience &amp; expertise</h1>
                <p class="mt-4 max-w-2xl text-base text-on-nuit-doux">
                    Une expérience opérationnelle, un réseau et des compétences éprouvées au service de votre performance.
                </p>
            </div>

            <div class="lg:col-span-5 lg:text-right">
                <x-magnetic-btn :href="route('contact', ['form' => 'devis'])" variante="light" icone="arrow-right">
                    Échanger sur mon parcours
                </x-magnetic-btn>
            </div>
        </div>
    </div>
</section>

{{-- PARCOURS --}}
<section class="bg-surface py-16 sm:py-20">
    <div class="container-x">
        <x-section-title eyebrow="Mon parcours" title="Une expérience construite sur le terrain"
                         text="Chaque poste a ajouté une compétence que les formations distribuent aujourd'hui." />

        <ol class="mt-12 space-y-6" data-reveal-group data-reveal-step="110">
            @forelse ($site['experiences'] ?? [] as $index => $experience)
                <li class="relative flex gap-6 pl-0 sm:pl-2">
                    {{-- Trait de la frise chronologique --}}
                    @if (! $loop->last)
                        <span class="absolute top-12 left-[1.35rem] hidden h-full w-0.5 bg-line sm:block" aria-hidden="true"></span>
                    @endif

                    <span class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-600 text-on-brand">
                        <x-icon :name="$experience['icon'] ?? 'briefcase'" class="h-5 w-5" />
                    </span>

                    <div class="card flex-1">
                        <h2 class="text-lg font-bold">{{ $experience['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-copy">{{ $experience['description'] }}</p>
                    </div>
                </li>
            @empty
                <p class="text-center text-muted">L'expérience détaillée sera bientôt disponible.</p>
            @endforelse
        </ol>
    </div>
</section>

{{-- COMPÉTENCES PAR DOMAINE : bento de cartes inégales --}}
@if (count($site['domains'] ?? []))
    <section class="bg-canvas py-16 sm:py-20">
        <div class="container-x">
            <x-section-title eyebrow="Expertise" title="Mes domaines de compétence"
                             text="Des terrains d'intervention différents, une même exigence de concret." />

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-12">
                @foreach ($site['domains'] as $index => $domain)
                    <a href="{{ route('formations', ['domaine' => $domain['title']]) }}"
                       @class([
                           'card card-lift group text-center',
                           'lg:col-span-7' => $index === 0,
                           'lg:col-span-5' => $index === 1,
                           'lg:col-span-6' => $index >= 2,
                       ])>
                        <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                        <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-on-brand">
                            <x-icon :name="$domain['icon'] ?? 'target'" class="h-6 w-6" />
                        </span>
                        <h3 class="relative mt-5 text-base font-bold">{{ $domain['title'] }}</h3>
                        <p class="relative mt-2 text-sm text-copy">{{ $domain['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<x-cta-section />
@endsection
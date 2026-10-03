@extends('layouts.site', [
    'title' => 'Services aux entreprises – '.$site['settings']['name'],
    'description' => 'Audit, plan de développement des compétences, ateliers et suivi post-formation pour les entreprises.',
])

@section('content')
@php
    $s = $site['settings'] ?? [];
@endphp

{{-- EN-TÊTE (CC §16) --}}
<section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-1 -top-28 left-[6%] h-80 w-80 bg-primary-600/30"></div>
    </div>

    <div class="container-x relative">
        <nav aria-label="Fil d'Ariane" class="text-xs text-primary-200">
            <a href="{{ route('home') }}" class="transition hover:text-primary-300">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Services aux entreprises</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-12 lg:items-end lg:gap-10">
            <div class="lg:col-span-7">
                <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-[2.75rem]">Services aux entreprises</h1>
                <p class="mt-4 max-w-2xl text-base text-primary-100">
                    Des prestations sur mesure pour armer vos équipes et transformer durablement vos pratiques.
                </p>
            </div>

            <div class="lg:col-span-5 lg:text-right">
                <x-magnetic-btn :href="route('contact', ['form' => 'devis'])" variante="light" icone="arrow-right">
                    Demander un devis
                </x-magnetic-btn>
            </div>
        </div>
    </div>
</section>

{{-- LISTE DES SERVICES : bento de cartes inégales --}}
<section class="bg-surface py-16 sm:py-20">
    <div class="container-x">
        <x-section-title
            eyebrow="Ce que je propose"
            title="Des formats d’intervention adaptés"
            text="Du format ponctuel à l'accompagnement dans la durée, chaque prestation est construite sur votre contexte." />

        <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-12" data-reveal-group data-reveal-step="110">
            @forelse ($site['services'] ?? [] as $index => $service)
                <div @class([
                    'card card-lift flex gap-5',
                    'lg:col-span-7' => $index === 0,
                    'lg:col-span-5' => $index === 1,
                    'lg:col-span-6' => $index >= 2,
                ])>
                    <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                    <span class="card-icon-solid relative h-14 w-14 shrink-0 rounded-2xl">
                        <x-icon :name="$service['icon'] ?? 'building'" class="h-6 w-6" />
                    </span>
                    <div class="relative">
                        <h2 class="text-lg leading-snug font-bold">{{ $service['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-copy">{{ $service['description'] }}</p>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-muted">Les services seront bientôt disponibles.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- PROCESSUS (CC §16) --}}
@php
    $process = [
        ['title' => '1. Prise de contact', 'text' => 'Vous décrivez votre besoin, votre contexte et vos objectifs par téléphone, email ou WhatsApp.'],
        ['title' => '2. Diagnostic', 'text' => 'Analyse de vos besoins, de vos équipes et identification des écarts à combler.'],
        ['title' => '3. Proposition sur mesure', 'text' => 'Vous recevez un programme, un format et un tarif adaptés à votre organisation.'],
        ['title' => '4. Formation et suivi', 'text' => 'Animation de la formation puis accompagnement et évaluation des acquis.'],
    ];
@endphp

<section class="bg-canvas py-16 sm:py-20">
    <div class="container-x">
        <x-section-title eyebrow="Comment ça se passe" title="Un accompagnement en quatre étapes"
                         text="Un interlocuteur unique du premier échange jusqu'au suivi final." />

        <ol class="mt-12 grid gap-6 md:grid-cols-4">
            @foreach ($process as $step)
                <li class="card">
                    <h3 class="text-base font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-copy">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<x-cta-section />
@endsection

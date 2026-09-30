@extends('layouts.site', [
    'title' => 'Services aux entreprises – '.$site['settings']['name'],
    'description' => 'Audit, plan de développement des compétences, ateliers et suivi post-formation pour les entreprises.',
])

@section('content')
@php
    $s = $site['settings'] ?? [];
@endphp

{{-- EN-TÊTE (CC §16) --}}
<section class="bg-gradient-to-br from-primary-800 to-primary-600 py-14 sm:py-20">
    <div class="container-x">
        <nav aria-label="Fil d'Ariane" class="text-xs text-primary-200">
            <a href="{{ route('home') }}" class="transition hover:text-white">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Services aux entreprises</span>
        </nav>
        <h1 class="mt-5 text-3xl font-extrabold text-white sm:text-4xl">Services aux entreprises</h1>
        <p class="mt-4 max-w-2xl text-base text-primary-100">
            Des prestations sur mesure pour armourer vos équipes et transformer durablement vos pratiques.
        </p>
        <a href="{{ route('contact', ['form' => 'devis']) }}" class="btn bg-white text-primary-700 hover:bg-primary-50 mt-8">
            Demander un devis
        </a>
    </div>
</section>

{{-- LISTE DES SERVICES --}}
<section class="bg-white py-16 sm:py-20">
    <div class="container-x">
        <div class="grid gap-6 md:grid-cols-2">
            @forelse ($site['services'] ?? [] as $service)
                <div class="card flex gap-5 transition hover:border-primary-300 hover:shadow-lg">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-600 text-white">
                        <x-icon :name="$service['icon'] ?? 'building'" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-lg leading-snug font-bold">{{ $service['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $service['description'] }}</p>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-slate-500">Les services seront bientôt disponibles.</p>
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

<section class="bg-slate-50 py-16 sm:py-20">
    <div class="container-x">
        <x-section-title eyebrow="Comment ça se passe" title="Un accompagnement en quatre étapes" />

        <ol class="mt-12 grid gap-6 md:grid-cols-4">
            @foreach ($process as $step)
                <li class="rounded-2xl bg-white p-6 shadow-soft">
                    <h3 class="text-base font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<x-cta-section />
@endsection

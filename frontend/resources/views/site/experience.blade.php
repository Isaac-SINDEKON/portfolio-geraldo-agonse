@extends('layouts.site', [
    'title' => 'Expérience et expertise – '.$site['settings']['name'],
    'description' => 'Parcours, expérience terrain et domaines d\'expertise du formateur.',
])

@section('content')
{{-- EN-TÊTE (CC §9) --}}
<section class="bg-slate-900 py-16 sm:py-20">
    <div class="container-x">
        <nav aria-label="Fil d'Ariane" class="text-xs text-primary-200">
            <a href="{{ route('home') }}" class="transition hover:text-primary-300">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Expérience &amp; expertise</span>
        </nav>
        <h1 class="mt-5 text-3xl font-extrabold text-white sm:text-4xl">Expérience &amp; expertise</h1>
        <p class="mt-4 max-w-2xl text-base text-primary-100">
            Une expérience opérationnelle, un réseau et des compétences éprouvées au service de votre performance.
        </p>
    </div>
</section>

{{-- PARCOURS --}}
<section class="bg-white py-16 sm:py-20">
    <div class="container-x">
        <x-section-title eyebrow="Mon parcours" title="Une expérience construite sur le terrain" />

        <ol class="mt-12 space-y-6">
            @forelse ($site['experiences'] ?? [] as $index => $experience)
                <li class="relative flex gap-6 pl-0 sm:pl-2">
                    {{-- Trait de la frise chronologique --}}
                    @if (! $loop->last)
                        <span class="absolute top-12 left-[1.35rem] hidden h-full w-0.5 bg-slate-200 sm:block" aria-hidden="true"></span>
                    @endif

                    <span class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                        <x-icon :name="$experience['icon'] ?? 'briefcase'" class="h-5 w-5" />
                    </span>

                    <div class="card flex-1">
                        <h2 class="text-lg font-bold">{{ $experience['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $experience['description'] }}</p>
                    </div>
                </li>
            @empty
                <p class="text-center text-slate-500">L'expérience détaillée sera bientôt disponible.</p>
            @endforelse
        </ol>
    </div>
</section>

{{-- COMPÉTENCES PAR DOMAINE --}}
@if (count($site['domains'] ?? []))
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="container-x">
            <x-section-title eyebrow="Expertise" title="Mes domaines de compétence" />

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($site['domains'] as $domain)
                    <a href="{{ route('formations', ['domaine' => $domain['title']]) }}"
                       class="card group text-center transition hover:-translate-y-1 hover:border-primary-300">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                            <x-icon :name="$domain['icon'] ?? 'target'" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-base font-bold">{{ $domain['title'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $domain['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<x-cta-section />
@endsection

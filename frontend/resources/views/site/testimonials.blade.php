@extends('layouts.site', [
    'title' => 'Témoignages – '.$site['settings']['name'],
    'description' => 'Retours de clients et partenaires ayant suivi une formation.',
])

@section('content')
{{-- EN-TÊTE (CC §10) --}}
<section class="bg-slate-900 py-16 sm:py-20">
    <div class="container-x">
        <nav aria-label="Fil d'Ariane" class="text-xs text-primary-200">
            <a href="{{ route('home') }}" class="transition hover:text-primary-300">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Témoignages</span>
        </nav>
        <h1 class="mt-5 text-3xl font-extrabold text-white sm:text-4xl">Témoignages</h1>
        <p class="mt-4 max-w-2xl text-base text-primary-100">
            Ils m'ont fait confiance pour former et accompagner leurs équipes. Voici leur retour.
        </p>
    </div>
</section>

{{-- TÉMOIGNAGES --}}
<section class="bg-slate-50 py-16 sm:py-20">
    <div class="container-x">
        @forelse ($site['testimonials'] ?? [] as $t)
            <figure class="card mb-6">
                <div class="flex flex-col gap-6 sm:flex-row">
                    <div class="flex items-center gap-4 sm:w-56 sm:shrink-0 sm:flex-col sm:items-start">
                        @if (! empty($t['photo_url']))
                            <img src="{{ $t['photo_url'] }}" alt="{{ $t['author'] }}" loading="lazy"
                                 class="h-16 w-16 rounded-full object-cover">
                        @else
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 text-xl font-bold text-primary-700">
                                {{ \Illuminate\Support\Str::limit($t['author'], 2, '') }}
                            </span>
                        @endif
                        <figcaption>
                            <span class="block text-sm font-bold text-slate-900">{{ $t['author'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $t['fonction'] }}</span>
                        </figcaption>
                    </div>

                    <div class="flex-1">
                        <x-icon name="quote" class="h-7 w-7 text-primary-200" />
                        <blockquote class="mt-3 text-sm leading-relaxed text-slate-700 sm:text-base">
                            « {{ $t['content'] }} »
                        </blockquote>
                    </div>
                </div>
            </figure>
        @empty
            <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                Les témoignages seront publiés prochainement.
            </p>
        @endforelse
    </div>
</section>

<x-cta-section />
@endsection

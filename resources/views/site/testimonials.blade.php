@extends('layouts.site', [
    'title' => 'Témoignages – '.$site['settings']['name'],
    'description' => 'Retours de clients et partenaires ayant suivi une formation.',
])

@section('content')
{{-- EN-TÊTE (CC §10) --}}
<section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-1 -top-28 left-[8%] h-80 w-80 bg-primary-600/30"></div>
    </div>

    <div class="container-x relative">
        <nav aria-label="Fil d'Ariane" class="text-xs text-on-nuit-doux">
            <a href="{{ route('home') }}" class="transition hover:text-on-nuit-vif">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Témoignages</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-12 lg:items-end lg:gap-10">
            <div class="lg:col-span-7">
                <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-[2.75rem]">Témoignages</h1>
                <p class="mt-4 max-w-2xl text-base text-on-nuit-doux">
                    Ils m'ont fait confiance pour former et accompagner leurs équipes. Voici leur retour.
                </p>
            </div>

            <div class="lg:col-span-5 lg:text-right">
                <x-magnetic-btn :href="route('contact', ['form' => 'formation'])" variante="light" icone="arrow-right">
                    Rejoindre mes clients
                </x-magnetic-btn>
            </div>
        </div>
    </div>
</section>

{{-- TÉMOIGNAGES : bento, la première carte occupe les deux tiers --}}
<section class="bg-canvas py-16 sm:py-20">
    <div class="container-x grid gap-6 lg:grid-cols-12">
        @php $formes = ['lg:col-span-7', 'lg:col-span-5', 'lg:col-span-6', 'lg:col-span-6', 'lg:col-span-6']; @endphp

        @forelse ($site['testimonials'] ?? [] as $index => $t)
            @php $forme = $formes[$index] ?? 'lg:col-span-6'; @endphp

            <figure @class(['card card-lift', $forme])
                    data-glow
                    data-reveal="up"
                    @if ($index) style="--reveal-delay: {{ $index * 90 }}ms" @endif>
                <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                <div class="relative flex flex-col gap-6 sm:flex-row">
                    <div class="flex items-center gap-4 sm:w-52 sm:shrink-0 sm:flex-col sm:items-start">
                        {{-- La photo transite par imageUrl() comme les autres images
                             du site : servie par /media, elle suit toujours la page. --}}
                        @php $photoTemoignage = $content->imageUrl($t['photo'] ?? $t['photo_url'] ?? null); @endphp
                        @if ($photoTemoignage)
                            <img src="{{ $photoTemoignage }}" alt="{{ $t['author'] }}" loading="lazy"
                                 class="h-16 w-16 rounded-full object-cover">
                        @else
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 text-xl font-bold text-primary-700">
                                {{ \Illuminate\Support\Str::limit($t['author'], 2, '') }}
                            </span>
                        @endif
                        <figcaption>
                            <span class="block text-sm font-bold text-ink">{{ $t['author'] }}</span>
                            <span class="block text-xs text-muted">{{ $t['fonction'] }}</span>
                        </figcaption>
                    </div>

                    <div class="flex-1">
                        <x-icon name="quote" class="h-7 w-7 text-on-nuit-doux" />
                        <blockquote class="mt-3 text-sm leading-relaxed text-copy sm:text-base">
                            « {{ $t['content'] }} »
                        </blockquote>
                    </div>
                </div>
            </figure>
        @empty
            <p class="col-span-full rounded-2xl border border-dashed border-line bg-surface p-10 text-center text-muted">
                Les témoignages seront publiés prochainement.
            </p>
        @endforelse
    </div>
</section>

<x-cta-section />
@endsection
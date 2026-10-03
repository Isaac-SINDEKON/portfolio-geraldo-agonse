@extends('layouts.site')

@section('title', $formation['title'] . ' – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', \Illuminate\Support\Str::limit($formation['description'] ?? '', 200))
@section('canonical', route('formations.show', $formation['slug']))
@section('og_type', 'article')

@section('content')

    <section class="relative overflow-hidden bg-nuit py-14 text-white sm:py-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="grille-technique absolute inset-0"></div>
            <div class="aurora aurora-3 -top-28 right-[10%] h-80 w-80 bg-primary-600/25"></div>
        </div>

        <div class="container-x relative">
            <nav class="text-sm text-primary-200" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}" class="hover:text-primary-300">Accueil</a>
                <span class="mx-2">/</span>
                <a href="{{ route('formations') }}" class="hover:text-primary-300">Formations</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $formation['title'] }}</span>
            </nav>

            <div class="mt-6 flex items-start gap-5">
                <span class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                    <x-icon :name="$formation['icon'] ?? 'target'" class="h-7 w-7" />
                </span>
                <div>
                    <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ $formation['title'] }}</h1>
                    @if (! empty($formation['subtitle']))
                        <p class="mt-2 text-lg text-primary-200">{{ $formation['subtitle'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="bg-surface py-14 sm:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-8">
                <h2 class="text-2xl font-bold">Description</h2>
                <p class="mt-4 leading-relaxed text-copy">{{ $formation['description'] }}</p>

                @if (! empty($formation['objectives']))
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold">Objectifs de la formation</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($formation['objectives'] as $objectif)
                                <li class="flex gap-3 text-ink">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                                        <x-icon name="check" class="h-4 w-4" />
                                    </span>
                                    <span class="text-sm leading-relaxed">{{ $objectif }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($formation['programme']))
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold">Programme détaillé</h2>
                        {{-- Chaque module est une carte : le programme se lit
                             comme une liste d'étapes, pas comme un bloc de texte. --}}
                        <ol class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ($formation['programme'] as $index => $module)
                                <li class="card flex gap-4 p-5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600 text-sm font-bold text-on-brand">
                                        {{ $index + 1 }}
                                    </span>
                                    <p class="text-sm leading-relaxed text-copy">{{ $module }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-4 lg:self-start lg:sticky lg:top-28">
                <div class="card p-6" data-glow>
                    <span class="halo-survol pointer-events-none absolute inset-0" aria-hidden="true"></span>

                    <div class="relative">
                        <h2 class="text-lg font-bold">Informations clés</h2>
                        <dl class="mt-5 space-y-4 text-sm">
                            @if (! empty($formation['duree']))
                                <div>
                                    <dt class="font-semibold text-muted">Durée</dt>
                                    <dd class="mt-0.5 font-medium text-ink">{{ $formation['duree'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($formation['public_cible']))
                                <div>
                                    <dt class="font-semibold text-muted">Public cible</dt>
                                    <dd class="mt-0.5 font-medium text-ink">{{ $formation['public_cible'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($formation['modalites']))
                                <div>
                                    <dt class="font-semibold text-muted">Modalités</dt>
                                    <dd class="mt-0.5 font-medium text-ink">{{ $formation['modalites'] }}</dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Les deux demandes sont pré-remplies avec la formation visitée (CC §14 et §15) --}}
                        <x-magnetic-btn :href="route('contact', ['form' => 'formation', 'theme' => $formation['title']]).'#formulaire-demande'"
                                        bloc class="mt-6">
                            Demander cette formation
                        </x-magnetic-btn>

                        <a href="{{ route('contact', ['form' => 'devis', 'theme' => $formation['title']]) }}#formulaire-demande"
                           class="btn-outline mt-3 w-full">
                            Demander un devis sur ce thème
                        </a>

                        <x-magnetic-btn :href="$content->whatsappUrl('Bonjour ' . ($site['settings']['name'] ?? '') . ', j\'aimerais en savoir plus sur la formation « ' . $formation['title'] . '».')"
                                        variante="whatsapp" icone="whatsapp" :externe="true" :force="0.2" bloc class="mt-3">
                            Poser une question sur WhatsApp
                        </x-magnetic-btn>

                        <a href="{{ route('formations') }}" class="mt-4 block text-center text-sm font-medium text-primary-600 hover:underline">
                            Voir les autres formations
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- Donnees structurees de la formation (CC §23) --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $formation['title'],
            'description' => $formation['description'] ?? null,
            'inLanguage' => 'fr',
            'provider' => [
                '@type' => 'Person',
                'name' => $site['settings']['name'] ?? 'Géraldo Perridys AGONSE',
                'jobTitle' => $site['settings']['role'] ?? 'Formateur',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    {{-- Partage : les programmes sont le contenu le plus partage (CC §30) --}}
    <section class="border-t border-line-soft bg-surface py-8">
        <div class="container-x">
            <x-partage
            variant="page"
            :titre="$formation['title'].' – '.($site['settings']['name'] ?? 'Geraldo Perridys AGONSE')"
            :description="\Illuminate\Support\Str::limit(strip_tags($formation['description'] ?? ''), 200)"
        />
        </div>
    </section>

    <x-cta-section />

@endsection

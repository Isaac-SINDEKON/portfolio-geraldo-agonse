@extends('layouts.site')

@section('title', $formation['title'] . ' – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', \Illuminate\Support\Str::limit($formation['description'] ?? '', 200))
@section('canonical', route('formations.show', $formation['slug']))
@section('og_type', 'article')

@section('content')

    <section class="bg-slate-900 py-14 sm:py-16">
        <div class="container-x">
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
                    <h1 class="text-3xl font-extrabold text-white sm:text-4xl">{{ $formation['title'] }}</h1>
                    @if (! empty($formation['subtitle']))
                        <p class="mt-2 text-lg text-primary-200">{{ $formation['subtitle'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold">Description</h2>
                <p class="mt-4 leading-relaxed text-slate-600">{{ $formation['description'] }}</p>

                @if (! empty($formation['objectives']))
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold">Objectifs de la formation</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($formation['objectives'] as $objectif)
                                <li class="flex gap-3 text-slate-700">
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
                        <ol class="mt-5 space-y-4">
                            @foreach ($formation['programme'] as $index => $module)
                                <li class="card flex gap-4 bg-slate-50 p-5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600 text-sm font-bold text-white">
                                        {{ $index + 1 }}
                                    </span>
                                    <p class="text-sm leading-relaxed text-slate-700">{{ $module }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-2xl border border-slate-100 bg-white p-6">
                    <h2 class="text-lg font-bold">Informations clés</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        @if (! empty($formation['duree']))
                            <div>
                                <dt class="font-semibold text-slate-500">Durée</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">{{ $formation['duree'] }}</dd>
                            </div>
                        @endif
                        @if (! empty($formation['public_cible']))
                            <div>
                                <dt class="font-semibold text-slate-500">Public cible</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">{{ $formation['public_cible'] }}</dd>
                            </div>
                        @endif
                        @if (! empty($formation['modalites']))
                            <div>
                                <dt class="font-semibold text-slate-500">Modalités</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">{{ $formation['modalites'] }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Les deux demandes sont pré-remplies avec la formation visitée (CC §14 et §15) --}}
                    <a href="{{ route('contact', ['form' => 'formation', 'theme' => $formation['title']]) }}#formulaire-demande"
                       class="btn-primary mt-6 w-full">
                        Demander cette formation
                    </a>
                    <a href="{{ route('contact', ['form' => 'devis', 'theme' => $formation['title']]) }}#formulaire-demande"
                       class="btn-outline mt-3 w-full">
                        Demander un devis sur ce thème
                    </a>
                    <a href="{{ $content->whatsappUrl('Bonjour ' . ($site['settings']['name'] ?? '') . ', j\'aimerais en savoir plus sur la formation « ' . $formation['title'] . ' ».') }}"
                       target="_blank" rel="noopener" class="btn-accent mt-3 w-full">
                        Poser une question sur WhatsApp
                    </a>
                    <a href="{{ route('formations') }}" class="mt-3 block text-center text-sm font-medium text-primary-600 hover:underline">
                        Voir les autres formations
                    </a>
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
    <section class="border-t border-slate-200 bg-white py-8">
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

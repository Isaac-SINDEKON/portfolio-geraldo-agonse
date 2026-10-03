@extends('layouts.site', [
    'title' => $site['settings']['seo_title'] ?? 'Géraldo Perridys AGONSE – Formateur professionnel',
    'description' => $site['settings']['seo_description'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
    $heroPhoto = $content->imageUrl($s['hero_photo'] ?? null);
@endphp

{{-- HERO
     Premier écran = promesse + preuve + action, sur deux colonnes de largeur
     inégale. Le H1 est en Playfair Display (cf. app.css), l'argument en Inter :
     la hiérarchie se lit avant même le texte. --}}
<section class="relative overflow-hidden bg-canvas">
    {{-- Décor : grille discrète + deux halos de marque qui dérivent. --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-1 -top-40 -right-32 h-[26rem] w-[26rem] bg-primary-500/30"></div>
        <div class="aurora aurora-2 -bottom-48 -left-24 h-[22rem] w-[22rem] bg-primary-400/20"></div>
    </div>

    <div class="container-x relative pt-14 pb-20 sm:pt-20 lg:pt-24 lg:pb-28">
        <div class="grid grid-cols-1 items-center gap-14 lg:grid-cols-12 lg:gap-10">

            {{-- Colonne gauche : promesse ------------------------------------}}
            <div class="lg:col-span-6" data-hero>
                <p class="inline-flex items-center gap-2.5 rounded-full bg-primary-50 px-4 py-1.5 text-xs font-bold text-primary-700 ring-1 ring-primary-100 ring-inset">
                    <span class="relative flex h-2 w-2">
                        <span class="pulse-doux absolute inline-flex h-full w-full rounded-full bg-primary-600"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                    </span>
                    {{ $s['role'] ?? 'Formateur Professionnel' }}
                </p>

                <h1 class="mt-6 text-4xl leading-[1.04] font-bold tracking-tight text-balance text-ink lg:text-6xl">
                    {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                </h1>

                @if (! empty($s['tagline']))
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-balance text-copy">
                        {{ $s['tagline'] }}
                    </p>
                @endif

                @if (! empty($s['hero_text']))
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-muted">
                        {{ $s['hero_text'] }}
                    </p>
                @endif

                {{-- Les deux actions principales sont magnétiques : le bouton suit
                     le pointeur et sa lueur se déplace avec lui. --}}
                <div class="mt-9 flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                    <x-magnetic-btn :href="route('contact', ['form' => 'formation'])" icone="arrow-right" class="px-6 py-3.5 text-base">
                        Demander une formation
                    </x-magnetic-btn>

                    <x-magnetic-btn :href="$content->whatsappUrl()" variante="whatsapp" icone="whatsapp" :externe="true" :force="0.2" class="px-6 py-3.5 text-base">
                        Me contacter sur WhatsApp
                    </x-magnetic-btn>
                </div>

                {{-- Réassurance : lève le doute avant le clic. Les libellés sont
                     volontairement qualitatifs : aucun chiffre inventé ne peut
                     discréditer le devis devant un prospect. --}}
                <ul class="mt-8 grid gap-x-6 gap-y-3 text-sm font-medium text-copy sm:grid-cols-2 lg:max-w-xl">
                    @foreach ([
                        ['graduation', 'Certifié formateur professionnel'],
                        ['map', 'Interventions au '.($s['location'] ?? 'Bénin et Togo')],
                        ['check', 'Programmes sur-mesure'],
                        ['calendar', 'Sur site, en intra ou à distance'],
                    ] as [$icone, $libelle])
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                                <x-icon :name="$icone" class="h-3.5 w-3.5" />
                            </span>
                            {{ $libelle }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-line-soft pt-6 text-sm text-muted">
                    <a href="{{ $content->telUrl() }}" class="flex items-center gap-2 font-semibold text-ink transition-colors hover:text-primary-600">
                        <x-icon name="phone" class="h-4 w-4 text-primary-600" />
                        {{ $s['phone_display'] ?? $s['phone'] ?? '' }}
                    </a>
                    <a href="mailto:{{ $s['email'] ?? '' }}" class="flex items-center gap-2 break-all transition-colors hover:text-primary-600">
                        <x-icon name="mail" class="h-4 w-4 text-muted" />
                        {{ $s['email'] ?? '' }}
                    </a>
                </div>
            </div>

            {{-- Colonne droite : preuve -------------------------------------}}
            <div class="lg:col-span-6" data-reveal="zoom">
                <div class="relative mx-auto w-full max-w-lg lg:max-w-none">

                    {{-- Cadre de la photo : `tilt` suit le pointeur sur les
                         écrans qui le permettent, `data-glow` pose la lueur. --}}
                    <div class="tilt relative" data-tilt data-tilt-max="5" data-glow>
                        <div class="halo-survol pointer-events-none absolute -inset-3 rounded-[2rem]" aria-hidden="true"></div>

                        <div class="absolute -top-5 -right-5 h-32 w-32 rounded-3xl bg-primary-400/25" aria-hidden="true"></div>
                        <div class="absolute -bottom-8 -left-8 h-48 w-48 rounded-[2.5rem] bg-primary-600/12" aria-hidden="true"></div>

                        <div class="relative aspect-4/5 overflow-hidden rounded-[2rem] bg-surface shadow-lift ring-1 ring-line"
                             x-data="{ failed: false }">
                            <img src="{{ $heroPhoto }}"
                                 alt="{{ $s['name'] ?? '' }}"
                                 class="h-full w-full object-cover"
                                 fetchpriority="high"
                                 x-show="! failed"
                                 x-on:error="failed = true">

                            {{-- Repli : initiales, si aucune photo n'est téléversée
                                 ou si le fichier est introuvable. --}}
                            <div class="flex h-full w-full flex-col items-center justify-center gap-4 bg-surface p-6 text-center"
                                 x-show="failed || ! @js((bool) $heroPhoto)">
                                <x-marque variante="portrait-clair" />
                                <p class="text-sm font-semibold text-ink">{{ $s['name'] ?? '' }}</p>
                                <p class="text-xs tracking-widest text-muted uppercase">{{ $s['role'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Carte de preuve haute : elle déborde du cadre et
                             remplit l'angle supérieur gauche de la colonne. --}}
                        <div class="animate-flottement absolute -top-4 -left-4 z-10 flex items-center gap-3 rounded-2xl border border-line-soft bg-card/95 px-4 py-3 shadow-lift backdrop-blur sm:-left-8">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-whatsapp/15 text-whatsapp">
                                <x-icon name="shield" class="h-5 w-5" />
                            </span>
                            <span class="leading-tight">
                                <span class="block text-xs text-muted">Interventions</span>
                                <span class="block text-sm font-bold text-ink">Bénin · Togo</span>
                            </span>
                        </div>

                        {{-- Carte de preuve basse : le chiffre le plus utile,
                             affiché avant même le premier échange. --}}
                        <div class="absolute -right-3 -bottom-6 z-10 rounded-2xl border border-line-soft bg-card/95 px-5 py-4 shadow-lift backdrop-blur sm:-right-8">
                            <p class="font-serif text-3xl font-bold tracking-tight text-primary-600">{{ $s['stat_3_value'] ?? '2 000+' }}</p>
                            <p class="mt-0.5 text-xs font-semibold tracking-wide text-muted uppercase">
                                {{ $s['stat_3_label'] ?? 'Professionnels formés' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- PRÉSENTATION RAPIDE (CC §7)
     Une seule section entre le hero et les domaines : qui je suis, ce que
     j'apporte, et la porte vers « À propos ». Pas de photo ici — le hero
     affiche déjà le portrait ; le répéter coûte un écran sans rien apprendre
     au visiteur. La section disparaît si l owner's approach est vide. --}}
@php
    $approche = $s['about_approche'] ?? $s['about_expertise'] ?? '';
    $qualifications = \App\Services\SiteContent::linesToArray($s['about_qualifications'] ?? '');
@endphp

@if (filled($approche) || count($qualifications))
    <section class="bg-surface py-20 sm:py-24">
        <div class="container-x">
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
                <div @class([
                    'lg:col-span-7' => count($qualifications),
                    'lg:col-span-12' => ! count($qualifications),
                ]) data-reveal>
                    <x-section-title
                        align="gauche"
                        eyebrow="Votre formateur"
                        title="Un accompagnement construit sur votre réalité"
                        text="{{ \Illuminate\Support\Str::limit($approche, 320) }}" />

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-magnetic-btn :href="route('about')" icone="arrow-right" class="px-6 py-3.5">
                            En savoir plus sur moi
                        </x-magnetic-btn>
                        <a href="{{ route('experience') }}" class="btn-outline px-6 py-3.5">Voir mon parcours</a>
                    </div>
                </div>

                @if (count($qualifications))
                    <div class="lg:col-span-5" data-reveal="right">
                        <div class="card">
                            <p class="text-sm font-bold uppercase tracking-wide text-muted">Mes qualifications</p>
                            <ul class="mt-5 grid gap-3">
                                @foreach ($qualifications as $qualification)
                                    <li class="flex items-start gap-3 text-sm font-medium text-ink">
                                        <x-icon name="graduation" class="mt-0.5 h-4 w-4 shrink-0 text-primary-600" />
                                        {{ $qualification }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

{{-- BENTO GRID DES DOMAINES
     Quatre cartes de tailles inégales qui occupent toute la largeur. C'est le
     composant `bento-services` : la grille y est explicite en col-span, donc
     elle est concu pour quatre elements et pas cinq. La liste est volontairement
     bornee ici (CC §7 « quatre domaines d'intervention ») plutot que dans le
     composant : le catalogue reste complet pour l'administration et pour la page
     Formations, seule la page d'accueil tient les quatre promise. --}}
<x-bento-services
    :items="array_slice($domains, 0, 4)"
    eyebrow="Domaines d’intervention"
    title="Quatre expertises pour transformer vos pratiques"
    text="Des programmes conçus pour répondre aux enjeux concrets de votre organisation."
    :lien="route('formations')" />

{{-- RAISONS DE SOLLICITER LE FORMATEUR
     Deux colonnes : les raisons à gauche, une carte « méthode » à droite qui
     occupe l'espace libre au lieu de laisser un trou. --}}
<section class="bg-canvas py-20 sm:py-24">
    <div class="container-x">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7">
                <x-section-title
                    align="gauche"
                    eyebrow="Pourquoi me choisir"
                    title="Un formateur opérationnel, pas seulement théorique"
                    text="Une approche concrète, adaptée à votre réalité, avec des résultats visibles." />

                <div class="mt-12 grid gap-5 sm:grid-cols-2" data-reveal-group data-reveal-step="110">
                    @foreach ($reasons as $reason)
                        <div class="card card-lift flex gap-4 p-5">
                            <span class="card-icon-solid">
                                <x-icon :name="$reason['icon'] ?? 'sparkles'" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-ink">{{ $reason['title'] ?? '' }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-copy">{{ $reason['description'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Colonne de droite : la « méthode », qui donne un nom aux
                 pratiques réutilisées dans chaque programme. --}}
            <div class="lg:col-span-5" data-reveal="right">
                <div class="relative h-full overflow-hidden rounded-3xl bg-nuit p-8 text-white shadow-lift">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grille-technique absolute inset-0"></div>
                        <div class="aurora aurora-3 -top-16 -right-10 h-56 w-56 bg-primary-600/30"></div>
                    </div>

                    <div class="relative">
                        <p class="eyebrow text-primary-300">La méthode</p>
                        <h3 class="mt-3 font-serif text-2xl leading-tight font-bold text-white">
                            80 % de terrain, 20 % de théorie
                        </h3>

                        <ul class="mt-8 space-y-5">
                            @foreach ([
                                ['users', 'Mises en situation', 'On travaille sur vos propres cas, pas sur des exemples génériques.'],
                                ['message', 'Questions en direct', 'Le temps des questions fait partie du programme, pas du rab.'],
                                ['chart', 'Mesure du transfert', 'On vérifie à 30 jours ce qui a réellement changé dans les équipes.'],
                            ] as [$icone, $titre, $texte])
                                <li class="flex gap-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-primary-300">
                                        <x-icon :name="$icone" class="h-5 w-5" />
                                    </span>
                                    <span>
                                        <span class="block font-bold text-white">{{ $titre }}</span>
                                        <span class="mt-1 block text-sm leading-relaxed text-slate-400">{{ $texte }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <x-magnetic-btn :href="route('about')" variante="light" icone="arrow-right" class="mt-9">
                            Découvrir mon approche
                        </x-magnetic-btn>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- APPEL À L'ACTION FINAL (CC §7) --}}
<x-cta-section />
@endsection

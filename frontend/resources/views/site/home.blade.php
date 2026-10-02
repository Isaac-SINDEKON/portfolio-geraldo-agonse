@extends('layouts.site', [
    'title' => $site['settings']['seo_title'] ?? 'Géraldo Perridys AGONSE – Formateur professionnel',
    'description' => $site['settings']['seo_description'] ?? null,
])

@section('content')
@php
    $s = $site['settings'] ?? [];
@endphp

{{-- HERO (CC §7)

     Premier écran = promesse + preuve + action. Le bandeau de réassurance
     sous les boutons répond aux trois questions qui bloquent la décision :
     "est-il qualifié ?", "a-t-il déjà travaillé là où je suis ?",
     "combien ça me coûte de le contacter ?". --}}
<section class="relative overflow-hidden bg-white">
    {{-- Trame de fond : grille discrète + halos de marque. Donne de la
         profondeur et une lecture "futuriste" sans alourdir la page. --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,color-mix(in_oklab,var(--brand)_7%,transparent)_1px,transparent_1px),linear-gradient(to_bottom,color-mix(in_oklab,var(--brand)_7%,transparent)_1px,transparent_1px)] bg-[size:56px_56px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_72%)]"></div>
        <div class="absolute -top-40 -right-32 h-[26rem] w-[26rem] rounded-full bg-primary-100/60 blur-3xl"></div>
        <div class="absolute -bottom-48 -left-24 h-[22rem] w-[22rem] rounded-full bg-accent-100/50 blur-3xl"></div>
    </div>

    <div class="container-x relative py-20 lg:py-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
            <div class="animate-rise">
                <p class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-100 ring-inset">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-600 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                    </span>
                    {{ $s['role'] ?? 'Formateur Professionnel' }}
                </p>

                <h1 class="mt-6 text-4xl leading-[1.1] font-extrabold tracking-tight text-slate-900 lg:text-6xl">
                    {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                </h1>

                @if (! empty($s['tagline']))
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                        {{ $s['tagline'] }}
                    </p>
                @endif

                @if (! empty($s['hero_text']))
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-500">
                        {{ $s['hero_text'] }}
                    </p>
                @endif

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('contact', ['form' => 'formation']) }}" class="btn-primary">
                        Demander une formation
                    </a>
                    <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                       class="btn-whatsapp">
                        <x-icon name="whatsapp" class="h-4 w-4" />
                        Me contacter sur WhatsApp
                    </a>
                </div>

                {{-- Réassurance : lève le doute avant le clic. Les libellés sont
                     volontairement qualitatifs : aucun chiffre inventé ne peut
                     discréditer le devis devant un prospect. --}}
                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-slate-600">
                    @foreach ([
                        ['graduation', 'Certifié formateur professionnel'],
                        ['map', 'Interventions au '.($s['location'] ?? 'Bénin et Togo')],
                        ['check', 'Programmes sur-mesure'],
                    ] as [$icone, $libelle])
                        <li class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                                <x-icon :name="$icone" class="h-3.5 w-3.5" />
                            </span>
                            {{ $libelle }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-slate-100 pt-6 text-sm text-slate-500">
                    <a href="{{ $content->telUrl() }}" class="flex items-center gap-2 font-semibold text-slate-700 transition-colors hover:text-primary-600">
                        <x-icon name="phone" class="h-4 w-4 text-primary-600" />
                        {{ $s['phone_display'] ?? $s['phone'] ?? '' }}
                    </a>
                    <a href="mailto:{{ $s['email'] ?? '' }}" class="flex items-center gap-2 break-all transition-colors hover:text-primary-600">
                        <x-icon name="mail" class="h-4 w-4 text-slate-400" />
                        {{ $s['email'] ?? '' }}
                    </a>
                </div>
            </div>

            <div class="animate-rise mx-auto w-full max-w-md lg:max-w-none">
                @php $heroPhoto = $content->imageUrl($s['hero_photo'] ?? null); @endphp

                <div class="relative">
                    <div class="absolute -top-5 -right-5 h-28 w-28 rounded-2xl bg-accent-400/40 lg:h-32 lg:w-32"
                         aria-hidden="true"></div>
                    <div class="absolute -bottom-8 -left-8 h-44 w-44 rounded-3xl bg-primary-600/10 lg:h-56 lg:w-56"
                         aria-hidden="true"></div>

                    <div class="relative aspect-4/5 overflow-hidden rounded-2xl bg-slate-100 shadow-glow"
                         x-data="{ failed: false }">
                        <img src="{{ $heroPhoto }}"
                             alt="{{ $s['name'] ?? '' }}"
                             class="h-full w-full object-cover"
                             fetchpriority="high"
                             x-show="! failed"
                             x-on:error="failed = true">

                        {{-- Repli : initiales, si aucune photo n'est téléversée ou si le fichier est introuvable --}}
                        <div class="flex h-full w-full flex-col items-center justify-center gap-4 bg-slate-50 p-6 text-center"
                             x-show="failed || ! @js((bool) $heroPhoto)">
                            <x-marque variante="portrait-clair" />
                            <p class="text-sm font-semibold text-slate-900">{{ $s['name'] ?? '' }}</p>
                            <p class="text-xs tracking-widest text-slate-500 uppercase">{{ $s['role'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BANDEAU DE CONFIANCE : les organisations clientes.

     Preuve sociale institutionnelle, distincte des témoignages nominatifs.
     Masqué tant qu'aucun nom n'est saisi : une liste vide se lirait comme un
     manque, alors que le silence passe inaperçu. --}}
@php
    $clients = \App\Services\SiteContent::linesToArray($s['clients'] ?? '');
@endphp
@if (count($clients))
    <section class="border-y border-slate-100 bg-slate-50/60 py-12">
        <div class="container-x">
            <p class="text-center text-xs font-bold tracking-[0.18em] text-slate-400 uppercase">
                Ils me font confiance
            </p>
            <ul class="stagger mt-7 flex flex-wrap items-center justify-center gap-x-10 gap-y-5 sm:gap-x-14">
                @foreach ($clients as $client)
                    <li class="text-lg font-bold tracking-tight text-slate-400 transition-colors hover:text-slate-600">
                        {{ $client }}
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

{{-- BANDEAU DE STATISTIQUES

     Les valeurs par defaut sont appliquees avec ?: et non ?? : le premier
     replie aussi sur une chaine vide. Un champ laisse vide par le
     proprietaire conserve donc la valeur du site au lieu de faire
     disparaitre le libelle ou le chiffre. --}}
@php
    $statsBandeau = [
        [$s['stat_1_label'] ?? '', 'Années d\'expérience', $s['stat_1_value'] ?? '', '10+'],
        [$s['stat_2_label'] ?? '', 'Formations animées', $s['stat_2_value'] ?? '', '120+'],
        [$s['stat_3_label'] ?? '', 'Professionnels formés', $s['stat_3_value'] ?? '', '2 000+'],
        [$s['stat_4_label'] ?? '', 'Organisations accompagnées', $s['stat_4_value'] ?? '', '50+'],
    ];
@endphp
<section class="bg-white pb-16 sm:pb-20">
    <div class="container-x">
        <div class="rounded-2xl bg-slate-900 px-6 py-10 text-white sm:px-10">
            <dl class="grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
                @foreach ($statsBandeau as [$libelle, $libelleDefaut, $valeur, $valeurDefaut])
                    <div>
                        <dt class="stat-label">{{ $libelle ?: $libelleDefaut }}</dt>
                        <dd class="stat-nombre mt-2">{{ $valeur ?: $valeurDefaut }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</section>

{{-- PREUVE VISUELLE

     Le section la plus importante pour un formateur : des photos réelles
     d'intervention. Elle est volontairement sombre pour casser la succession
     de sections blanches, et donne enfin une preuve concrète au lieu d'une
     nouvelle grille de cartes. --}}
@if (count($galerie))
    <section class="relative overflow-hidden bg-slate-900 py-20 sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-32 left-1/3 h-96 w-96 rounded-full bg-primary-600/20 blur-3xl"></div>
            <div class="absolute -bottom-32 right-10 h-80 w-80 rounded-full bg-accent-500/10 blur-3xl"></div>
        </div>

        <div class="container-x relative">
            <div class="max-w-2xl">
                <x-section-title
                    eyebrow="En intervention"
                    title="Des formations qui se vivent sur le terrain"
                    text="Ateliers, séminaires et accompanying d’équipes : voici où les programmes prennent vie."
                    variante="sombre" />

                <a href="{{ route('gallery') }}" class="btn-light mt-8">
                    <x-icon name="gallery" class="h-4 w-4" />
                    Voir toute la galerie
                </a>
            </div>

{{-- La première photo occupe la moitié gauche : c'est elle qui donne
                     l'échelle et le ton. Les autres suivent en vignettes. --}}
            <div class="stagger mt-12 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($galerie as $index => $photo)
                    @php
                        $url = $content->imageUrl($photo['image_path'] ?? $photo['image_url'] ?? null);
                        $grande = $index === 0;
                    @endphp
                    <figure @class([
                        'group relative overflow-hidden rounded-2xl bg-slate-800 ring-1 ring-white/10',
                        'col-span-2 row-span-2 aspect-square' => $grande,
                        'aspect-square' => ! $grande,
                    ])>
                        @if ($url)
                            <img src="{{ $url }}"
                                 alt="{{ $photo['caption'] ?? 'Intervention en formation' }}"
                                 loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <span class="flex h-full w-full items-center justify-center text-slate-600">
                                <x-icon name="gallery" class="h-10 w-10" />
                            </span>
                        @endif

                        @if (! empty($photo['caption']))
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/90 to-transparent p-4 pt-10 text-sm font-medium text-white">
                                {{ $photo['caption'] }}
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- PARCOURS CLIENT

     Répond à la question que personne ne pose à voix haute mais que tout
     decisionner se demande : "que se passe-t-il après mon clic ?". Sans
     chronologie visible, un appel à l'action ressemble à une promesse en
     suspens. --}}
@php
    $etapes = [
        ['search', 'Diagnostic', 'Échange de 30 minutes sur votre contexte, vos équipes et vos objectifs. Vous repartez avec un premier avis honnête, gratuit.'],
        ['pencil', 'Programme sur-mesure', 'Un contenu construit pour vos enjeux réels, avec vos exemples et vos contraintes — jamais un jeu de diapositives générique.'],
        ['users', 'Animation', 'Atelier, séminaire ou coaching : des mises en situation, des cas concrets, et le temps nécessaire pour les questions.'],
        ['chart', 'Suivi post-formation', 'Un point de suivi à 30 jours. On mesure ce qui a changé et on ajuste ce qui doit l’être.'],
    ];
@endphp
<section class="bg-white py-20 sm:py-24">
    <div class="container-x">
        <x-section-title
            eyebrow="Comment ça se passe"
            title="De votre premier appel à l’impact mesuré"
            text="Quatre étapes balisées, un interlocuteur unique, aucune mauvaise surprise en cours de route." />

        <ol class="relative mt-14 grid gap-8 lg:grid-cols-4 lg:gap-6">
            {{-- Ligne de liaison desktop : purely décorative. --}}
            <div class="pointer-events-none absolute top-6 right-[12%] left-[12%] hidden h-px bg-gradient-to-r from-primary-200 via-primary-300 to-primary-200 lg:block"
                 aria-hidden="true"></div>

            @foreach ($etapes as $index => [$icone, $titre, $texte])
                <li class="relative">
                    <div class="flex items-center gap-4 lg:flex-col lg:items-start">
                        <span class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white shadow-glow">
                            <x-icon :name="$icone" class="h-5 w-5" />
                        </span>
                        <span class="text-xs font-bold tracking-[0.18em] text-slate-300 uppercase lg:mt-6">
                            Étape {{ $index + 1 }}
                        </span>
                    </div>
                    <h3 class="mt-4 text-lg font-bold lg:mt-2">{{ $titre }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $texte }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- DOMAINES D'INTERVENTION (CC §7) --}}
<section class="bg-white py-16 sm:py-20">
    <div class="container-x">
        <x-section-title
            eyebrow="Domaines d’intervention"
            title="Quatre expertises pour transformer vos pratiques"
            text="Des programmes conçus pour répondre aux enjeux concrets de votre organisation." />

        <div class="stagger mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            @forelse ($domains as $domain)
                <a href="{{ route('formations', ['domaine' => $domain['title']]) }}"
                   class="card card-lift group flex flex-col">
                    <span class="card-icon group-hover:bg-primary-600 group-hover:text-white">
                        <x-icon :name="$domain['icon'] ?? 'target'" />
                    </span>
                    <h3 class="mt-5 text-lg leading-snug font-bold">{{ $domain['title'] }}</h3>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $domain['description'] }}</p>
                    <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                        Voir la formation
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </span>
                </a>
            @empty
                <p class="col-span-full text-center text-slate-500">Domaines à venir.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- RAISONS DE SOLLICITER LE FORMATEUR (CC §7) --}}
<section class="bg-slate-50 py-16 sm:py-20">
    <div class="container-x">
        <x-section-title
            eyebrow="Pourquoi me choisir"
            title="Un formateur opérationnel, pas seulement théorique"
            text="Une approche concrète, adaptée à votre réalité, avec des résultats visibles." />

        <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2">
            @foreach ($reasons as $reason)
                <div class="card flex gap-5">
                    <span class="card-icon-solid">
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

{{-- APERÇU DES FORMATIONS --}}
@if (count($formations))
    <section class="bg-white py-16 sm:py-20">
        <div class="container-x">
            <x-section-title
                eyebrow="Catalogue de formations"
                title="Les formations les plus demandées"
                text="Quatre programmes clés pour renforcer la performance de vos équipes." />

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($formations as $formation)
                    <a href="{{ route('formations.show', $formation['slug']) }}"
                       class="card card-lift group flex flex-col">
                        <div class="flex items-start justify-between gap-4">
                            <span class="card-icon group-hover:bg-primary-600 group-hover:text-white">
                                <x-icon :name="$formation['icon'] ?? 'target'" />
                            </span>
                            @if (! empty($formation['duree']))
                                <span class="badge-saffron">{{ $formation['duree'] }}</span>
                            @endif
                        </div>
                        <h3 class="mt-5 text-lg leading-snug font-bold">{{ $formation['title'] }}</h3>
                        @if (! empty($formation['subtitle']))
                            <p class="mt-2 text-sm font-medium text-primary-600">{{ $formation['subtitle'] }}</p>
                        @endif
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $formation['description'] }}</p>
                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                            Détail de la formation
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('formations') }}" class="btn-outline">Voir toutes les formations</a>
            </div>
        </div>
    </section>
@endif

{{-- TÉMOIGNAGES --}}
@if (count($testimonials))
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="container-x">
            <x-section-title eyebrow="Ils me font confiance" title="Témoignages de clients satisfaits" />

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="card flex h-full flex-col">
                        <x-icon name="quote" class="h-8 w-8 shrink-0 text-primary-200" />

                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                            « {{ $t['content'] }} »
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                            {{-- La photo transite par imageUrl() comme les autres
                                 images du site : servie par le frontend, elle ne
                                 depend pas du backend pour s'afficher. --}}
                            @php $photoTemoignage = $content->imageUrl($t['photo'] ?? $t['photo_url'] ?? null); @endphp
                            @if ($photoTemoignage)
                                <img src="{{ $photoTemoignage }}" alt="{{ $t['author'] }}" loading="lazy"
                                     class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 text-sm font-bold text-primary-700">
                                    {{ \Illuminate\Support\Str::limit($t['author'], 2, '') }}
                                </span>
                            @endif
                            <span>
                                <span class="block text-sm font-bold text-slate-900">{{ $t['author'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $t['fonction'] }}</span>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('testimonials') }}" class="btn-outline">Lire tous les témoignages</a>
            </div>
        </div>
    </section>
@endif

{{-- QUI SUIS-JE (condensé)

     Placé juste avant la clôture : après les preuves, c'est l'humain qui
     convainc. Reprend les contenus « à propos » déjà saisis dans l'admin,
     sans dupliquer la page complète — le lien renvoie au détail. --}}
<section class="bg-white py-20 sm:py-24">
    <div class="container-x">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
            @php $aproposPhoto = $content->imageUrl($s['about_photo'] ?? $s['hero_photo'] ?? null); @endphp

            @if ($aproposPhoto)
                <div class="order-last lg:order-first">
                    <div class="overflow-hidden rounded-2xl bg-slate-100 shadow-xl">
                        <img src="{{ $aproposPhoto }}"
                             alt="{{ $s['name'] ?? '' }}"
                             loading="lazy"
                             class="aspect-4/5 w-full object-cover">
                    </div>
                </div>
            @endif

            <div>
                <x-section-title
                    align="gauche"
                    eyebrow="Votre formateur"
                    title="{{ $s['name'] ?? '' }}"
                    text="{{ \Illuminate\Support\Str::limit($s['about_approche'] ?? $s['about_expertise'] ?? '', 240) }}" />

                @php $qualifications = \App\Services\SiteContent::linesToArray($s['about_qualifications'] ?? ''); @endphp
                @if (count($qualifications))
                    {{-- Les diplômes sont la preuve la plus dure à contester
                         face à un décideur : ils méritent d'être lus avant
                         d'arriver sur le CTA. --}}
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        @foreach ($qualifications as $qualification)
                            <li class="flex items-start gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                                <x-icon name="graduation" class="mt-0.5 h-4 w-4 shrink-0 text-primary-600" />
                                {{ $qualification }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (count($experiences))
                    <ul class="stagger mt-8 space-y-4">
                        @foreach (array_slice($experiences, 0, 3) as $experience)
                            <li class="flex items-start gap-4">
                                <span class="card-icon shrink-0">
                                    <x-icon :name="$experience['icon'] ?? 'briefcase'" />
                                </span>
                                <span>
                                    <span class="block font-bold text-slate-900">{{ $experience['title'] }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-600">
                                        {{ \Illuminate\Support\Str::limit($experience['description'], 130) }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('about') }}" class="btn-primary">En savoir plus sur moi</a>
                    <a href="{{ route('experience') }}" class="btn-outline">Voir mon parcours</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- APPEL À L'ACTION FINAL (CC §7) --}}
<x-cta-section />
@endsection

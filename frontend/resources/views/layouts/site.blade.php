<!DOCTYPE html>
{{-- Les deux couleurs venues de l'administration sont recopiées sur <html>,
     validées ici avant d'atteindre le CSS (cf. BrandColorTest).

     Elles ne pilotent plus la palette publique : la maquette « Bento Grid »
     fixe son identité visuelle dans app.css via `--accent-base` (indigo en
     clair, or en sombre). Les deux variables restent émises pour ne pas casser
     le contrat historique du réglage, et restent exploitables par une
     éventuelle retouche de marque. --}}
@php
    $couleurs = [];

    foreach ([
        '--brand' => $site['settings']['brand_color'] ?? null,
        '--brand-accent' => $site['settings']['brand_accent'] ?? null,
    ] as $variable => $valeur) {
        $valeur = is_string($valeur) ? trim($valeur) : '';

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $valeur) === 1) {
            $couleurs[$variable] = $valeur;
        }
    }

    $styleMarque = collect($couleurs)->map(fn ($v, $k) => $k.':'.$v)->implode('; ');
@endphp
<html lang="fr" @if ($styleMarque) style="{{ $styleMarque }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $settings = $site['settings'] ?? [];
        $nom = $settings['name'] ?? 'Géraldo Perridys AGONSE';
        $seoTitle = $settings['seo_title'] ?? null;
        $seoDescription = $settings['seo_description'] ?? null;
        $seoKeywords = $settings['seo_keywords'] ?? null;
        // Titre, description et image fournis par la page via @extends.
        // Prioritaires sur les valeurs globales : chaque page a ainsi son
        // propre titre SEO et sa propre description (CC §23).
        $titlePage = $title ?? null;
        $descriptionPage = $description ?? null;
        $ogImage = $content->imageUrl($image ?? $settings['hero_photo'] ?? null);

        // Titre et description reellement affiches, repris tels quels par les
        // liens de partage visibles : l'apercu partage est identique a l'apercu
        // Open Graph. Une page peut fournir ces valeurs par @extends (variable
        // $title) ou par @section('title') : les deux sources sont lues, sans
        // consommer la section utilisee plus bas par les balises og/twitter.
        $lire = function (?string $section, string $defaut) use ($__env) {
            $valeur = $__env->hasSection($section) ? (string) $__env->getSection($section) : $defaut;

            return trim((string) preg_replace('/\s+/', ' ', strip_tags($valeur)));
        };

        $shareTitle = \Illuminate\Support\Str::limit($lire('title', (string) ($titlePage ?: ($seoTitle ?: $nom))), 120);
        $shareDescription = \Illuminate\Support\Str::limit($lire('meta_description', (string) ($descriptionPage ?? ($seoDescription ?? ($settings['tagline'] ?? '')))), 200);
    @endphp

    <title>@yield('title', $titlePage ?: ($seoTitle ?: $nom.' – '.($settings['role'] ?? '')))</title>

    <meta name="description" content="@yield('meta_description', $descriptionPage ?? ($seoDescription ?? $settings['tagline'] ?? ''))">
    @if ($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta name="author" content="{{ $nom }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Partage sur WhatsApp, Facebook, LinkedIn (CC §30) --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $nom }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="@yield('title', $titlePage ?: ($seoTitle ?: $nom))">
    <meta property="og:description" content="@yield('meta_description', $descriptionPage ?? ($seoDescription ?? ''))">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $titlePage ?: ($seoTitle ?: $nom))">
    <meta name="twitter:description" content="@yield('meta_description', $descriptionPage ?? ($seoDescription ?? ''))">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

{{-- Inter pour le corps de texte, Playfair Display pour les H1 et H2 :
         deux polices, deux registres. Les deux sont Chargées en une seule
         requête et avec display=swap, donc aucun texte n'est invisible. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">

    {{-- Thème clair / sombre et moteur d'animation : deux décisions prises
         AVANT le premier rendu, sinon la page clignoterait. Ce script est donc
         volontairement en ligne et minimal — il ne depend ni de Vite ni de
         Livewire, qui arrivent plus tard dans le document.

         1. La classe `js` autorise app.css a masquer les blocs `data-reveal`.
            Sans elle, aucun bloc ne reste invisible si le script echoue.
         2. Le sombre est le theme PRINCIPAL : il s'applique donc par defaut.
            Un choix explicite du visiteur est memorise et prime ensuite sur la
            preference systeme. --}}
    <script>
        (function () {
            var racine = document.documentElement

            racine.classList.add('js')

            var memoire = null

            try {
                memoire = localStorage.getItem('theme-portfolio')
            } catch (erreur) {
                // Navigation privee : le defaut du site s'applique.
            }

            var sombre = memoire === 'sombre'
                || (memoire !== 'clair' && ! window.matchMedia('(prefers-color-scheme: light)').matches)

            racine.classList.toggle('dark', sombre)
            racine.dataset.theme = memoire === 'clair' || memoire === 'sombre'
                ? memoire
                : (sombre ? 'sombre' : 'clair')
            racine.style.colorScheme = sombre ? 'dark' : 'light'

            // Repli de securite : si le moteur d'animation ne demarre pas,
            // les blocs `data-reveal` sont affiches immediatement plutot que
            // de laisser une page a moitie invisible.
            setTimeout(function () {
                if (! window.reinitialiserAnimations) {
                    document.querySelectorAll('[data-reveal]').forEach(function (bloc) {
                        bloc.classList.add('est-visible')
                    })
                }
            }, 4000)
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        // Donnees structurees Google (CC §23). Construit dans une variable
        // : Blade compile mal un commentaire place juste avant un echo.
        $donneesStructurees = [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter([
                [
                    '@type' => 'Person',
                    '@id' => url('/').'#personne',
                    'name' => $nom,
                    'jobTitle' => $settings['role'] ?? 'Formateur professionnel',
                    'description' => $settings['hero_text'] ?? ($settings['tagline'] ?? null),
                    'image' => $ogImage,
                    'url' => url('/'),
                    'telephone' => $settings['phone'] ?? null,
                    'email' => $settings['email'] ?? null,
                    'address' => ($settings['location'] ?? null) ? [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $settings['location'],
                    ] : null,
                    'knowsAbout' => array_map(fn ($f) => $f['title'] ?? null, $site['formations'] ?? []),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#site',
                    'url' => url('/'),
                    'name' => $nom,
                    'inLanguage' => 'fr',
                    'publisher' => ['@id' => url('/').'#personne'],
                ],
            ])),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($donneesStructurees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    @stack('structured-data')
</head>
<body class="min-h-screen bg-canvas">
    {{-- Barre de progression de lecture : fil de 3 px colle sous l'en-tete.
         Le deplacement est applique en CSS a partir de la variable --scroll
         (cf. app.js), donc aucune mesure de largeur n'est ecrite dans le DOM. --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-50 h-0.5 bg-transparent" aria-hidden="true">
        <div data-barre-progression
             class="barre-progression h-full w-full bg-gradient-to-r from-primary-500 via-accent-400 to-primary-600 shadow-[0_0_14px_var(--halo)]"></div>
    </div>

    <a href="#contenu"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Aller au contenu principal
    </a>

    <x-site-header />
    <x-site-message />

    <main id="contenu">
        @yield('content')
    </main>

    <x-site-footer :partage-titre="$shareTitle" :partage-description="$shareDescription" />
    <x-whatsapp-float />

    {{-- Confirmation visuelle apres un clic sur un lien email : sans client
         de messagerie configure, le mailto: ne declenche rien et le visiteur
         croit que le lien est casse. L'adresse est donc aussi copiee. --}}
    <div id="toast-email"
         class="pointer-events-none fixed bottom-24 left-1/2 z-[60] -translate-x-1/2 translate-y-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white opacity-0 shadow-lg transition duration-300"
         role="status" aria-live="polite"></div>

    @livewireScripts

    <script>
        (function () {
            var toast = document.getElementById('toast-email');
            var minuteur = null;

            function montrer(message) {
                if (!toast) {
                    return;
                }

                toast.textContent = message;
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, 0)';

                clearTimeout(minuteur);
                minuteur = setTimeout(function () {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translate(-50%, 0.5rem)';
                }, 3000);
            }

            document.addEventListener('click', function (evenement) {
                var lien = evenement.target.closest('a[href^="mailto:"]');

                if (!lien) {
                    return;
                }

                // On ne bloque jamais le mailto: le client de messagerie
                // doit pouvoir s'ouvrir si l'utilisateur en a un.
                var adresse = lien.getAttribute('href')
                    .replace(/^mailto:/i, '')
                    .split('?')[0]
                    .trim();

                if (!adresse) {
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(adresse).then(function () {
                        montrer('Adresse copiée : ' + adresse);
                    }, function () {
                        montrer(adresse);
                    });
                } else {
                    montrer(adresse);
                }
            });
        })();
    </script>
</body>
</html>

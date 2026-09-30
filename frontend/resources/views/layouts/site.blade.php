<!DOCTYPE html>
<html lang="fr">
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

    <title>@yield('title', $titlePage ?: ($seoTitle ?: $nom.' – '.$settings['role']))</title>

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
<body class="min-h-screen bg-white">
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

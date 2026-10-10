<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Administration') – {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Thème posé avant la première peinture : sans ce script en ligne, le
         navigateur afficherait le thème clair puis basculerait en sombre dès
         l'arrivée de Vite. Même préférence mémorisée que sur le site public,
         donc un basculement sur l'un reste visible sur l'autre. --}}
    <script>
        (function () {
            var racine = document.documentElement
            racine.classList.add('js')

            var memoire = null

            try {
                memoire = localStorage.getItem('theme-portfolio')
            } catch (erreur) {
                // Navigation privée : on retombe sur la préférence du système.
            }

            var sombre = memoire === 'sombre'
                || (memoire !== 'clair' && ! window.matchMedia('(prefers-color-scheme: light)').matches)

            racine.classList.toggle('dark', sombre)
            racine.dataset.theme = sombre ? 'sombre' : 'clair'
            racine.style.colorScheme = sombre ? 'dark' : 'light'
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-canvas text-ink">
<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- Barre latérale sur grand écran, en-tête + menu trois traits sur téléphone --}}
    <x-admin-nav />

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Barre de contexte : titre de la page, retour, messages.
             Regroupés dans un seul bandeau plutôt que dispersés dans la page. --}}
        <header class="border-b border-line bg-surface">
            <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button"
                                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = @js(route('admin.dashboard')); }"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-canvas hover:text-ink"
                                aria-label="Revenir à la page précédente"
                                title="Revenir à la page précédente">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                        </button>

                        <h1 class="min-w-0 truncate text-lg font-bold text-ink sm:text-xl">
                            @yield('title', 'Administration')
                        </h1>
                    </div>

                    <div class="flex items-center gap-3" x-data>
                        {{-- Bascule clair / sombre : même composant que le site
                             public, donc le même goût et la même persistance. --}}
                        <div x-data="basculeTheme" x-cloak>
                            <button type="button"
                                    x-on:click="basculer()"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-canvas hover:text-ink"
                                    :aria-label="sombre ? 'Activer le thème clair' : 'Activer le thème sombre'"
                                    :title="sombre ? 'Activer le thème clair' : 'Activer le thème sombre'">
                                <x-icon name="sun" x-show="sombre" class="h-4 w-4" />
                                <x-icon name="moon" x-show="! sombre" class="h-4 w-4" />
                            </button>
                        </div>

                        {{-- Zone d'actions de la page. Le x-data vide est
                             indispensable : les boutons « Ajouter » vivent ici,
                             dans l'en-tête, donc hors du conteneur Alpine du
                             contenu (@section('content')). Alpine n'initialisant
                             que l'intérieur d'un x-data, leur x-on:click restait
                             inerte et le formulaire ne s'ouvrait jamais.
                             L'evenement window relie le bouton a son contenu. --}}
                        @yield('actions')
                    </div>
                </div>

                {{-- Resume des erreurs de validation. Regroupes ici plutot que
                     repetees sous chaque champ : les formulaires d'edition sont
                     empiles en ligne dans une liste, donc une meme erreur se
                     afficherait une fois par ligne. Le resume la dit une fois,
                     et il se place avant le repli des pages invalides. --}}
                @if ($errors->any())
                    <div class="alerte alerte-erreur mt-4" role="alert">
                        <div class="flex items-start gap-3">
                            <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-alert-fort" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">
                                    {{ trans_choice(
                                        'Une donnée à corriger.|:count données à corriger.',
                                        $errors->count(),
                                        ['count' => $errors->count()]
                                    ) }}
                                </p>
                                <ul class="mt-2 space-y-1 text-sm">
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('success') || session('error') || session('api_error'))
                    <div class="mt-4 space-y-3">
                        @if (session('success'))
                            <div class="alerte alerte-succes flex items-start gap-3 text-sm font-medium" role="status">
                                <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-ok-fort" />
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        @if (session('error') || session('api_error'))
                            <div class="alerte alerte-erreur flex items-start gap-3 text-sm font-medium" role="alert">
                                <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-alert-fort" />
                                <p>{{ session('error') ?: session('api_error') }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <div class="mx-auto w-full max-w-7xl">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>

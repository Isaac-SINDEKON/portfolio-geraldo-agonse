<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Administration') – {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- Barre latérale sur grand écran, en-tête + menu trois traits sur téléphone --}}
    <x-admin-nav />

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Barre de contexte : titre de la page, retour, messages.
             Regroupés dans un seul bandeau plutôt que dispersés dans la page. --}}
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button"
                                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = @js(route('admin.dashboard')); }"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                aria-label="Revenir à la page précédente"
                                title="Revenir à la page précédente">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                        </button>

                        <h1 class="truncate text-lg font-bold text-slate-900 sm:text-xl">
                            @yield('title', 'Administration')
                        </h1>
                    </div>

                    @yield('actions')
                </div>

                @if (session('success') || session('error') || session('api_error'))
                    <div class="mt-4 space-y-3">
                        @if (session('success'))
                            <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
                                <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        @if (session('error') || session('api_error'))
                            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800" role="alert">
                                <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
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

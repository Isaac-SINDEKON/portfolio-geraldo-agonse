<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion – {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
    {{-- min-h-dvh plutôt que min-h-screen : la hauteur suit la barre d'adresse
         du navigateur mobile, qui apparaît puis disparaît au défilement. --}}
    <body class="flex min-h-dvh items-center justify-center overflow-x-hidden bg-nuit px-4 py-8 sm:py-10">
        <div class="w-full max-w-md">
            <div class="text-center">
                <x-marque variante="connexion" />
                <h1 class="mt-4 text-xl font-extrabold text-white">Espace administration</h1>
                <p class="mt-1.5 text-sm text-muted">
                    {{ config('app.name') }}
                </p>
            </div>

            <div class="mt-6 rounded-2xl border border-line-soft bg-white p-6">

            @if (session('success'))
                <div class="alerte alerte-succes mb-5 flex items-start gap-3 text-sm font-medium" role="status">
                    <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="alerte alerte-erreur mb-5 flex items-start gap-3 text-sm font-medium" role="alert">
                    <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="field-label">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           @class(['field', 'field-error' => $errors->has('email')])
                           autocomplete="username" required autofocus>
                    @error('email') <p class="field-message">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-password-field
                        id="password"
                        name="password"
                        label="Mot de passe"
                        autocomplete="current-password" />
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>

            <p class="mt-5 border-t border-line-soft pt-4 text-center text-xs text-muted">
                Accès réservé à l'administrateur du site.
            </p>
        </div>

        <p class="mt-5 text-center">
            <a href="{{ route('home') }}" class="text-sm text-muted underline-offset-4 transition-colors hover:text-white">
                Retour au site
            </a>
        </p>
    </div>
</body>
</html>

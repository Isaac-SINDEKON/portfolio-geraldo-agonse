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
<body class="flex min-h-screen items-center justify-center bg-primary-700 px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center">
            <x-marque variante="connexion" />
            <h1 class="mt-5 text-2xl font-extrabold text-white">Espace administration</h1>
            <p class="mt-2 text-sm text-primary-100">
                {{ config('app.name') }}
            </p>
        </div>

        <div class="mt-8 rounded-2xl bg-white p-7 shadow-soft">
            @if (session('success'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
                    <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800" role="alert">
                    <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
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

                <button type="submit" class="btn-primary w-full">Se connecter</button>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">
                Accès réservé à l'administrateur du site.
            </p>
        </div>

        <p class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm text-primary-100 underline-offset-4 hover:underline">
                Retour au site
            </a>
        </p>
    </div>
</body>
</html>

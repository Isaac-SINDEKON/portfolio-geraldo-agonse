<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends AdminController
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'adresse email',
            'password' => 'mot de passe',
        ]);

        $throttleKey = 'admin-login|'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()
                ->with('error', 'Trop de tentatives de connexion. Réessayez dans une minute.')
                ->withInput($request->only('email'));
        }

        $response = $this->api->post('auth/login', $credentials);

        if ($response === null) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->with('error', session('api_error') ?: 'Identifiants incorrects.')
                ->withInput($request->only('email'));
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $this->api->setToken($response['token'] ?? null);
        $request->session()->put('admin_user', $response['user'] ?? []);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($this->api->isAuthenticated()) {
            $this->api->post('auth/logout');
        }

        $this->api->setToken(null);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Vous êtes déconnecté.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ]);

        $response = $this->api->post('auth/change-password', $validated);

        // Le message doit etre lu avant l'invalidation de session (flush des flashes).
        $errorMessage = $response === null
            ? (session('api_error') ?: 'Impossible de modifier le mot de passe.')
            : null;

        // Le backend revoque tous les tokens apres un changement de mot de passe :
        // la session doit donc etre fermee dans tous les cas.
        $this->api->setToken(null);
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        if ($errorMessage !== null) {
            return redirect()->route('admin.login')->with('error', $errorMessage);
        }

        return redirect()->route('admin.login')
            ->with('success', 'Mot de passe mis à jour. Reconnectez-vous avec vos nouveaux identifiants.');
    }
}

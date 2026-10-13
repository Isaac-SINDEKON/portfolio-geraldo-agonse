<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->with('error', 'Identifiants incorrects.')
                ->withInput($request->only('email'));
        }

        if (! $user->is_admin) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->with('error', 'Accès réservé aux administrateurs.')
                ->withInput($request->only('email'));
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        // La cle de session reste la meme que du temps ou l'administration
        // passait par un jeton Sanctum : les middlewares et les tests s'y
        // referent toujours.
        $request->session()->put('admin_token', Str::random(40));
        $request->session()->put('admin_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_token', 'admin_user']);
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
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ]);

        $user = User::find($request->session()->get('admin_user.id'));

        if (! $user) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expirée, veuillez vous reconnecter.');
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->with('error', 'Le mot de passe actuel est incorrect.')
                ->withInput();
        }

        if (Hash::check($validated['password'], $user->password)) {
            return back()
                ->with('error', 'Le nouveau mot de passe doit être différent de l\'actuel.')
                ->withInput();
        }

        $user->password = $validated['password'];
        $user->save();

        // Apres un changement de mot de passe, la session est fermee : les
        // autres jetons eventuels sont revoques, l'administrateur se reconnecte.
        $user->tokens()->delete();
        $request->session()->forget(['admin_token', 'admin_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Mot de passe mis à jour. Reconnectez-vous avec vos nouveaux identifiants.');
    }
}

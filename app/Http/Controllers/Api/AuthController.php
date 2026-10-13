<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        if (! $user->is_admin) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        $user->tokens()->where('name', 'admin-token')->delete();

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        // Les messages sont explicites : l'application n'embarque pas de fichier
        // de langue, donc sans eux l'API renverrait « validation.confirmed ».
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Mot de passe actuel incorrect.',
                'errors' => ['current_password' => ['Mot de passe actuel incorrect.']],
            ], 422);
        }

        if (Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
            ], 422);
        }

        $user->password = $validated['password'];
        $user->save();

        $user->tokens()->delete();

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }
}

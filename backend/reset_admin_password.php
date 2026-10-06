<?php

/**
 * Reinitialise le mot de passe de l'administrateur sans passer par le site.
 *
 *   php reset_admin_password.php <nouveau-mot-de-passe> [email]
 *
 * Utile quand l'acces a l'administration est perdu : identifiants oublies,
 * mot de passe de depart jamais modifie, ou base prod reseedee. Le mot de
 * passe est hache avec l'algorithme de Laravel, comme lors d'une connexion.
 */

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$motDePasse = $argv[1] ?? null;

if ($motDePasse === null || strlen($motDePasse) < 8) {
    fwrite(STDERR, "Usage : php reset_admin_password.php <nouveau-mot-de-passe> [email]\n");
    fwrite(STDERR, "Le mot de passe doit contenir au moins 8 caracteres.\n");
    exit(1);
}

$email = $argv[2] ?? null;

$query = User::where('is_admin', true);

if ($email !== null) {
    $query->where('email', $email);
}

$admin = $query->first();

if (! $admin) {
    fwrite(STDERR, 'Aucun administrateur trouve'.($email !== null ? ' pour '.$email : '').".\n");
    fwrite(STDERR, "Lancez d'abord : php artisan migrate --force && php artisan db:seed --force\n");
    exit(1);
}

$admin->password = Hash::make($motDePasse);
$admin->save();

// Invalide les anciens tokens afin de couper toute session deja ouverte.
$admin->tokens()->delete();

echo "Mot de passe reinitialise pour ".$admin->email.' ('.$admin->name.").\n";
echo "Connectez-vous a l'administration avec ce nouveau mot de passe.\n";
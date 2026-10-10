<?php

/**
 * Audit du changement de mot de passe administrateur (CC §22).
 *
 * Reproduit le parcours complet vu par l'administrateur :
 * formulaire du tableau de bord -> appel API -> connexion.
 *
 * Ne touche jamais au vrai compte : un compte administrateur temporaire est
 * cree, utilise, puis supprime. Le vrai mot de passe n'est donc pas modifie.
 *
 * Prerequis : le serveur doit tourner (demarrer.ps1).
 *
 *   php geraldoportfolio/verify_password.php
 */

use App\Models\User;
use Illuminate\Contracts\Http\Kernel;

$racine = dirname(__DIR__);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$api = rtrim(config('app.url'), '/').'/api/v1';
$emailTemporaire = 'test.motdepasse@exemple.org';

$reussis = 0;
$echecs = 0;

function verif(string $libelle, bool $condition, string $detail = ''): void
{
    global $reussis, $echecs;

    if ($condition) {
        $reussis++;
        echo "  [OK]   $libelle".($detail ? "  \xE2\x80\x94 $detail" : '')."\n";
    } else {
        $echecs++;
        echo "  [KO]   $libelle".($detail ? "  \xE2\x80\x94 $detail" : '')."\n";
    }
}

function api(string $url, array $payload = [], ?string $token = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => array_values(array_filter([
            'Content-Type: application/json',
            'Accept: application/json',
            $token ? 'Authorization: Bearer '.$token : null,
        ])),
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erreur = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['code' => 0, 'json' => [], 'erreur' => $erreur];
    }

    return ['code' => $code, 'json' => json_decode($body, true) ?? [], 'erreur' => ''];
}

echo "======================================================================\n";
echo "  AUDIT DU CHANGEMENT DE MOT DE PASSE ADMINISTRATEUR\n";
echo "======================================================================\n";

$atteignable = api($api.'/auth/login', ['email' => 'inconnu@exemple.org', 'password' => 'x']);

if ($atteignable['code'] === 0) {
    echo "\n  ERREUR : l'API geraldoportfolio ne repond pas sur ".$api.".\n";
    echo "  Lancez .\demarrer.ps1 puis relancez cet audit.\n";
    exit(1);
}

echo "\n== 1. Preparation : compte administrateur temporaire ==\n";

$ancien = User::where('email', $emailTemporaire)->first();

if ($ancien) {
    $ancien->tokens()->delete();
    $ancien->delete();
}

$user = User::create([
    'name' => 'Test Mot de passe',
    'email' => $emailTemporaire,
    'password' => 'MotDePasseInitial2026',
    'is_admin' => true,
]);

verif('Compte temporaire cree', $user->exists, $emailTemporaire);

echo "\n== 2. L'API refuse un changement sans la confirmation ==\n";

$r = api($api.'/auth/login', ['email' => $emailTemporaire, 'password' => 'MotDePasseInitial2026']);
$token = $r['json']['token'] ?? null;

verif('Connexion au compte temporaire', $r['code'] === 200 && $token !== null, 'HTTP '.$r['code']);

if ($token) {
    $r = api($api.'/auth/change-password', [
        'current_password' => 'MotDePasseInitial2026',
        'password' => 'NouveauMotDePasse2026',
    ], $token);

    verif('Refus quand password_confirmation est absent', $r['code'] === 422, 'HTTP '.$r['code']);
    verif(
        'Message lisible, pas une cle de traduction brute',
        ! str_contains($r['json']['message'] ?? '', 'validation.'),
        $r['json']['message'] ?? '(vide)'
    );

    $r = api($api.'/auth/login', ['email' => $emailTemporaire, 'password' => 'NouveauMotDePasse2026']);
    verif('Mot de passe reste inchange apres un refus', $r['code'] === 401, 'HTTP '.$r['code']);

    $r = api($api.'/auth/change-password', [
        'current_password' => 'MauvaisMotDePasse',
        'password' => 'NouveauMotDePasse2026',
        'password_confirmation' => 'NouveauMotDePasse2026',
    ], $token);
    verif('Refus quand le mot de passe actuel est faux', $r['code'] === 422, 'HTTP '.$r['code']);

    $r = api($api.'/auth/change-password', [
        'current_password' => 'MotDePasseInitial2026',
        'password' => 'MotDePasseInitial2026',
        'password_confirmation' => 'MotDePasseInitial2026',
    ], $token);
    verif('Refus quand le nouveau mot de passe est identique', $r['code'] === 422, 'HTTP '.$r['code']);

    echo "\n== 3. Avec la confirmation, le changement est ecrit ==\n";

    $r = api($api.'/auth/change-password', [
        'current_password' => 'MotDePasseInitial2026',
        'password' => 'NouveauMotDePasse2026',
        'password_confirmation' => 'NouveauMotDePasse2026',
    ], $token);

    verif('Changement accepte', $r['code'] === 200, 'HTTP '.$r['code'].' '.$r['json']['message'] ?? '');

    $r = api($api.'/auth/login', ['email' => $emailTemporaire, 'password' => 'NouveauMotDePasse2026']);
    verif('Connexion avec le nouveau mot de passe', $r['code'] === 200, 'HTTP '.$r['code']);

    $r = api($api.'/auth/login', ['email' => $emailTemporaire, 'password' => 'MotDePasseInitial2026']);
    verif('Ancien mot de passe refuse', $r['code'] === 401, 'HTTP '.$r['code']);
} else {
    verif('Connexion au compte temporaire', false, 'aucun token');
}

echo "\n== 4. Le formulaire et le controleur transmettent la confirmation ==\n";

$vue = (string) file_get_contents($racine.'/geraldoportfolio/resources/views/admin/dashboard.blade.php');
$controleur = (string) file_get_contents($racine.'/geraldoportfolio/app/Http/Controllers/Admin/AuthController.php');

verif('Le formulaire contient un champ password_confirmation',
    str_contains($vue, 'name="password_confirmation"'));
verif('Le controleur transmet password_confirmation a l\'API',
    str_contains($controleur, "'password_confirmation'"));
verif('Le controleur n\'envoie plus seulement $validated',
    ! preg_match("/post\('auth\/change-password',\s*\\\$validated\s*\)/", $controleur));

echo "\n== 5. Nettoyage ==\n";

$user->tokens()->delete();
$user->delete();

verif('Compte temporaire supprime', ! User::where('email', $emailTemporaire)->exists());
verif('Le vrai compte administrateur est intact', User::where('email', 'geraldoagonse@gmail.com')->exists());

echo "\n".str_repeat('=', 70)."\n";
echo "  Reussis: $reussis | Echecs: $echecs\n";
echo str_repeat('=', 70)."\n";
echo $echecs === 0
    ? "  L'administrateur peut changer son mot de passe.\n"
    : "  Le changement de mot de passe est CASSE : ne pas livrer en l'etat.\n";

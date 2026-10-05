<?php

/**
 * Audit de la gestion des numéros supplémentaires (CC §22 « coordonnées »).
 *
 * Vérifie que le propriétaire peut ajouter autant de numéros qu'il souhaite,
 * que chacun devient cliquable sur le site, et qu'un seul peut porter le
 * bouton WhatsApp flottant (CC §17).
 *
 * Usage : php verify_extra_phones.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;

$frontend = rtrim($argv[1] ?? 'http://127.0.0.1:8001', '/');
$backend = rtrim($argv[2] ?? 'http://127.0.0.1:8000', '/').'/api/v1';

$pass = 0;
$fail = 0;
$failures = [];

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $failures;

    if ($ok) {
        $pass++;
        printf("  [OK]   %s%s\n", $label, $detail ? " — $detail" : '');
    } else {
        $fail++;
        $failures[] = $label.($detail ? " ($detail)" : '');
        printf("  [ECHEC] %s%s\n", $label, $detail ? " — $detail" : '');
    }
}

function section(string $title): void
{
    echo "\n== $title ==\n";
}

$backend = $backend;
$jar = new CookieJar();

// Numero WhatsApp tel qu'il est actuellement configure : le test ne doit pas
// dependre d'une valeur codee en dur, qui deviendrait fausse des que le
// proprietaire change de numero.
$siteActuel = json_decode((string) Http::get($backend.'/site')->body(), true)['settings'] ?? [];
$whatsappPrincipal = (string) ($siteActuel['whatsapp'] ?? '');
$lienWaPrincipal = 'wa.me/'.preg_replace('/\D+/', '', $whatsappPrincipal);

$reglagesBase = [
    'name' => 'Géraldo Perridys AGONSE',
    'role' => 'Formateur',
    'tagline' => 'Développer les compétences. Optimiser la performance. Transformer les pratiques.',
    'whatsapp' => $whatsappPrincipal,
    'phone' => '+229 98 54 54 14',
    'email' => 'geraldoagonse@gmail.com',
    'location' => 'Bénin · Togo',
];

$lireSite = fn () => Http::get($backend.'/site')->json('settings') ?? [];

/**
 * Chaque envoi doit porter le jeton CSRF de la page, comme le ferait le
 * navigateur : on relit le formulaire avant chaque POST.
 */
$enregistrer = function (array $extra) use ($jar, $frontend, $reglagesBase) {
    $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/contenu/reglages')->body();
    preg_match('/name="_token"\s+value="([^"]+)"/', $page, $m);

    // Le marqueur est toujours présent dans le vrai formulaire, y compris
    // quand la liste est vide : le reproduire ici aussi.
    return Http::withOptions(['cookies' => $jar])
        ->asForm()
        ->post($frontend.'/admin/contenu/reglages', $reglagesBase
            + ['_token' => $m[1] ?? '', 'extra_phones_present' => '1']
            + $extra);
};

// Connexion : la page de connexion fournit le jeton CSRF, comme le navigateur.
$pageLogin = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/login')->body();
preg_match('/name="_token"\s+value="([^"]+)"/', $pageLogin, $mLogin);

Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/login', [
    '_token' => $mLogin[1] ?? '',
    'email' => 'geraldoagonse@gmail.com',
    'password' => 'Geraldo@2026',
]);

check('Connexion administrateur', str_contains(
    Http::withOptions(['cookies' => $jar])->get($frontend.'/admin')->body(),
    'Tableau de bord'
));

section('1. Saisie dans l\'administration');

$reglages = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/contenu/reglages')->body();
check('Page « Contenu du site » accessible', str_contains($reglages, 'Coordonnées'));
check('Bloc « Numéros supplémentaires » présent', str_contains($reglages, 'Numéros supplémentaires'));
check('Bouton « Ajouter un numéro » présent', str_contains($reglages, 'Ajouter un numéro'));
check('Champ de saisie des numéros supplémentaires', str_contains($reglages, 'is_whatsapp'));

// Le test part d'un état vide connu, quel que soit le contenu réel du site.
$enregistrer(['extra_phones' => []]);
check('Liste initialisee a vide', ($lireSite()['extra_phones'] ?? []) === []);

section('2. Enregistrement de plusieurs numéros');

$save = $enregistrer(['extra_phones' => [
    ['label' => 'Bureau Lomé', 'number' => '+228 90 11 22 33', 'is_whatsapp' => '0'],
    ['label' => 'Mobile Togo', 'number' => '+228 98 54 54 14', 'is_whatsapp' => '1'],
]]);
check('Enregistrement via l\'administration', $save->successful() || $save->status() === 302, 'HTTP '.$save->status());

$phones = $lireSite()['extra_phones'] ?? [];
check('Deux numéros enregistrés', is_array($phones) && count($phones) === 2, json_encode($phones, JSON_UNESCAPED_UNICODE));
check('Libellé et numéro du premier conservés',
    ($phones[0]['label'] ?? '') === 'Bureau Lomé' && ($phones[0]['number'] ?? '') === '+228 90 11 22 33');
check('Un seul numéro porte le badge WhatsApp',
    count(array_filter($phones, fn ($p) => $p['is_whatsapp'] ?? false)) === 1,
    json_encode(array_column($phones, 'is_whatsapp')));

section('3. Affichage public (CC §16 et §17)');

$contact = Http::get($frontend.'/contact')->body();
check('Libellé du numéro supplémentaire affiché', str_contains($contact, 'Bureau Lomé'));
check('Numéro supplémentaire cliquable en tel:', str_contains($contact, 'tel:+22890112233'));
check('Lien WhatsApp du numéro supplémentaire', str_contains($contact, 'wa.me/22890112233'));
check('Bouton flottant WhatsApp sur le numéro désigné', str_contains($contact, 'wa.me/22898545414'), 'Mobile Togo');
check('Ancien WhatsApp principal écarté du bouton flottant', ! str_contains($contact, $lienWaPrincipal), $lienWaPrincipal);
check('Téléphone principal toujours cliquable', str_contains($contact, 'tel:+22998545414'));
check('Email toujours cliquable', str_contains($contact, 'mailto:geraldoagonse@gmail.com'));

$accueil = Http::get($frontend.'/')->body();
check('Bouton WhatsApp flottant présent sur l\'accueil', str_contains($accueil, 'wa.me/22898545414'));

section('4. Un seul badge WhatsApp même si plusieurs sont cochés');

$enregistrer(['extra_phones' => [
    ['label' => 'A', 'number' => '+229 97 00 00 01', 'is_whatsapp' => '1'],
    ['label' => 'B', 'number' => '+229 97 00 00 02', 'is_whatsapp' => '1'],
]]);
$phones = $lireSite()['extra_phones'] ?? [];
check('Les deux lignes sont conservées', count($phones) === 2);
check('Un seul numéro_marké WhatsApp', count(array_filter($phones, fn ($p) => $p['is_whatsapp'] ?? false)) === 1);
$contact = Http::get($frontend.'/contact')->body();
check('Le premier coché devient le bouton flottant', str_contains($contact, 'wa.me/22997000001'));
check('Le second n\'est pas retenu', ! str_contains($contact, 'wa.me/22997000002&text'));

section('5. Robustesse');

$enregistrer(['extra_phones' => [
    ['label' => '', 'number' => '+229 97 00 00 03', 'is_whatsapp' => '0'],
    ['label' => 'Sans numéro', 'number' => '', 'is_whatsapp' => '0'],
    ['label' => 'Ligne valide', 'number' => '+229 97 00 00 04', 'is_whatsapp' => '0'],
]]);
$phones = $lireSite()['extra_phones'] ?? [];
check('Les lignes incomplètes sont ignorées', count($phones) === 1, json_encode($phones, JSON_UNESCAPED_UNICODE));
check('La ligne complète est conservée', ($phones[0]['label'] ?? '') === 'Ligne valide');
check('Page contact toujours en 200', Http::get($frontend.'/contact')->successful());

section('6. Nettoyage');

// Le dernier retrait de ligne ne produit aucun champ extra_phones[...]. Le
// formulaire envoie donc un marqueur explicite, sinon le vide serait
// confondu avec un envoi partiel.
$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/contenu/reglages')->body();
check('Marqueur de liste vide présent dans le formulaire', str_contains($page, 'extra_phones_present'));

$vider = $enregistrer(['extra_phones' => []]);
check('Liste vidée', ($lireSite()['extra_phones'] ?? []) === [], 'HTTP '.$vider->status());
$contact = Http::get($frontend.'/contact')->body();
check('WhatsApp principal rétabli', str_contains($contact, $lienWaPrincipal), $lienWaPrincipal);
check('Plus de bloc « Autres numéros »', ! str_contains($contact, 'Autres numéros'));

echo "\n".str_repeat('-', 60)."\n";
printf("Résultat : %d réussis, %d échecs\n", $pass, $fail);

if ($fail > 0) {
    echo "\nÉchecs :\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
}

exit($fail > 0 ? 1 : 0);

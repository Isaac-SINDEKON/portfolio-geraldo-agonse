<?php

/**
 * Audit end-to-end du frontend (site public + administration).
 *
 * Usage :
 *   php verify_frontend.php [url_frontend] [url_backend]
 *
 * Le frontend et le backend doivent tourner :
 *   php artisan serve --port=8001   (frontend)
 *   php artisan serve --port=8000   (backend)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;

$frontend = rtrim($argv[1] ?? 'http://127.0.0.1:8001', '/');
$backend = rtrim($argv[2] ?? 'http://127.0.0.1:8000', '/');

$email = 'geraldoagonse@gmail.com';
$password = 'Admin@2026';

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
        $failures[] = $label.($detail ? " — $detail" : '');
        printf("  [ECHEC] %s%s\n", $label, $detail ? " — $detail" : '');
    }
}

function section(string $title): void
{
    echo "\n== $title ==\n";
}

/* ------------------------------------------------------------------ */
section('1. Pages publiques');

$publicPages = [
    '/' => 'Accueil',
    '/a-propos' => 'À propos',
    '/formations' => 'Formations',
    '/services-entreprises' => 'Services entreprises',
    '/experience-expertise' => 'Expérience & expertise',
    '/temoignages' => 'Témoignages',
    '/galerie' => 'Galerie',
    '/contact' => 'Contact',
];

$html = [];

foreach ($publicPages as $path => $label) {
    $response = Http::get($frontend.$path);
    $html[$path] = $response->body();

    check($label.' ('.$path.')', $response->successful(), 'HTTP '.$response->status());
}

check('Page inconnue renvoie 404', Http::get($frontend.'/page-inexistante')->status() === 404);

section('2. Contenu issu de l\'API backend');

$site = Http::acceptJson()->get($backend.'/api/v1/site');

check('API /site joignable', $site->successful(), 'HTTP '.$site->status());

if ($site->successful()) {
    $data = $site->json();
    $name = $data['settings']['name'] ?? '';

    check('Nom affiché sur l\'accueil', str_contains($html['/'], $name), $name);
    check('Domaines d’intervention : 4 minimum', count($data['domains'] ?? []) >= 4, count($data['domains'] ?? []).' domaine(s)');
    check('Catalogue : 4 formations minimum', count($data['formations'] ?? []) >= 4, count($data['formations'] ?? []).' formation(s)');
    check('Services : 4 entrées minimum', count($data['services'] ?? []) >= 4);
    check('Expériences : 5 entrées minimum', count($data['experiences'] ?? []) >= 5);
    check('Témoignages : 4 entrées minimum', count($data['testimonials'] ?? []) >= 4);
    check('Galerie : au moins 1 image', count($data['gallery'] ?? []) >= 1, count($data['gallery'] ?? []).' image(s)');
    // CC §5 et §17 : le bouton WhatsApp flottant doit etre present sur toutes
    // les pages, avec le numero reellement enregistre dans l'administration.
    $whatsappDigits = preg_replace('/\D+/', '', (string) ($data['settings']['whatsapp'] ?? ''));
    $waLien = 'wa.me/'.$whatsappDigits;
    $pagesSansBouton = [];

    foreach ($html as $chemin => $contenu) {
        if (! str_contains($contenu, $waLien)) {
            $pagesSansBouton[] = $chemin;
        }
    }

    check(
        'Bouton WhatsApp flottant present sur les 8 pages (CC §17)',
        count($pagesSansBouton) === 0,
        $pagesSansBouton ? 'absent de : '.implode(', ', $pagesSansBouton) : $waLien
    );
    check('Message WhatsApp prérempli admin', str_contains($html['/contact'], rawurlencode($data['settings']['whatsapp_message'] ?? 'zzz')) || ($data['settings']['whatsapp_message'] ?? '') !== '');

    // CC §5 : WhatsApp, telephone et email du professionnel doivent etre cliquables
    check('WhatsApp du professionnel cliquable (wa.me)', $whatsappDigits !== '', (string) ($data['settings']['whatsapp'] ?? ''));
    check('Telephone du professionnel cliquable (tel:)', str_contains($html['/contact'], 'tel:'.preg_replace('/[^\d+]/', '', (string) ($data['settings']['phone'] ?? ''))), (string) ($data['settings']['phone'] ?? ''));
    check('Email du professionnel cliquable (mailto:)', str_contains($html['/contact'], 'mailto:'.($data['settings']['email'] ?? '')), (string) ($data['settings']['email'] ?? ''));

    // CC §13 / §8 : les images doivent pointer vers une URL absolue servie par l'API
    $imagesPage = [
        '/' => 'photo de la page d’accueil',
        '/a-propos' => 'photo de la page À propos',
        '/galerie' => 'vignettes de la galerie',
    ];

    foreach ($imagesPage as $page => $label) {
        $srcs = [];

        preg_match_all('/<img[^>]*src="([^"]*)"/i', $html[$page], $matches);

        foreach ($matches[1] as $src) {
            if (str_contains($src, '{{') || str_contains($src, 'current?.url')) {
                continue;
            }

            $srcs[] = $src;
        }

        $relatives = array_values(array_filter($srcs, fn ($src) => ! str_starts_with($src, 'http')));

        check($label.' : URLs absolues', $relatives === [], implode(', ', $relatives));

        $injoignables = [];

        foreach ($srcs as $src) {
            try {
                if (! Http::timeout(8)->get($src)->successful()) {
                    $injoignables[] = $src;
                }
            } catch (\Throwable) {
                $injoignables[] = $src;
            }
        }

        check($label.' : images joignables', $injoignables === [], implode(', ', $injoignables));
    }

    // CC §14 / §15 : le choix du type de demande doit être proposé et pré-remplissable
    check('Choix formation / devis affiché', str_contains($html['/contact'], 'Que souhaitez-vous me confier')
        && str_contains($html['/contact'], 'Demander une formation')
        && str_contains($html['/contact'], 'Demander un devis'));

    // Le choix doit aussi être faisable depuis la page, pas seulement dans le formulaire
    check('Choix cliquable sur la page contact (2 boutons)', str_contains($html['/contact'], 'id="choisir-formation"')
        && str_contains($html['/contact'], 'id="choisir-devis"')
        && str_contains($html['/contact'], "demande-type"));
    check('Choix sur la page : libellés exacts du CC', str_contains($html['/contact'], 'Que souhaitez-vous me confier')
        && str_contains($html['/contact'], 'Demander une formation')
        && str_contains($html['/contact'], 'Demander un devis'));

    // Un seul couple de cartes de choix sur la page : le composant Livewire ne
    // doit pas redessiner un second selecteur. On compte les elements identifes,
    // pas le texte, qui apparait aussi dans les boutons d'appel a l'action.
    check('Contact : un seul bloc de choix (pas de doublon)',
        substr_count($html['/contact'], 'id="choisir-formation"') === 1
        && substr_count($html['/contact'], 'id="choisir-devis"') === 1
        && ! str_contains($html['/contact'], 'role="tablist"')
        && ! str_contains($html['/contact'], 'onglet-formation'));

    // CC 14 et CC 15 : deux formulaires distincts, presents en meme temps.
    // On isole chaque bloc (id formulaire-demande / formulaire-devis) plutot
    // que de fouiller toute la page, pour verifier la separation reelle.
    $blocs = [];
    foreach (['/contact', '/contact?form=devis'] as $variante) {
        $page = Http::get($frontend.$variante)->body();

        foreach (['demande' => 'id="formulaire-demande"', 'devis' => 'id="formulaire-devis"'] as $cle => $ancre) {
            $debut = strpos($page, $ancre);
            $suite = strpos($page, '<div id="formulaire-', $debut + strlen($ancre));
            $blocs[$cle][$variante] = $debut === false
                ? ''
                : substr($page, $debut, $suite === false ? null : $suite - $debut);
        }
    }

    $formation = $blocs['demande']['/contact'];
    $devis = $blocs['devis']['/contact'];

    check('Contact : le formulaire de formation est présent', $formation !== ''
        && str_contains($formation, 'Formulaire de demande de formation')
        && str_contains($formation, 'Envoyer ma demande'));

    check('Contact : le formulaire de devis est présent', $devis !== ''
        && str_contains($devis, 'Formulaire de demande de devis')
        && str_contains($devis, 'Demander un devis'));

    check('Contact : les deux formulaires sont visibles sur la même page',
        $formation !== '' && $devis !== '' && $formation !== $devis
        && $blocs['devis']['/contact?form=devis'] !== '');

    check('Contact : ?form=devis affiche bien le formulaire de devis',
        str_contains($blocs['devis']['/contact?form=devis'], 'Formulaire de demande de devis')
        && str_contains($blocs['devis']['/contact?form=devis'], 'Besoins particuliers'));

    check('Contact : le formulaire de formation a ses champs propres',
        str_contains($formation, 'Format')
        && str_contains($formation, 'Fonction'));

    // CC 14 et CC 15 : aucun champ propre a un formulaire ne doit apparaitre
    // dans l'autre. Les identifiants de champs sont prefixes par formulaire,
    // ce qui rend la verification exacte.
    check('Contact : le formulaire de formation ne contient aucun champ de devis',
        ! str_contains($formation, 'devis-'));

    check('Contact : le formulaire de devis ne contient aucun champ de formation',
        ! str_contains($devis, 'formation-'));

    check('Contact : chaque formulaire n\'a qu\'un seul bloc de champs',
        substr_count($formation, 'wire:submit="submit"') === 1
        && substr_count($devis, 'wire:submit="submit"') === 1);

    check('Contact : les deux boutons du CC sont distincts',
        str_contains($formation, 'Envoyer ma demande')
        && str_contains($devis, 'Demander un devis')
        && ! str_contains($formation, 'Demander un devis'));

    check('Contact : le budget est bien facultatif et propre au devis',
        str_contains($devis, 'facultatif')
        && ! str_contains($formation, 'Budget indicatif'));

    $prefilled = Http::get($frontend.'/contact?form=devis&theme=Vente et techniques commerciales')->body();
    check('Pré-remplissage du thème depuis une formation',
        str_contains($prefilled, 'Vente et techniques commerciales'),
        'demande de devis pré-remplie avec le thème de la formation');
}

section('3. SEO');

$sitemap = Http::get($frontend.'/sitemap.xml');
$robots = Http::get($frontend.'/robots.txt');
$home = $html['/'];

check('sitemap.xml accessible', $sitemap->successful() && str_contains($sitemap->body(), '<urlset'));
check('sitemap liste les 8 pages', substr_count($sitemap->body(), '<url>') === 8 + count(Http::get($backend.'/api/v1/formations')->json() ?: []));
check('robots.txt accessible', $robots->successful() && str_contains($robots->body(), 'Sitemap:'));
check('robots.txt bloque /admin', str_contains($robots->body(), 'Disallow: /admin'));
check('Balise title', (bool) preg_match('#<title>.+</title>#', $home));
check('Meta description', str_contains($home, 'name="description"'));
check('Mots-clés SEO', str_contains($home, 'name="keywords"'));
check('URL canonique', str_contains($home, 'rel="canonical"'));
check('Open Graph', str_contains($home, 'property="og:title"'));
check('Données structurées JSON-LD', str_contains($home, 'application/ld+json'));
check('Un seul H1 par page', substr_count($home, '<h1') === 1, substr_count($home, '<h1').' H1');

section('4. Formulaires (Livewire + API leads)');

check('Formulaire de formation présent', str_contains($html['/contact'], 'Envoyer ma demande'));
check('Formulaire de devis présent', str_contains($html['/contact'], 'Demander un devis'));
check('Champ anti-spam présent sur chaque formulaire',
    str_contains($html['/contact'], 'wire:model="website"'));
check('Scripts Livewire chargés', str_contains($home, 'livewire'));

// Envoi reel via l'API (ce que fait le composant Livewire)
$leadPayload = [
    'type' => 'formation',
    'organisation' => 'Organisation de verification',
    'responsable' => 'Responsable test',
    'telephone' => '+229 90 00 00 00',
    'indicatif_pays' => '229',
    'email' => 'test@example.com',
    'theme' => 'Formation à distance',
    'message' => "Audit automatique du frontend.\nDeuxieme ligne : le message doit\ns'afficher sur plusieurs lignes.",
];

/**
 * Le point d'entrée des demandes est limité à 5 envois/minute/IP (anti-spam,
 * CC §25). Enchaîner plusieurs audits sature cette limite : on patiente et on
 * réessaie une fois plutôt que de signaler un faux échec.
 */
function postLead(array $payload)
{
    $response = Http::acceptJson()->post($GLOBALS['backend'].'/api/v1/leads', $payload);

    if ($response->status() === 429) {
        echo "  (limite anti-spam atteinte, attente 65 s)\n";
        sleep(65);
        $response = Http::acceptJson()->post($GLOBALS['backend'].'/api/v1/leads', $payload);
    }

    return $response;
}

$send = postLead($leadPayload);
check('Envoi demande de formation (201)', $send->status() === 201, 'HTTP '.$send->status());

$honeypot = postLead($leadPayload + ['website' => 'http://spam.example.com']);
check('Anti-spam : honeypot silencieusement accepte', $honeypot->status() === 201, 'HTTP '.$honeypot->status());

$invalide = postLead(['type' => 'formation', 'organisation' => '']);
check('Validation serveur (422)', $invalide->status() === 422, 'HTTP '.$invalide->status());

section('5. Administration');

$jar = new CookieJar();

$loginPage = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/login');
preg_match('/name="_token"\s+value="([^"]+)"/', $loginPage->body(), $matches);
$csrf = $matches[1] ?? '';

check('Page de connexion accessible', $loginPage->successful() && $csrf !== '');

$mauvais = Http::withOptions(['cookies' => $jar])->post($frontend.'/admin/login', [
    '_token' => $csrf,
    'email' => $email,
    'password' => 'mauvais-mot-de-passe',
]);

check('Mauvais identifiants refusés', str_contains($mauvais->body(), 'Identifiants incorrects'));

$loginPage = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/login');
preg_match('/name="_token"\s+value="([^"]+)"/', $loginPage->body(), $matches);
$csrf = $matches[1] ?? '';

$login = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/login', [
    '_token' => $csrf,
    'email' => $email,
    'password' => $password,
]);

check('Connexion administrateur', str_contains($login->body(), 'Tableau de bord'));
check('Tableau de bord alimenté par l\'API (token Sanctum valide)', str_contains($login->body(), 'Demandes reçues') && str_contains($login->body(), 'geraldoagonse@gmail.com'));

$adminPages = [
    '/admin' => 'Tableau de bord',
    '/admin/contenu' => 'Hub du contenu',
    '/admin/contenu/reglages' => 'Réglages du contenu',
    '/admin/formations' => 'Formations',
    '/admin/formations/1/modifier' => 'Édition d\'une formation',
    '/admin/sections/domains' => 'Domaines',
    '/admin/sections/reasons' => 'Raisons',
    '/admin/sections/services' => 'Services',
    '/admin/sections/experiences' => 'Expériences',
    '/admin/temoignages' => 'Témoignages',
    '/admin/galerie' => 'Galerie',
    '/admin/demandes' => 'Demandes',
];

foreach ($adminPages as $path => $label) {
    $response = Http::withOptions(['cookies' => $jar])->get($frontend.$path);
    check('Admin : '.$label, $response->successful(), 'HTTP '.$response->status());
}

// Navigation : toutes les rubriques accessibles, avec un menu « trois traits »
// sur téléphone et une barre laterale verticale sur grand ecran.
$navHtml = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin')->body();

// Les libelles reprennent ceux du site public (CC §6) : le proprietaire retrouve
// le meme vocabulaire des deux cotes.
$rubriquesAdmin = [
    '/admin' => 'Tableau de bord',
    '/admin/demandes' => 'Demandes reçues',
    '/admin/contenu/reglages' => 'Identité &amp; coordonnées',
    '/admin/formations' => 'Formations',
    '/admin/temoignages' => 'Témoignages',
    '/admin/galerie' => 'Galerie',
    '/admin/sections/domains' => 'Accueil',
    '/admin/sections/reasons' => 'Raisons de solliciter',
    '/admin/sections/services' => 'Services entreprises',
    '/admin/sections/experiences' => 'Expérience',
];

$manquantes = [];

foreach ($rubriquesAdmin as $href => $libelle) {
    if (! str_contains($navHtml, $libelle) || ! str_contains($navHtml, 'href="'.$frontend.$href.'"')) {
        $manquantes[] = $libelle;
    }
}

check('Admin : toutes les rubriques sont accessibles depuis le menu', $manquantes === [],
    $manquantes ? 'manquant : '.implode(', ', $manquantes) : count($rubriquesAdmin).' rubriques');

check('Admin : menu trois traits sur petit écran', str_contains($navHtml, 'aria-label="Ouvrir le menu"')
    && str_contains($navHtml, 'x-data="{ open: false }"')
    && str_contains($navHtml, 'id="menu-admin"'));

check('Admin : barre latérale verticale sur grand écran', str_contains($navHtml, 'hidden h-screen w-64 shrink-0 flex-col')
    && str_contains($navHtml, 'lg:flex'));

// Une seule rubrique doit etre signalee active, sur chaque page. Le menu etant
// rendu deux fois (barre laterale + tiroir mobile), on deduplique les libelles.
$actifsIncorrects = [];

foreach (array_keys($rubriquesAdmin) as $href) {
    $htmlPage = Http::withOptions(['cookies' => $jar])->get($frontend.$href)->body();

    // On isole chaque lien du menu, puis on garde ceux marques actifs.
    $blocs = [];
    preg_match_all('~<a href="[^"]*admin[^"]*"[^>]*aria-current="page"[^>]*>(.*?)</a>~s', $htmlPage, $blocs);

    $libelles = [];

    foreach ($blocs[1] as $bloc) {
        if (preg_match('~truncate">([^<]*)<~', $bloc, $m)) {
            $libelles[] = trim($m[1]);
        }
    }

    $libelles = array_values(array_unique($libelles));

    if (count($libelles) !== 1) {
        $actifsIncorrects[] = $href.' -> '.($libelles === [] ? 'aucune' : count($libelles).' entrées');
    }
}

check('Admin : une seule rubrique active par page', $actifsIncorrects === [],
    $actifsIncorrects ? implode(' ; ', $actifsIncorrects) : count($rubriquesAdmin).' pages vérifiées');

// La page « Contenu du site » est un point d'entree : elle doit rendre
// atteignable chaque section editable (CC §22).
$hubHtml = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/contenu')->body();

$sectionsDuHub = [
    'Accueil' => '/admin/sections/domains',
    'Raisons de solliciter' => '/admin/sections/reasons',
    'Services aux entreprises' => '/admin/sections/services',
    'Expérience &amp; expertise' => '/admin/sections/experiences',
    'Formations' => '/admin/formations',
    'Témoignages' => '/admin/temoignages',
    'Galerie photos' => '/admin/galerie',
    'Présentation &amp; coordonnées' => '/admin/contenu/reglages',
];

$sectionsManquantes = [];

foreach ($sectionsDuHub as $libelle => $href) {
    if (! str_contains($hubHtml, $libelle) || ! str_contains($hubHtml, 'href="'.$frontend.$href.'"')) {
        $sectionsManquantes[] = $libelle;
    }
}

check('Admin : toutes les sections sont accessibles depuis la page Contenu', $sectionsManquantes === [],
    $sectionsManquantes ? 'manquant : '.implode(', ', $sectionsManquantes) : count($sectionsDuHub).' sections');

section('6. CRUD via l\'administration');

// Ajout d'un témoignage
$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/temoignages');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);
$csrf = $matches[1] ?? '';

$create = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/temoignages', [
    '_token' => $csrf,
    'author' => 'Audit automatique',
    'fonction' => 'Verification',
    'content' => 'Témoignage créé par verify_frontend.php.',
    'active' => '1',
]);

check('Création d\'un témoignage', str_contains($create->body(), 'Le témoignage a été ajouté'));

// La demande de test doit apparaître dans l'administration
$leads = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes');
check('Demande de test visible dans l\'admin', str_contains($leads->body(), 'Organisation de verification'));

// Le téléphone et l'email du client doivent servir à le contacter directement
check('Admin : WhatsApp du client depuis la liste des demandes', str_contains($leads->body(), 'wa.me/2299000000'),
    'le numéro saisi comme téléphone est utilisé en wa.me');

if (preg_match('#href="'.preg_quote($frontend, '#').'/admin/demandes/(\d+)"[^>]*>\s*(?:<[^>]+>\s*)*Organisation de verification#s', $leads->body(), $m)) {
    $detail = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes/'.$m[1]);
    $detailHtml = $detail->body();

    check('Admin : détail de la demande accessible', $detail->successful(), 'HTTP '.$detail->status());
    check('Admin : WhatsApp du client sur la fiche demande', str_contains($detailHtml, 'wa.me/2299000000'));
    check('Admin : appel direct au client (tel:)', str_contains($detailHtml, 'tel:+2299000000'));
    check('Admin : email du client en mailto:', str_contains($detailHtml, 'mailto:test@example.com'));
    check('Admin : message affiché sur plusieurs lignes (whitespace-pre-line)', str_contains($detailHtml, 'whitespace-pre-line'));
    check('Admin : message du client rendu en pleine largeur', str_contains($detailHtml, 'max-w-prose'));

    // Le pays choisi par le client doit être mémorisé et indiqué à l'admin,
    // sinon un numéro togo serait responddu comme s'il était bénin.
    check('Admin : pays du client mémorisé', str_contains($detailHtml, 'Bénin'), 'indicatif pays affiché sur la fiche');
} else {
    check('Admin : détail de la demande accessible', false, 'lien de détail introuvable');
}

// Sélecteur de pays et validation du numéro (numéro joignable, CC §17)
check('Contact : sélection du pays du téléphone', str_contains($html['/contact'], 'wire:model.live="indicatif_pays"'));
check('Contact : indicatif du Bénin proposé', str_contains($html['/contact'], '+229'));
check('Contact : Togo proposé en plus du Bénin', str_contains($html['/contact'], '+228'));
check('Contact : pays hors Afrique proposé', str_contains($html['/contact'], '+33'));
check('Contact : exemple de saisie selon le pays', str_contains($html['/contact'], '01 96 12 34 56')
    && str_contains($html['/contact'], 'sans l\'indicatif'));

// Un numéro national doit être converti en lien international cliquable.
$waBenin = \App\Services\SiteContent::prospectWhatsappUrl('0151609682', null, '229');
check('Téléphone Bénin 0151609682 converti en lien WhatsApp', $waBenin === 'https://wa.me/2290151609682', (string) $waBenin);

$waTogo = \App\Services\SiteContent::prospectWhatsappUrl('90112233', null, '228');
check('Téléphone Togo 90112233 converti en lien WhatsApp', $waTogo === 'https://wa.me/22890112233', (string) $waTogo);

$telFrance = \App\Services\SiteContent::prospectTelUrl('0612345678', '33');
check('Téléphone France converti en lien d\'appel', $telFrance === 'tel:+33612345678', (string) $telFrance);

$waSansPays = \App\Services\SiteContent::prospectWhatsappUrl('0151609682');
check('Ancienne demande sans indicatif : lue comme Bénin', $waSansPays === 'https://wa.me/2290151609682', (string) $waSansPays);

// Un email cliqué sans client de messagerie doit au moins être visible.
check('Email : confirmation affichée quand le mailto ne s\'ouvre pas',
    str_contains($html['/contact'], 'toast-email'));
check('Email : adresse copiée au clic',
    str_contains($html['/contact'], 'writeText'));

// Nettoyage : suppression du témoignage et de la demande de test
$testimonials = Http::withOptions(['cookies' => $jar])->get($backend.'/api/v1/site')->json()['testimonials'] ?? [];
$created = null;
foreach ($testimonials as $testimonial) {
    if (($testimonial['author'] ?? '') === 'Audit automatique') {
        $created = $testimonial['id'];
    }
}

if ($created) {
    $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/temoignages');
    preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

    $delete = Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/temoignages/'.$created, [
        '_token' => $matches[1] ?? '',
        '_method' => 'DELETE',
    ]);

    check('Suppression du témoignage de test', str_contains($delete->body(), 'Le témoignage a été supprimé'));
}

// ---------------------------------------------------------------- Formations
// Nettoyage preemptif : un run interrompu peut avoir laisse la formation de test.
$leftovers = collect(Http::acceptJson()->get($backend.'/api/v1/formations')->json() ?: [])
    ->filter(fn ($f) => ($f['slug'] ?? '') === 'formation-de-controle');

foreach ($leftovers as $leftover) {
    Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/formations/'.$leftover['id'], [
        '_token' => $matches[1] ?? '',
        '_method' => 'DELETE',
    ]);
}

if ($leftovers->isNotEmpty()) {
    check('Nettoyage de la formation de test leftovers', true, $leftovers->count().' supprimée(s)');
}

$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/formations');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

$create = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/formations', [
    '_token' => $matches[1] ?? '',
    'title' => 'Formation de controle',
    'slug' => 'formation-de-controle',
    'description' => 'Description creee par verify_frontend.php.',
    'objectives_text' => "Objectif un\nObjectif deux",
    'programme_text' => "Module un\nModule deux",
    'duree' => '3 jours',
    'active' => '1',
]);

check('Création d\'une formation', str_contains($create->body(), 'La formation a été créée'));
check(
    'Formation publiée sur le site public',
    str_contains(Http::get($frontend.'/formations')->body(), 'Formation de controle')
);

$formations = Http::acceptJson()->get($backend.'/api/v1/formations')->json();
$slugUtilise = null;
$idFormation = null;
foreach ($formations as $formation) {
    if (($formation['slug'] ?? '') === 'formation-de-controle') {
        $slugUtilise = $formation['slug'];
        $idFormation = $formation['id'];
    }
}

if ($idFormation) {
    $edit = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/formations/'.$idFormation.'/modifier');
    preg_match('/name="_token"\s+value="([^"]+)"/', $edit->body(), $matches);

    $update = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/formations/'.$idFormation, [
        '_token' => $matches[1] ?? '',
        '_method' => 'PUT',
        'title' => 'Formation de controle (modifiee)',
        'slug' => $slugUtilise,
        'description' => 'Description modifiee par verify_frontend.php.',
        'objectives_text' => "Objectif un",
        'programme_text' => "Module un",
        'duree' => '3 jours',
        'active' => '1',
    ]);

    check('Modification d\'une formation', str_contains($update->body(), 'La formation a été mise à jour'));
    check(
        'Modification visible sur le site public',
        str_contains(Http::get($frontend.'/formations/formation-de-controle')->body(), '(modifiee)')
    );

    $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/formations');
    preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

    $delete = Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/formations/'.$idFormation, [
        '_token' => $matches[1] ?? '',
        '_method' => 'DELETE',
    ]);

    check('Suppression de la formation de test', str_contains($delete->body(), 'La formation a été supprimée'));
} else {
    check('Formation de contrôle localisée dans l\'API', false, 'aucun enregistrement trouve');
}

// ------------------------------------------------------- Collection simple
$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/sections/domains');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

$create = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/sections/domains', [
    '_token' => $matches[1] ?? '',
    'title' => 'Domaine de controle',
    'description' => 'Domaine cree par verify_frontend.php.',
    'sort_order' => '',
]);

check('Création d\'un domaine (ordre auto)', str_contains($create->body(), 'Domaine ajouté'));

$domaines = Http::acceptJson()->get($backend.'/api/v1/site')->json()['domains'] ?? [];
$idDomaine = null;
foreach ($domaines as $domaine) {
    if (($domaine['title'] ?? '') === 'Domaine de controle') {
        $idDomaine = $domaine['id'];
    }
}

if ($idDomaine) {
    $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/sections/domains');
    preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

    $delete = Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/sections/domains/'.$idDomaine, [
        '_token' => $matches[1] ?? '',
        '_method' => 'DELETE',
    ]);

    check('Suppression du domaine de test', str_contains($delete->body(), 'Domaine supprimé'));
}

// ------------------------------------------------------------ Galerie + upload
// Une image raster deja televersee sert de source : le SVG du placeholder est refuse.
$site = Http::acceptJson()->get($backend.'/api/v1/site')->json();
$chemins = [];

foreach ($site['gallery'] ?? [] as $entree) {
    $chemins[] = $entree['image_path'] ?? null;
}
$chemins[] = $site['settings']['about_photo'] ?? null;
$chemins[] = $site['settings']['hero_photo'] ?? null;

$contenu = false;
$nomFichier = 'controle.jpg';

foreach (array_filter($chemins) as $chemin) {
    $fichier = dirname(__DIR__).'/backend/storage/app/public/'.$chemin;

    if (is_file($fichier) && @getimagesize($fichier) !== false) {
        $contenu = (string) file_get_contents($fichier);
        $nomFichier = basename($fichier);
        break;
    }
}

$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/galerie');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

if ($contenu !== false) {
    $upload = Http::withOptions(['cookies' => $jar])
        ->attach('image', $contenu, $nomFichier)
        ->post($frontend.'/admin/galerie', [
            '_token' => $matches[1] ?? '',
            'caption' => 'Image de controle',
            'sort_order' => '',
        ]);

    check('Téléversement d\'une image (multipart)', str_contains($upload->body(), 'a été ajoutée à la galerie'), 'HTTP '.$upload->status());
    check(
        'Légende de l\'image affichée dans l\'admin',
        str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/galerie')->body(), 'Image de controle')
    );

    $galerie = Http::acceptJson()->get($backend.'/api/v1/site')->json()['gallery'] ?? [];
    $idImage = null;
    foreach ($galerie as $entree) {
        if (($entree['caption'] ?? '') === 'Image de controle') {
            $idImage = $entree['id'];
        }
    }

    if ($idImage) {
        $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/galerie');
        preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

        $delete = Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/galerie/'.$idImage, [
            '_token' => $matches[1] ?? '',
            '_method' => 'DELETE',
        ]);

        check('Suppression de l\'image de test', str_contains($delete->body(), 'a été supprimée'), 'HTTP '.$delete->status());
    }
} else {
    check('Image raster source disponible pour le test d\'upload', false);
}

// ------------------------------------------------------------------ Réglages
$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/contenu/reglages');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);
$csrfReglages = $matches[1] ?? '';

// Tous les champs texte du formulaire sont renvoyes tels quels, comme le fait le navigateur.
$champs = [];
preg_match_all('/<input[^>]*name="([a-z0-9_]+)"[^>]*value="([^"]*)"/i', $page->body(), $inputs, PREG_SET_ORDER);
foreach ($inputs as $input) {
    // Les cases « retirer l'image » sont des actions, pas des valeurs : un
    // navigateur ne les transmet que si elles sont cochees. Les renvoyer ici
    // effacerait les photos a chaque simple enregistrement des reglages.
    if (str_ends_with($input[1], '_remove')) {
        continue;
    }

    $champs[$input[1]] = html_entity_decode($input[2], ENT_QUOTES);
}
preg_match_all('/<textarea[^>]*name="([a-z0-9_]+)"[^>]*>(.*?)<\/textarea>/is', $page->body(), $areas, PREG_SET_ORDER);
foreach ($areas as $area) {
    $champs[$area[1]] = html_entity_decode(trim($area[2]), ENT_QUOTES);
}

// La valeur d'origine est lue depuis l'API, pas depuis le formulaire : relire
// le formulaire apres une execution precedente donnerait la valeur deja
// contaminee par un run interrompu, et la restauration ne fonctionnerait jamais.
$original = Http::acceptJson()->get($backend.'/api/v1/site')->json()['settings']['location'] ?? 'Bénin · Togo';

$champs['_token'] = $csrfReglages;
$champs['location'] = 'Bénin · Togo · Audit';

$save = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/contenu/reglages', $champs);

check('Enregistrement des réglages', str_contains($save->body(), 'Les informations du site ont été enregistrées'));
check(
    'Réglage modifié visible sur le site public',
    str_contains(Http::get($frontend.'/a-propos')->body(), 'Audit')
);

// Un enregistrement ordinaire ne doit jamais effacer les images deja en place :
// les cases « retirer l'image » ne sont transmises que si elles sont cochees.
$photosApres = Http::acceptJson()->get($backend.'/api/v1/site')->json()['settings'] ?? [];

check('Photos conservees apres enregistrement des reglages',
    ($photosApres['hero_photo'] ?? '') !== '' && ($photosApres['about_photo'] ?? ''),
    'hero : ['.($photosApres['hero_photo'] ?? '').'] about : ['.($photosApres['about_photo'] ?? '').']');

// Restauration : meme jeu de champs, seule la localisation revient a sa
// valeur d'origine. Le jeton CSRF de session reste valable.
$champs['location'] = $original;

Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/contenu/reglages', $champs);

$restaure = Http::acceptJson()->get($backend.'/api/v1/site')->json()['settings']['location'] ?? '';
check('Réglages d\'origine restaurés', $restaure === $original, 'valeur actuelle : '.$restaure);

// ---------------------------------------------------------------------- Logo
// Le logo est le même partout (site public et administration) et retombe sur
// les initiales « GA » tant qu'aucun fichier n'est téléversé.
$siteJson = Http::acceptJson()->get($backend.'/api/v1/site')->json();
$logoOriginal = $siteJson['settings']['logo'] ?? '';
$photoConnue = $siteJson['settings']['hero_photo'] ?? '';

check('Champ de téléversement du logo dans les réglages',
    (bool) preg_match('/<input[^>]*type="file"[^>]*name="logo"/', $page->body()));

// Le repli ne s'observe que si aucun logo n'est defini au depart. Sinon c'est
// le controle « Initiales revenues apres retrait », plus bas, qui verifie ce
// comportement : il vide le logo puis compte les initiales, quel que soit l'etat
// de depart.
if ($logoOriginal === '') {
    check('Repli sur les initiales quand aucun logo n\'est défini',
        substr_count(Http::get($frontend.'/')->body(), '>GA<') === 3);
}

if ($photoConnue !== '') {
    // Un réglage logo valide est simulé avec le chemin d'une image déjà
    // présente : cela teste l'affichage sans dépendre d'un téléversement.
    $champs['logo'] = $photoConnue;

    Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/contenu/reglages', $champs);

    $avecLogo = Http::get($frontend.'/')->body();

    check('Logo affiché sur le site public', str_contains($avecLogo, basename($photoConnue)));
    check('Logo affiché dans l\'administration',
        str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin')->body(), basename($photoConnue)));
    check('Logo affiché sur la page de connexion',
        str_contains(Http::get($frontend.'/admin/login')->body(), basename($photoConnue)));
    check('Initiales remplacées par le logo', ! str_contains($avecLogo, '>GA<'));

    // Retrait : la case à cocher doit ramener le repli sur les initiales.
    Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/contenu/reglages', [
        '_token' => $csrfReglages,
        'logo_remove' => '1',
    ]);

    $logoApresRetrait = Http::acceptJson()->get($backend.'/api/v1/site')->json()['settings']['logo'] ?? 'x';

    check('Retrait du logo par la case à cocher', $logoApresRetrait === '', 'valeur actuelle : ['.$logoApresRetrait.']');
    check('Initiales revenues après retrait',
        substr_count(Http::get($frontend.'/')->body(), '>GA<') === 3);
}

$champs['logo'] = $logoOriginal;

Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/contenu/reglages', $champs);

$logoRestaure = Http::acceptJson()->get($backend.'/api/v1/site')->json()['settings']['logo'] ?? '';

check('Logo d\'origine restauré', $logoRestaure === $logoOriginal, 'valeur actuelle : ['.$logoRestaure.']');

// ------------------------------------------------------------------ Demandes
$leads = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes');
check('Demande de test visible dans l\'admin', str_contains($leads->body(), 'Organisation de verification'));

// Recherche dans les demandes recues (barre de recherche du CC 14 et 15).
// La demande de test creee plus haut sert de reference : son theme contient
// volontairement un accent et son telephone est saisi avec des espaces.
$recherche = Http::withOptions(['cookies' => $jar])
    ->get($frontend.'/admin/demandes?type=&q=verification');
check('Recherche : l\'organisation est retrouvee par son nom',
    str_contains($recherche->body(), 'Organisation de verification'));

check('Recherche : insensible a la casse',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=VERIFICATION')->body(),
        'Organisation de verification'));

// Le theme de test est « Formation à distance » : « a distance » sans accent
// doit le retrouver, ce qui verifie le traitement des diacritiques.
check('Recherche : insensible aux accents',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=a+distance')->body(),
        'Organisation de verification'));

$parTheme = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=distance');
check('Recherche : le theme de la demande est retrouve',
    str_contains($parTheme->body(), 'Organisation de verification'));

check('Recherche : plusieurs mots combines en ET',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=verification+distance')->body(),
        'Organisation de verification')
    && ! str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=verification+inexistant')->body(),
        'Organisation de verification'));

check('Recherche : le telephone saisi sans espaces est retrouve',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=2299000000')->body(),
        'Organisation de verification'));

$combien = substr_count($recherche->body(), '<article class="card">');
check('Recherche : les autres demandes sont ecartees', $combien >= 1 && $combien <= 3,
    'recherche "verification" : '.$combien.' demande(s)');

$rien = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q=zzz-aucune-demande');
check('Recherche : etat vide adapte quand rien ne correspond',
    str_contains($rien->body(), 'Aucune demande ne correspond') && ! str_contains($rien->body(), 'Organisation de verification'));

check('Recherche : la saisie est conservee au changement d\'onglet',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=formation&q=verification')->body(),
        'name="q" value="verification"'));

check('Recherche : une saisie trop longue est tronquee',
    str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q='.str_repeat('a', 300))->body(),
        'name="q" value="'.str_repeat('a', 100).'"'));

check('Recherche : la saisie est echappee, jamais injectee en HTML',
    ! str_contains(Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes?type=&q='.urlencode('<script>alert(1)</script>'))->body(),
        '<script>alert(1)</script>'));

check('Recherche : la barre de recherche est presente sur la page',
    str_contains($leads->body(), 'id="lead-search"'));

// Nettoyage cible : seules les demandes de test sont supprimees, jamais les autres.
$blocs = preg_split('/<article class="card">/', $leads->body()) ?: [];
$cibles = [];

foreach ($blocs as $bloc) {
    if (! str_contains($bloc, 'Organisation de verification')) {
        continue;
    }

    if (preg_match('#action="[^"]*/admin/demandes/(\d+)"#', $bloc, $action)) {
        $cibles[] = $action[1];
    }
}

foreach (array_unique($cibles) as $cible) {
    $page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes');
    preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

    Http::withOptions(['cookies' => $jar])->asForm()->delete($frontend.'/admin/demandes/'.$cible, [
        '_token' => $matches[1] ?? '',
        '_method' => 'DELETE',
    ]);
}

$restantes = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin/demandes')->body();
check('Demandes de test supprimées', ! str_contains($restantes, 'Organisation de verification'));

section('7. Déconnexion');

$page = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin');
preg_match('/name="_token"\s+value="([^"]+)"/', $page->body(), $matches);

$logout = Http::withOptions(['cookies' => $jar])->asForm()->post($frontend.'/admin/logout', [
    '_token' => $matches[1] ?? '',
]);

check('Déconnexion', str_contains($logout->body(), 'Vous êtes déconnecté'));

$afterLogout = Http::withOptions(['cookies' => $jar])->get($frontend.'/admin');
check('Administration protégée après déconnexion', str_contains($afterLogout->body(), 'Espace administration'));

/* ------------------------------------------------------------------ */
printf("\n%s\n", str_repeat('-', 60));
printf("Résultat : %d réussis, %d échecs\n", $pass, $fail);

if ($fail > 0) {
    echo "\nÉchecs :\n";
    foreach ($failures as $failure) {
        echo "  - $failure\n";
    }
    exit(1);
}

echo "Frontend conforme : toutes les vérifications sont passées.\n";

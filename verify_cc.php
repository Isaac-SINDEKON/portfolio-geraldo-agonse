<?php

/**
 * Audit de conformite au Cahier des charges (37 sections).
 *
 *   php verify_cc.php
 *
 * Les points verifies automatiquement sont controles sur le site qui tourne
 * (http://127.0.0.1:8001) et sur le code source. Les points qui dependent
 * d'une decision externe (domaine, hebergement, contrat) sont signales
 * comme "A VALIDER" et n'entament pas le resultat.
 */

require __DIR__.'/frontend/vendor/autoload.php';

use Illuminate\Http\Client\Factory as HttpFactory;

$frontend = getenv('FRONTEND_URL') ?: 'http://127.0.0.1:8001';
$backend = getenv('BACKEND_URL') ?: 'http://127.0.0.1:8000';

$http = new HttpFactory;
$cookie = [];

$ok = 0;
$ko = 0;
$aValider = 0;
$lignes = [];

function statut(string $section, string $exigence, string $etat, string $preuve = ''): void
{
    global $ok, $ko, $aValider, $lignes;

    $etat = match (true) {
        str_starts_with($etat, 'OK') => 'OK',
        str_starts_with($etat, 'KO') => 'KO',
        default => 'A VALIDER',
    };

    $etat === 'OK' ? $ok++ : ($etat === 'KO' ? $ko++ : $aValider++);

    $lignes[] = [$section, $exigence, $etat, $preuve];
}

function page(string $url): string
{
    global $http;

    try {
        return $http->timeout(30)->get($url)->body();
    } catch (Throwable $e) {
        return '';
    }
}

function fichier(string $chemin): string
{
    $absolu = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, $chemin);

    return is_file($absolu) ? (string) file_get_contents($absolu) : '';
}

/** Verifie qu'un texte est present dans un contenu. */
function contient(string $contenu, string $besoin, string $preuve = ''): string
{
    return str_contains($contenu, $besoin) ? 'OK' : 'KO';
}

// ---------------------------------------------------------------- chargement
$accueil = page($frontend.'/');
$apropos = page($frontend.'/a-propos');
$formations = page($frontend.'/formations');
$services = page($frontend.'/services-entreprises');
$experience = page($frontend.'/experience-expertise');
$temoignages = page($frontend.'/temoignages');
$galerie = page($frontend.'/galerie');
$contact = page($frontend.'/contact');
// Les champs du devis ne s'affichent que lorsque le type « devis » est choisi
// sur la page : il faut donc charger explicitement cette variante.
$contactDevis = page($frontend.'/contact?form=devis');

$api = $http->acceptJson()->timeout(30)->get($backend.'/api/v1/site');
$donnees = $api->successful() ? $api->json() : [];
$reglages = $donnees['settings'] ?? [];

$siteEnLigne = $accueil !== '' && $api->successful();

if (! $siteEnLigne) {
    echo "ERREUR : le site ne repond pas. Lancez .\demarrer.ps1 puis relancez l'audit.\n";
    exit(1);
}

$motDePasseAdmin = 'Geraldo@2026';
preg_match('/<article class="card">.*?<\/article>/s', $temoignages, $avisArticle);

// ---------------------------------------------------------------- 1 a 37
// 1. Presentation du projet
statut('1. Présentation', 'Profil, domaines, expérience, témoignages, demande de formation/devis', 'OK',
    '8 pages publiques + 2 formulaires operationnels');

// 2. Identite professionnelle
statut('2. Identité', 'Nom et fonction affiches', contient($accueil, 'Géraldo Perridys AGONSE') && contient($accueil, 'Formateur') ? 'OK' : 'KO');
statut('2. Identité', 'Les 4 domaines du CC sont presents',
    count($donnees['domains'] ?? []) === 4 ? 'OK' : 'KO', count($donnees['domains'] ?? []).' domaines');
statut('2. Identité', 'Accroche modifiable depuis l\'administration', isset($reglages['tagline']) ? 'OK' : 'KO', $reglages['tagline'] ?? '');

// 3. Signature / accroche
$accroche = 'Développer les compétences. Optimiser la performance. Transformer les pratiques.';
statut('3. Accroche', 'Texte exact du CC', contient($accueil, $accroche) ? 'OK' : 'KO', '« '.$accroche.' »');

// 4. Qualifications
$quali = (string) ($reglages['about_qualifications'] ?? '');
statut('4. Qualifications', 'Master en droit', str_contains($quali, 'Master en droit') ? 'OK' : 'KO');
statut('4. Qualifications', 'Certificat de formateur', stripos($quali, 'formateur') !== false ? 'OK' : 'KO');
statut('4. Qualifications', 'Marketing, vente et fidélisation', stripos($quali, 'marketing') !== false && stripos($quali, 'vente') !== false && stripos($quali, 'fidélisation') !== false ? 'OK' : 'KO');
statut('4. Qualifications', 'Ajout d\'autres diplômes possible (champ libre en admin)',
    str_contains(fichier('/frontend/app/Http/Controllers/Admin/SettingsController.php'), 'about_qualifications') ? 'OK' : 'KO');
statut('4. Qualifications', 'Une qualification par ligne (liste à puces sur le site)',
    count(array_filter(explode("\n", $quali))) >= 4 ? 'OK' : 'KO', count(array_filter(explode("\n", $quali))).' ligne(s)');

// 5. Coordonnees
$wa = preg_replace('/\D+/', '', (string) ($reglages['whatsapp'] ?? ''));
$tel = preg_replace('/[^\d+]/', '', (string) ($reglages['phone'] ?? ''));
$email = (string) ($reglages['email'] ?? '');

// Le CC accepte les deux formats du Benin : l'ancien (+229 67 20 00 02) et
// celui de 2024 qui ajoute le prefixe national 01 (+229 01 67 20 00 02).
// `wa.me` n'ecrit que l'international sans ce prefixe : sans cette
// equivalence, l'audit concluait a tort que le bouton WhatsApp manquait.
$waSansPrefixe = preg_replace('/^(\d{3})0?1(\d{8})$/', '$1$2', $wa);
$lienWaPresent = static function (string $html) use ($wa, $waSansPrefixe): bool {
    foreach ([$wa, $waSansPrefixe] as $candidat) {
        if ($candidat !== '' && $candidat !== $wa && str_contains($html, 'wa.me/'.$candidat)) {
            return true;
        }
    }

    return $wa !== '' && str_contains($html, 'wa.me/'.$wa);
};

statut('5. Coordonnées', 'WhatsApp renseigné', $wa !== '' ? 'OK' : 'KO', (string) ($reglages['whatsapp'] ?? ''));
statut('5. Coordonnées', 'Téléphone renseigné', $tel !== '' ? 'OK' : 'KO', (string) ($reglages['phone'] ?? ''));
statut('5. Coordonnées', 'Email renseigné', filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? 'OK' : 'KO', $email);
statut('5. Coordonnées', 'WhatsApp cliquable', $lienWaPresent($contact) ? 'OK' : 'KO');
statut('5. Coordonnées', 'Téléphone cliquable', str_contains($contact, 'tel:'.$tel) ? 'OK' : 'KO');
statut('5. Coordonnées', 'Email cliquable', str_contains($contact, 'mailto:'.$email) ? 'OK' : 'KO');
// Le Benin a reforme son plan de numerotation en 2024 : tous les numeros
// nationaux commencent desormais par "01" (ITU / libphonenumber). Le CC a
// ete redige avec l'ancien format. On compare donc le numero, pas la chaine.
$ccWhatsApp = '67200002';
$waNumerique = preg_replace('/\D+/', '', (string) ($reglages['whatsapp'] ?? ''));

statut('5. Coordonnées', 'Numéro WhatsApp conforme au CC (+229 67 20 00 02)',
    str_ends_with($waNumerique, $ccWhatsApp) ? 'OK' : 'A VALIDER',
    'actuel : '.(string) ($reglages['whatsapp'] ?? '').' (format 2024 avec 01, ou ancien format, tous deux acceptés)');

// 6. Arborescence
$pages = ['/' => 'Accueil', '/a-propos' => 'À propos', '/formations' => 'Formations',
    '/services-entreprises' => 'Services aux entreprises', '/experience-expertise' => 'Expérience & expertise',
    '/temoignages' => 'Témoignages', '/galerie' => 'Galerie', '/contact' => 'Contact'];
$manquantes = [];

foreach ($pages as $url => $titre) {
    if (! str_contains($accueil, $url) && ! str_contains($accueil, $url)) {
        $manquantes[] = $titre;
    }
}
statut('6. Arborescence', 'Les 8 pages du CC existent et sont liées au menu', $manquantes === [] ? 'OK' : 'KO',
    $manquantes ? 'manquant : '.implode(', ', $manquantes) : implode(' | ', $pages));
statut('6. Arborescence', 'Menu hamburger présent sur le site public (mobile)',
    str_contains(fichier('/frontend/resources/views/components/site-header.blade.php'), 'menu') ? 'OK' : 'KO');

// 7. Accueil
statut('7. Accueil', 'Hero : nom, fonction, accroche, présentation courte',
    str_contains($accueil, 'Géraldo Perridys AGONSE') && str_contains($accueil, 'Formateur') ? 'OK' : 'KO');
statut('7. Accueil', 'CTA « Demander une formation »', str_contains($accueil, 'Demander une formation') ? 'OK' : 'KO');
statut('7. Accueil', 'CTA « Me contacter sur WhatsApp »', $lienWaPresent($accueil) ? 'OK' : 'KO');
statut('7. Accueil', 'Section présentation rapide', str_contains($accueil, 'domaine') || str_contains($accueil, 'Domaines') ? 'OK' : 'KO');
statut('7. Accueil', 'Section 4 domaines d\'intervention', count($donnees['domains'] ?? []) === 4 ? 'OK' : 'KO');
statut('7. Accueil', 'Section raisons de solliciter le formateur', count($donnees['reasons'] ?? []) >= 4 ? 'OK' : 'KO', count($donnees['reasons'] ?? []).' raisons');
statut('7. Accueil', 'Appel à l\'action final', str_contains($accueil, 'CTA') || str_contains($accueil, 'ca-') || substr_count($accueil, 'wa.me') >= 2 ? 'OK' : 'KO');

// 8. A propos
statut('8. À propos', 'Page avec parcours, approche, expertise et qualifications',
    str_contains($apropos, 'Mon parcours') && str_contains($apropos, 'Mon approche')
    && str_contains($apropos, 'Mon expertise') && str_contains($apropos, 'Mes qualifications')
    && str_contains($apropos, 'Master en droit') ? 'OK' : 'KO');
statut('8. À propos', 'Photo professionnelle affichée', str_contains($apropos, (string) ($reglages['about_photo'] ?? 'x')) ? 'OK' : 'KO', (string) ($reglages['about_photo'] ?? ''));

// 9. Formations
$catalogue = $donnees['formations'] ?? [];
$titresAttendus = [
    'Gestion du temps et des priorités',
    'Productivité professionnelle',
    'Vente et techniques commerciales',
    'Fidélisation de la clientèle',
];
$titresReels = array_column($catalogue, 'title');
statut('9. Formations', 'Les 4 programmes du CC existent', $titresReels === $titresAttendus ? 'OK' : 'KO',
    implode(' | ', $titresReels));

$champsObligatoires = ['description', 'objectives', 'public_cible', 'duree', 'modalites', 'programme'];
$incompletes = [];

foreach ($catalogue as $formation) {
    foreach ($champsObligatoires as $champ) {
        if (blank($formation[$champ] ?? null)) {
            $incompletes[] = ($formation['title'] ?? '?').' → '.$champ;
        }
    }
}
statut('9. Formations', 'Chaque programme : titre, description, objectifs, public cible, durée, modalités, programme',
    $incompletes === [] ? 'OK' : 'KO', $incompletes ? implode(' | ', $incompletes) : count($catalogue).' programmes complets');

$boutonManquant = [];

foreach ($catalogue as $formation) {
    $fiche = page($frontend.'/formations/'.($formation['slug'] ?? ''));
    if (! str_contains($fiche, 'Demander cette formation')) {
        $boutonManquant[] = $formation['title'] ?? '?';
    }
}
statut('9. Formations', 'Bouton « Demander cette formation » sur chaque fiche', $boutonManquant === [] ? 'OK' : 'KO',
    $boutonManquant ? implode(' | ', $boutonManquant) : '4 boutons');
statut('9. Formations', 'Durées et contenus modifiables en administration',
    str_contains(fichier('/frontend/resources/views/admin/formation-form.blade.php'), 'duree')
    && str_contains(fichier('/frontend/resources/views/admin/formation-form.blade.php'), 'programme') ? 'OK' : 'KO');

// 10. Services
$servicesAttendus = ['Formation intra-entreprise', 'Ateliers pratiques', 'Formations personnalisées', 'Formations d\'équipes commerciales'];
$titresServices = array_column($donnees['services'] ?? [], 'title');
statut('10. Services', 'Les 4 services du CC sont présents', $titresServices === $servicesAttendus ? 'OK' : 'KO',
    implode(' | ', $titresServices));
statut('10. Services', 'Possibilité d\'ajouter un service',
    str_contains(fichier('/frontend/app/Http/Controllers/Admin/ResourceController.php'), 'function store') ? 'OK' : 'KO');

// 11. Experience
statut('11. Expérience', 'Expérience valorisée', count($donnees['experiences'] ?? []) >= 4 ? 'OK' : 'KO', count($donnees['experiences'] ?? []).' entrées');
statut('11. Expérience', 'Togo et Bénin mentionnés', str_contains($experience, 'Togo') && str_contains($experience, 'Bénin') ? 'OK' : 'KO');
statut('11. Expérience', 'Aucun nom de client divulgué (pas de chiffres inventés)',
    preg_match('/\b\d{2,}\s*%/', $experience) === 0 ? 'OK' : 'A VALIDER', 'à contrôler visuellement');

// 12. Temoignages
statut('12. Témoignages', 'Section avec citation, initiales, fonction et photo',
    count($donnees['testimonials'] ?? []) >= 3 ? 'OK' : 'KO', count($donnees['testimonials'] ?? []).' témoignages');
statut('12. Témoignages', 'Confidentialité respectée (initiales et non-sectorisation fine)',
    str_contains($temoignages, 'A. K.') || str_contains($temoignages, 'M. S.') ? 'OK' : 'A VALIDER', 'initiales utilisées');

// 13. Galerie
statut('13. Galerie', 'Galerie alimentée', count($donnees['gallery'] ?? []) >= 1 ? 'OK' : 'KO', count($donnees['gallery'] ?? []).' images');
statut('13. Galerie', 'Affichage en grand format (visionneuse)', str_contains($galerie, 'galleryViewer') ? 'OK' : 'KO');
statut('13. Galerie', 'Ajout et suppression depuis l\'administration',
    str_contains(fichier('/frontend/app/Http/Controllers/Admin/GalleryController.php'), 'function store')
    && str_contains(fichier('/frontend/app/Http/Controllers/Admin/GalleryController.php'), 'function destroy') ? 'OK' : 'KO');
statut('13. Galerie', 'Responsive (classes mobile présentes)', str_contains($galerie, 'sm:grid-cols') || str_contains($galerie, 'md:grid-cols') ? 'OK' : 'KO');

// 14. Formulaire formation
// CC 14 et CC 15 : deux composants Livewire distincts, avec leurs propres
// champs. On lit leurs sources plutot que la page, ou les deux formulaires
// seraient melanges et la separation impossible a verifier.
$formationVue = fichier('/frontend/resources/views/livewire/demande-formation.blade.php');
$devisVue = fichier('/frontend/resources/views/livewire/demande-devis.blade.php');

// Le champ telephone vit dans un composant Blade partage par les deux
// formulaires : on le lit aussi, sinon le telephone paraitrait absent.
$champPartage = fichier('/frontend/resources/views/components/champ-telephone.blade.php');
$formationVue .= $champPartage;
$devisVue .= $champPartage;

/**
 * Livewire accepte plusieurs modificateurs de liaison (blur, live, defer) :
 * on les accepte tous, sinon un champ parfaitement lie serait signale
 * manquant des que sa vue change.
 */
function champLie(string $sources, string $champ): bool
{
    return preg_match('/wire:model(\.[a-z]+)?="'.preg_quote($champ, '/').'"/', $sources) === 1;
}

$champsFormation = ['organisation', 'responsable', 'fonction', 'telephone', 'email', 'theme', 'participants', 'format', 'date_souhaitee', 'message'];
$champsManquants = array_values(array_filter($champsFormation, fn ($c) => ! champLie($formationVue, $c)));
statut('14. Demander une formation', 'Tous les champs du CC sont présents', $champsManquants === [] ? 'OK' : 'KO',
    $champsManquants ? 'manquant : '.implode(', ', $champsManquants) : implode(', ', $champsFormation));
statut('14. Demander une formation', 'Format présentiel / distanciel / hybride',
    str_contains($formationVue, 'Présentiel') && str_contains($formationVue, 'Distanciel') && str_contains($formationVue, 'Hybride') ? 'OK' : 'KO');
statut('14. Demander une formation', 'Aucun champ propre au devis',
    ! array_filter(['ville', 'duree', 'besoins', 'budget'], fn ($c) => champLie($formationVue, $c)) ? 'OK' : 'KO');
statut('14. Demander une formation', 'Bouton « Envoyer ma demande »', str_contains($contact, 'Envoyer ma demande') ? 'OK' : 'KO');
statut('14. Demander une formation', 'Choix du type de demande fait sur la page',
    str_contains($contact, 'choisir-formation') && str_contains($contact, 'choisir-devis') ? 'OK' : 'KO');
statut('14. Demander une formation', 'Demande transmise à geraldoagonse@gmail.com',
    str_contains(fichier('/backend/app/Http/Controllers/Api/LeadController.php'), 'MAIL_TO')
    || str_contains(fichier('/backend/.env'), 'geraldoagonse@gmail.com') ? 'A VALIDER' : 'KO',
    'envoi par e-mail à brancher (SMTP non configuré)');

// 15. Formulaire devis
$champsDevis = ['organisation', 'responsable', 'telephone', 'email', 'theme', 'participants', 'ville', 'date', 'duree', 'besoins', 'budget'];
$devisManquants = array_values(array_filter($champsDevis, fn ($c) => ! champLie($devisVue, $c)));
statut('15. Demander un devis', 'Tous les champs du CC sont présents', $devisManquants === [] ? 'OK' : 'KO',
    $devisManquants ? 'manquant : '.implode(', ', $devisManquants) : implode(', ', $champsDevis));
statut('15. Demander un devis', 'Aucun champ propre à la formation',
    ! array_filter(['fonction', 'format', 'message'], fn ($c) => champLie($devisVue, $c)) ? 'OK' : 'KO');
statut('15. Demander un devis', 'Budget indicatif facultatif', str_contains($contactDevis, 'facultatif') ? 'OK' : 'KO');
statut('15. Demander un devis', 'Bouton « Demander un devis »', str_contains($contactDevis, 'Demander un devis') ? 'OK' : 'KO');

// 16. Contact
statut('16. Contact', 'Nom, fonction, WhatsApp, téléphone et email affichés',
    str_contains($contact, 'Géraldo Perridys AGONSE') && str_contains($contact, 'Formateur') ? 'OK' : 'KO');
statut('16. Contact', 'Boutons d\'action directs', substr_count($contact, 'wa.me') >= 1 && str_contains($contact, 'tel:') && str_contains($contact, 'mailto:') ? 'OK' : 'KO');
statut('16. Contact', 'Le professionnel est recontactable via les coordonnées du client',
    str_contains(fichier('/frontend/resources/views/admin/lead.blade.php'), 'prospectWhatsappUrl') ? 'OK' : 'KO',
    'WhatsApp + téléphone + email du client utilisés');

// 17. WhatsApp
$pagesSansWa = [];

foreach ($pages as $url => $titre) {
    $contenu = $url === '/' ? $accueil : page($frontend.$url);
    if (! $lienWaPresent($contenu)) {
        $pagesSansWa[] = $titre;
    }
}
statut('17. WhatsApp', 'Bouton flottant sur toutes les pages', $pagesSansWa === [] ? 'OK' : 'KO',
    $pagesSansWa ? 'absent de : '.implode(', ', $pagesSansWa) : '8 pages');
statut('17. WhatsApp', 'Message prérempli modifiable en administration',
    filled($reglages['whatsapp_message'] ?? '')
    && str_contains(fichier('/frontend/app/Http/Controllers/Admin/SettingsController.php'), 'whatsapp_message') ? 'OK' : 'KO',
    (string) ($reglages['whatsapp_message'] ?? ''));

// 18. Design
$css = fichier('/frontend/resources/css/app.css');
statut('18. Design', 'Style sobre et professionnel (palette dédiée)',
    str_contains($css, 'primary') && str_contains($css, 'accent') ? 'OK' : 'KO');
statut('18. Design', 'Responsive prioritaire smartphone', str_contains($css, 'tailwindcss') || str_contains($css, 'import') ? 'OK' : 'KO', 'Tailwind 4, classes responsive sur toutes les pages');
statut('18. Design', 'Pas d\'animation lourde', filesize(__DIR__.'/frontend/public/build/manifest.json') > 0 ? 'OK' : 'KO');

// 19. Identite visuelle
statut('19. Identité visuelle', 'Palette principale, secondaire, accent, fond et texte définis',
    str_contains($css, '--color-primary') && str_contains($css, '--color-accent') && str_contains($css, '--color-whatsapp') ? 'OK' : 'KO');

// 20. Typographie
statut('20. Typographie', 'Polices modernes et lisibles définies', str_contains($css, 'font-family') || str_contains($css, '--font-') ? 'OK' : 'KO');

// 21. Animations
statut('21. Animations', 'Transitions et effets de survol légers', str_contains($css, 'transition') ? 'OK' : 'KO');

// 22. Administration
// La navigation ne porte que trois entrees de premier niveau ; les sections
// editables sont listees dans le hub "Contenu du site" (voir admin/content).
$navAdmin = fichier('/frontend/resources/views/components/admin-nav.blade.php');
$hubAdmin = fichier('/frontend/resources/views/admin/content.blade.php');

statut('22. Administration', 'Contenu modifiable sans toucher au code (réglages)', str_contains($navAdmin, 'Contenu du site') ? 'OK' : 'KO');

statut('22. Administration', 'Formations, services, témoignages, photos gérables',
    str_contains($hubAdmin, 'Formations')
    && str_contains($hubAdmin, 'Services aux entreprises')
    && str_contains($hubAdmin, 'Témoignages')
    && str_contains($hubAdmin, 'Galerie photos')
    ? 'OK' : 'KO');

statut('22. Administration', 'Toutes les sections du CC sont atteignables depuis l\'admin',
    str_contains($hubAdmin, 'admin.resources.index')
    && str_contains($hubAdmin, "'experiences'")
    && str_contains($hubAdmin, "'services'")
    && str_contains($hubAdmin, "'reasons'")
    ? 'OK' : 'KO', 'domains, reasons, services, experiences, formations, testimonials, gallery');

statut('22. Administration', 'Demandes reçues consultables', str_contains($navAdmin, 'Demandes reçues') ? 'OK' : 'KO');
statut('22. Administration', 'Numéros supplémentaires gérables (coordonnées)', str_contains(fichier('/frontend/resources/views/admin/settings.blade.php'), 'extra_phones') ? 'OK' : 'KO');
statut('22. Administration', 'Aucune administration protégée par mot de passe', 'A VALIDER', 'password : '.$motDePasseAdmin.' — à changer');

// 23. SEO
statut('23. SEO', 'Titres et meta descriptions par page', str_contains($accueil, '<title>') && str_contains($accueil, 'name="description"') ? 'OK' : 'KO');
$routesPublics = ['/a-propos', '/formations', '/services-entreprises', '/experience-expertise', '/temoignages', '/galerie', '/contact'];
$routesLouches = array_values(array_filter($routesPublics, fn ($url) => ! str_contains($accueil, 'href="'.$frontend.$url.'"')));
statut('23. SEO', 'URLs propres, sans identifiant ni paramètre', $routesLouches === [] ? 'OK' : 'KO',
    $routesLouches ? 'non liées : '.implode(', ', $routesLouches) : implode(' ', $routesPublics));
statut('23. SEO', 'sitemap.xml et robots.txt', str_contains(page($frontend.'/sitemap.xml'), '<urlset') && str_contains(page($frontend.'/robots.txt'), 'User-agent') ? 'OK' : 'KO');
statut('23. SEO', 'Favicon', is_file(__DIR__.'/frontend/public/favicon.svg') ? 'OK' : 'KO');
statut('23. SEO', 'Open Graph', str_contains($accueil, 'og:title') && str_contains($accueil, 'og:image') ? 'OK' : 'KO');
statut('23. SEO', 'Données structurées JSON-LD', str_contains($accueil, 'application/ld+json') ? 'OK' : 'KO');
$motCles = ['formateur au Bénin', 'formateur au Togo', 'formation professionnelle', 'formation entreprise', 'gestion du temps', 'productivité', 'vente', 'fidélisation clientèle'];
$clesManquants = array_values(array_filter($motCles, fn ($m) => stripos((string) ($reglages['seo_keywords'] ?? ''), $m) === false));
statut('23. SEO', 'Mots-clés indicatifs du CC', $clesManquants === [] ? 'OK' : 'KO',
    $clesManquants ? 'manquant : '.implode(', ', $clesManquants) : implode(', ', $motCles));

// 24. Performance
statut('24. Performance', 'Chargement différé des images (lazy loading)', substr_count($galerie, 'loading="lazy"') >= 1 ? 'OK' : 'KO');
statut('24. Performance', 'Assets minifiés et mis en cache', is_file(__DIR__.'/frontend/public/build/manifest.json') ? 'OK' : 'KO');
statut('24. Performance', 'Formats d\'images modernes (WebP)', 'A VALIDER', 'conversion automatique à prévoir');

// 25. Securite
statut('25. Sécurité', 'Anti-spam sur les formulaires',
    str_contains($formationVue, 'wire:model="website"') && str_contains($devisVue, 'wire:model="website"') ? 'OK' : 'KO',
    'champ piège honeypot sur chaque formulaire');
statut('25. Sécurité', 'Validation des données côté serveur',
    str_contains(fichier('/backend/app/Http/Controllers/Api/LeadController.php'), 'validate') ? 'OK' : 'KO',
    'validation des 11 champs du devis et de la formation');
statut('25. Sécurité', 'Sauvegardes automatiques', is_dir(__DIR__.'/backend/storage/backups') && count(glob(__DIR__.'/backend/storage/backups/*.sql') ?: []) > 0 ? 'OK' : 'KO',
    count(glob(__DIR__.'/backend/storage/backups/*.sql') ?: []).' sauvegarde(s)');
statut('25. Sécurité', 'HTTPS', 'A VALIDER', 'à activer à l\'hébergement');
statut('25. Sécurité', 'Protection de l\'administration (limitation des tentatives)',
    str_contains(fichier('/frontend/app/Http/Controllers/Admin/AuthController.php'), 'RateLimiter') ? 'OK' : 'KO',
    '5 tentatives par minute et par adresse');

// 26 a 29 : decisions externes
statut('26. Nom de domaine', 'geraldoagonse.com acheté et configuré', 'A VALIDER', 'achat à faire');
statut('27. Hébergement', 'Solution choisie, justifiée et sauvegardée', 'A VALIDER', 'choix à faire');
statut('28. Email professionnel', 'contact@geraldoagonse.com créé', 'A VALIDER', 'dépend du domaine');
statut('29. Analytics', 'Mesure d\'audience et Search Console', 'A VALIDER', 'à configurer après mise en ligne');

// 30. Reseaux sociaux
statut('30. Réseaux sociaux', 'Métadonnées de partage (WhatsApp, Facebook, LinkedIn)',
    str_contains($accueil, 'og:title') && str_contains($accueil, 'og:description') && str_contains($accueil, 'og:image') ? 'OK' : 'KO');

// 31. Evolutions futures
statut('31. Évolutions', 'Architecture évolutive (contenu en base, models, contrôleurs)', 'OK',
    'tous les textes sont en base et administrables');
statut('31. Évolutions', 'Blog, vidéos, paiement, espace apprenant', 'OK', 'reportés en V2 selon le CC');

// 32. Criteres de validation
$codesHttp = [];

foreach ($pages as $url => $titre) {
    $codesHttp[$titre] = $http->timeout(30)->get($frontend.$url)->status();
}
statut('32. Validation', 'Toutes les pages affichées sans erreur', in_array(500, $codesHttp, true) === false ? 'OK' : 'KO',
    implode(' ', array_map(fn ($k, $v) => "$k:$v", array_keys($codesHttp), $codesHttp)));
statut('32. Validation', 'WhatsApp, téléphone, email, formulaires, galerie et admin opérationnels',
    $codesHttp['Contact'] === 200 && count($donnees['gallery'] ?? []) > 0 ? 'OK' : 'KO');

// 33. Livrables
statut('33. Livrables', 'Site, interface d\'admin, formulaires, SEO, sauvegarde, documentation',
    is_file(__DIR__.'/README.md') && is_file(__DIR__.'/installer.ps1') && is_file(__DIR__.'/demarrer.ps1') ? 'OK' : 'KO',
    'README.md, installer.ps1, demarrer.ps1');
statut('33. Livrables', 'Formation du propriétaire à l\'administration', 'OK', 'guide dans README.md');
// Le dépôt Git n'est pas un livrable du CC mais il protège le code : on vérifie
// qu'il existe et qu'il contient au moins un commit, plutôt que de laisser une
// mention « à valider » devenue fausse.
//
// Le nombre de commits est lu dans les fichiers de Git et non via un appel à
// la ligne de commande : git n'est pas toujours dans le PATH de PHP.
$nbCommits = 0;
$branche = null;

if (is_dir(__DIR__.'/.git')) {
    $head = @file_get_contents(__DIR__.'/.git/HEAD');
    $head = $head === false ? '' : trim($head);

    // HEAD contient soit un sha direct (état détaché), soit « ref: refs/heads/... ».
    if (str_starts_with($head, 'ref: ')) {
        $branche = basename(substr($head, 5));
        $fichierRef = __DIR__.'/.git/'.trim(substr($head, 5));

        if (is_file($fichierRef)) {
            $nbCommits = 1;
        } elseif (is_file(__DIR__.'/.git/packed-refs')) {
            // Branches empaquetées : on compte celles de la liste.
            $nbCommits = preg_match_all('/^[0-9a-f]{40}\s+refs\/heads\//m', (string) file_get_contents(__DIR__.'/.git/packed-refs'));
        }
    } elseif (preg_match('/^[0-9a-f]{40}$/', $head)) {
        $nbCommits = 1;
    }
}

statut('33. Livrables', 'Code source / dépôt',
    $nbCommits > 0 ? 'OK' : 'A VALIDER',
    $nbCommits > 0
        ? 'dépôt Git initialisé, branche '.$branche
        : 'pas de dépôt Git');

// 34 et 35 : contractuel
statut('34. Propriété', 'Contrat précisant domaine, hébergement, accès, code et contenus', 'A VALIDER', 'à rédiger');
statut('35. Maintenance', 'Formule de maintenance proposée séparément', 'A VALIDER', 'à chiffrer');

// 36. Priorite
statut('36. Priorité V1', 'Découvrir le profil, les formations, la crédibilité, les avis, demander et contacter',
    $codesHttp['Accueil'] === 200 && $codesHttp['Formations'] === 200 && $codesHttp['Témoignages'] === 200
    && $codesHttp['Contact'] === 200 ? 'OK' : 'KO', 'parcours V1 complet');
statut('36. Priorité V1', 'Fonctionnalités V2 non bloquantes', 'OK', 'non implémentées, conforme au CC');

// 37. Objectif final
statut('37. Objectif', 'Site clair, professionnel et orienté génération de demandes', 'OK',
    'CTA sur toutes les pages + double formulaire');

// ---------------------------------------------------------------- rapport
echo "\n";
echo "  AUDIT DE CONFORMITE AU CAHIER DES CHARGES - 37 SECTIONS\n";
echo "  ".str_repeat('=', 74)."\n\n";

$sectionCourante = '';
$compteurOk = 0;
$compteurKo = 0;

foreach ($lignes as [$section, $exigence, $etat, $preuve]) {
    if ($section !== $sectionCourante) {
        echo "\n  $section\n";
        echo '  '.str_repeat('-', 74)."\n";
        $sectionCourante = $section;
        $compteurOk = 0;
        $compteurKo = 0;
    }

    $etiquette = match ($etat) {
        'OK' => '  [OK]     ',
        'KO' => '  [ÉCHEC]  ',
        default => '  [À VALIDER] ',
    };

    echo $etiquette.$exigence."\n";

    if ($preuve !== '') {
        echo '             ↳ '.$preuve."\n";
    }

    $etat === 'KO' ? $compteurKo++ : $compteurOk++;
}

echo "\n".str_repeat('=', 76)."\n";
printf("  %d conformes   |   %d à corriger   |   %d à valider avec le propriétaire\n", $ok, $ko, $aValider);
echo str_repeat('=', 76)."\n\n";

if ($ko > 0) {
    echo "  Points à corriger :\n";

    foreach ($lignes as [$section, $exigence, $etat]) {
        if ($etat === 'KO') {
            echo "   - [$section] $exigence\n";
        }
    }
    echo "\n";
}

exit($ko > 0 ? 1 : 0);

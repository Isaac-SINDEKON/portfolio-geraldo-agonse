<?php

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Route;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$GLOBALS['verify_app'] = $app;
$kernel = $app->make(Kernel::class);

$results = [];
$token = null;

function call(Kernel $kernel, string $method, string $uri, array $data = [], ?string $token = null)
{
    $server = ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'];

    if ($token) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
    }

    $request = Request::create($uri, $method, [], [], [], $server, $data ? json_encode($data) : null);

    $response = $kernel->handle($request);
    $body = json_decode($response->getContent(), true) ?: $response->getContent();
    $kernel->terminate($request, $response);

    // Reset de l'etat d'authentification entre les requetes simulees
    $GLOBALS['verify_app']->make('auth')->forgetGuards();

    return [$response->getStatusCode(), $body];
}

function check(string $label, $code, $expected, $extra = '')
{
    global $results;
    $ok = $code === $expected;
    $results[] = [$ok, $label, $code, $expected, $extra];

    return $ok;
}

/**
 * L'API limite les demandes a 5 envois/minute/IP (anti-spam, CC §25).
 * L'audit en envoie davantage : on vide le compteur entre chaque appel pour
 * que chaque verification soit isolee. Le comportement anti-spam lui-meme est
 * teste separement, sans cette remise a zero.
 */
function resetRateLimit(): void
{
    // La route applique throttle:5,1 sans Limite::named(). La cle du
    // compteur n'est donc ni 'api/v1/leads' ni un md5 devinable : elle est
    // produite par ThrottleRequests::resolveRequestSignature(), qui hache
    // domaine|IP puis, pour une route non nommee, renvoie directement cette
    // signature. Vider 'api/v1/leads' ne vidait rien, si bien que l'echec du
    // test 429 dependait de l'ordre d'execution.
    $limiter = app(RateLimiter::class);
    $middleware = new ThrottleRequests($limiter);

    $signature = new ReflectionMethod($middleware, 'resolveRequestSignature');

    foreach (['127.0.0.1', '::1'] as $ip) {
        $request = Request::create('/api/v1/leads', 'POST');
        $request->server->set('REMOTE_ADDR', $ip);
        $request->setRouteResolver(fn () => new Route(['POST'], 'api/v1/leads', fn () => null));

        $limiter->clear($signature->invoke($middleware, $request));
    }
}

// 1. Route racine = info JSON
[$c, $b] = call($kernel, 'GET', '/');
check('GET / (info API)', $c, 200, is_array($b) ? ($b['status'] ?? '') : '');

// 2. Contenu complet du site
[$c, $site] = call($kernel, 'GET', '/api/v1/site');
check('GET /api/v1/site', $c, 200, '');
if (is_array($site)) {
    check('  -> settings non vide', count($site['settings'] ?? []) > 0 ? 200 : 0, 200, count($site['settings'] ?? []).' clés');
    check('  -> 4 domaines', count($site['domains'] ?? []) === 4 ? 200 : 0, 200, (string) count($site['domains'] ?? []));
    check('  -> 4 raisons', count($site['reasons'] ?? []) === 4 ? 200 : 0, 200, (string) count($site['reasons'] ?? []));
    check('  -> 4 formations', count($site['formations'] ?? []) === 4 ? 200 : 0, 200, (string) count($site['formations'] ?? []));
    check('  -> 4 services', count($site['services'] ?? []) === 4 ? 200 : 0, 200, (string) count($site['services'] ?? []));
    check('  -> experiences', count($site['experiences'] ?? []) > 0 ? 200 : 0, 200, (string) count($site['experiences'] ?? []));
    check('  -> temoignages', count($site['testimonials'] ?? []) > 0 ? 200 : 0, 200, (string) count($site['testimonials'] ?? []));
    check('  -> galerie', count($site['gallery'] ?? []) > 0 ? 200 : 0, 200, (string) count($site['gallery'] ?? []));
    $f = $site['formations'][0] ?? [];
    check('  -> objectifs formation = array', is_array($f['objectives'] ?? null) ? 200 : 0, 200, '');
    check('  -> programme formation = array', is_array($f['programme'] ?? null) ? 200 : 0, 200, '');
}

// 3. Liste et detail des formations
[$c, ] = call($kernel, 'GET', '/api/v1/formations');
check('GET /api/v1/formations', $c, 200, '');

[$c, $one] = call($kernel, 'GET', '/api/v1/formations/gestion-du-temps-et-des-priorites');
check('GET /api/v1/formations/{slug}', $c, 200, $one['title'] ?? '');

[$c, ] = call($kernel, 'GET', '/api/v1/formations/slug-inexistant');
check('GET formation inexistante -> 404', $c, 404, '');

// 4. Validation des 4 formations du CC
foreach (['gestion-du-temps-et-des-priorites', 'productivite-professionnelle', 'vente-et-techniques-commerciales', 'fidelisation-de-la-clientele'] as $slug) {
    [$c, $fm] = call($kernel, 'GET', '/api/v1/formations/'.$slug);
    $complete = $c === 200 && ! empty($fm['description']) && ! empty($fm['objectives']) && ! empty($fm['programme'])
        && ! empty($fm['public_cible']) && ! empty($fm['duree']) && ! empty($fm['modalites']);
    check('  Formation CC: '.$slug, $complete ? 200 : 0, 200, '');
}

// 5. Envoi demande de formation (champs complets CC)
resetRateLimit();
[$c, $r] = call($kernel, 'POST', '/api/v1/leads', [
    'type' => 'formation',
    'organisation' => 'ONG Test',
    'responsable' => 'Aline K.',
    'fonction' => 'Directrice RH',
    'telephone' => '+229 90 00 00 00',
    'email' => 'test@example.com',
    'theme' => 'Gestion du temps et des priorités',
    'participants' => '15',
    'format' => 'Intra-entreprise',
    'date_souhaitee' => '2026-11-10',
    'message' => 'Nous souhaitons planifier cette formation.',
]);
check('POST /api/v1/leads (formation)', $c, 201, $r['message'] ?? ($r['message'] ?? ''));

// 6. Envoi demande de devis (champs complets CC)
resetRateLimit();
[$c, $r] = call($kernel, 'POST', '/api/v1/leads', [
    'type' => 'devis',
    'organisation' => 'Entreprise Test SARL',
    'responsable' => 'M. S.',
    'telephone' => '+228 90 11 22 33',
    'email' => 'devis@example.com',
    'theme' => 'Vente et techniques commerciales',
    'participants' => '8',
    'ville' => 'Lomé',
    'date_souhaitee' => '2026-12-01',
    'duree' => '2 jours',
    'besoins' => 'Renforcement des techniques de négociation.',
    'budget' => '500 000 FCFA',
]);
check('POST /api/v1/leads (devis)', $c, 201, '');

// 7. Validation : champs obligatoires manquants
resetRateLimit();
[$c, ] = call($kernel, 'POST', '/api/v1/leads', ['type' => 'formation', 'organisation' => 'X']);
check('POST leads incomplet -> 422', $c, 422, '');

// 7b. Anti-spam : le champ piege "website" rempli ne doit rien enregistrer
resetRateLimit();
$before = \App\Models\Lead::count();
[$c, ] = call($kernel, 'POST', '/api/v1/leads', [
    'type' => 'formation',
    'organisation' => 'Robot spam',
    'responsable' => 'Bot',
    'telephone' => '000',
    'email' => 'bot@spam.com',
    'theme' => 'spam',
    'website' => 'http://spam.example',
]);
$after = \App\Models\Lead::count();
check('Anti-spam: champ piege rempli -> 201 sans enregistrement', ($c === 201 && $after === $before) ? 200 : 0, 200, "codes {$before}->{$after}");

// 7c. CC §17 : message WhatsApp prerempli modifiable
$wa = $site['settings']['whatsapp_message'] ?? '';
check('CC §17 - message WhatsApp prerempli administrable', $wa !== '' ? 200 : 0, 200, substr((string) $wa, 0, 40).'...');

// 7d. CC §23 : mots-cles SEO presents
check('CC §23 - mots-cles SEO en base', ! empty($site['settings']['seo_keywords']) ? 200 : 0, 200, '');

// 7e. CC §25 : rate limiting des formulaires
// Le compteur n'est volontairement PAS remis a zero ici : on veut mesurer le
// declenchement reel de la limite de 5 envois/minute.
resetRateLimit();
$codes = [];
for ($i = 0; $i < 7; $i++) {
    [$c, ] = call($kernel, 'POST', '/api/v1/leads', [
        'type' => 'devis', 'organisation' => 'Test RL', 'responsable' => 'R',
        'telephone' => '00', 'email' => 'rl@test.com', 'theme' => 't',
    ]);
    $codes[] = $c;
}
check('CC §25 - rate limiting actif (429 apres 5 envois/min)', in_array(429, $codes, true) ? 200 : 0, 200, implode(',', $codes));

// 8. Auth admin
[$c, $auth] = call($kernel, 'POST', '/api/v1/auth/login', [
    'email' => 'geraldoagonse@gmail.com',
    'password' => 'Geraldo@2026',
]);
check('POST /api/v1/auth/login (identifiants corrects)', $c, 200, '');
$token = $auth['token'] ?? null;
check('  -> token renvoyé', $token ? 200 : 0, 200, '');

[$c, ] = call($kernel, 'POST', '/api/v1/auth/login', [
    'email' => 'geraldoagonse@gmail.com',
    'password' => 'mauvais-mot-de-passe',
]);
check('POST login mauvais mot de passe -> 401', $c, 401, '');

// 9. Routes protegees
[$c, ] = call($kernel, 'GET', '/api/v1/admin/content');
check('GET admin/content sans token -> 401', $c, 401, '');

[$c, $content] = call($kernel, 'GET', '/api/v1/admin/content', [], $token);
check('GET admin/content avec token', $c, 200, '');
check('  -> contient settings + 7 collections', isset($content['settings'], $content['formations'], $content['gallery']) ? 200 : 0, 200, '');

[$c, ] = call($kernel, 'GET', '/api/v1/auth/me', [], $token);
check('GET auth/me', $c, 200, '');

// 10. CRUD admin
[$c, $created] = call($kernel, 'POST', '/api/v1/admin/domains', [
    'title' => 'Domaine test',
    'description' => 'Description test',
    'icon' => 'target',
    'sort_order' => 99,
], $token);
check('POST admin/domains (create)', $c, 201, '');
$domainId = $created['id'] ?? null;

[$c, ] = call($kernel, 'PUT', '/api/v1/admin/domains/'.$domainId, [
    'title' => 'Domaine test modifié',
    'description' => 'Description modifiée',
], $token);
check('PUT admin/domains/{id} (update)', $c, 200, '');

[$c, ] = call($kernel, 'DELETE', '/api/v1/admin/domains/'.$domainId, [], $token);
check('DELETE admin/domains/{id}', $c, 200, '');

[$c, ] = call($kernel, 'GET', '/api/v1/admin/leads', [], $token);
check('GET admin/leads', $c, 200, '');

// L'ecriture doit etre prouvee, mais la valeur de production doit survivre a
// l'audit : on memorise l'accroche reelle avant de la remplacer, puis on la
// remet a la fin. Sans cette restauration, le site public affiche « Accroche
// de test » sur l'accueil et le pied de page.
[$c, $avant] = call($kernel, 'GET', '/api/v1/site');
$taglineReel = (string) ($avant['settings']['tagline'] ?? '');

[$c, ] = call($kernel, 'PUT', '/api/v1/admin/settings', [
    'settings' => ['tagline' => 'Accroche de test'],
], $token);
check('PUT admin/settings', $c, 200, '');
[$c, $site] = call($kernel, 'GET', '/api/v1/site');
check('  -> accent mis a jour visible', ($site['settings']['tagline'] ?? '') === 'Accroche de test' ? 200 : 0, 200, '');

[$c, ] = call($kernel, 'PUT', '/api/v1/admin/settings', [
    'settings' => ['tagline' => $taglineReel],
], $token);
[$c, $siteApres] = call($kernel, 'GET', '/api/v1/site');
check('  -> accroche de production restaure', ($siteApres['settings']['tagline'] ?? '') === $taglineReel ? 200 : 0, 200, mb_substr($taglineReel, 0, 60));

// 11. Logout
[$c, ] = call($kernel, 'POST', '/api/v1/auth/logout', [], $token);
check('POST auth/logout', $c, 200, '');

[$c, ] = call($kernel, 'GET', '/api/v1/admin/content', [], $token);
check('GET admin/content avec token revoque -> 401', $c, 401, '');

// 12. CORS preflight
$preflight = Request::create('/api/v1/site', 'OPTIONS', [], [], [], [
    'HTTP_ORIGIN' => 'http://localhost:8001',
    'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
]);
$response = $kernel->handle($preflight);
$acao = $response->headers->get('Access-Control-Allow-Origin');
check('CORS preflight depuis le frontend', $response->getStatusCode() === 204 && $acao ? 200 : 0, 200, (string) $acao);
$kernel->terminate($preflight, $response);

// Rapport
$pass = 0; $fail = 0;
echo str_repeat('=', 78)."\n";
echo "  AUDIT FONCTIONNEL DU BACKEND\n";
echo str_repeat('=', 78)."\n";
foreach ($results as $r) {
    if ($r[0]) { $pass++; echo "  [OK]   {$r[1]}"; }
    else { $fail++; echo "  [FAIL] {$r[1]}"; }
    if ($r[4] !== '') echo "  ({$r[4]})";
    echo "\n";
}
echo str_repeat('=', 78)."\n";
echo "  Reussis: $pass | Echecs: $fail\n";
echo str_repeat('=', 78)."\n";
exit($fail > 0 ? 1 : 0);

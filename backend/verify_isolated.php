<?php

// Verification isolee : chaque scenario tourne dans son propre processus PHP,
// afin qu'aucun etat de session ne puisse fuiter entre les requetes.
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$scenario = $argv[1] ?? '';

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$tokenFile = __DIR__.'/storage/app/token_test.txt';

function call(Kernel $kernel, string $method, string $uri, array $data = [], ?string $token = null): array
{
    $server = ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'];
    if ($token) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
    }
    $request = Request::create($uri, $method, [], [], [], $server, $data ? json_encode($data) : null);
    $response = $kernel->handle($request);
    $body = $response->getContent();
    $kernel->terminate($request, $response);

    return [$response->getStatusCode(), $body];
}

function storedToken(string $file): string
{
    return is_file($file) ? trim(file_get_contents($file)) : '';
}

switch ($scenario) {
    case 'no_token':
        [$c, ] = call($kernel, 'GET', '/api/v1/admin/content');
        echo $c;
        break;

    case 'bad_token':
        [$c, ] = call($kernel, 'GET', '/api/v1/admin/content', [], 'jeton-inexistant-123');
        echo $c;
        break;

    // Processus 1 : obtention du token
    case 'login':
        [$c, $auth] = call($kernel, 'POST', '/api/v1/auth/login', [
            'email' => 'geraldoagonse@gmail.com', 'password' => 'Admin@2026',
        ]);
        $token = json_decode($auth, true)['token'] ?? '';
        file_put_contents($tokenFile, $token);
        echo $c;
        break;

    // Processus 2 : utilisation du token fraichement cree
    case 'use_token':
        [$c, ] = call($kernel, 'GET', '/api/v1/admin/content', [], storedToken($tokenFile));
        echo $c;
        break;

    // Processus 3 : revocation
    case 'revoke':
        [$c, ] = call($kernel, 'POST', '/api/v1/auth/logout', [], storedToken($tokenFile));
        echo $c;
        break;

    // Processus 4 : le token revoque doit etre rejete
    case 'use_revoked':
        [$c, ] = call($kernel, 'GET', '/api/v1/admin/content', [], storedToken($tokenFile));
        @unlink($tokenFile);
        echo $c;
        break;

    case 'user_is_admin':
        $u = User::where('email', 'geraldoagonse@gmail.com')->first();
        echo ($u && $u->is_admin) ? 'oui' : 'non';
        break;

    case 'db_tables':
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        echo count($tables).' tables dans '.\Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        break;

    case 'token_rows':
        echo \Laravel\Sanctum\PersonalAccessToken::count().' token(s) en base';
        break;

    default:
        echo 'scenario inconnu';
}

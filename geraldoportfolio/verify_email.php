<?php

use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// 1. Le template d'email se rend-il sans erreur ?
$mail = new NewLeadMail([
    'organisation' => 'ONG Exemple',
    'responsable' => 'Aline K.',
    'fonction' => 'Directrice RH',
    'telephone' => '+229 90 00 00 00',
    'email' => 'aline@exemple.org',
    'theme' => 'Fidélisation de la clientèle',
    'participants' => '20',
    'format' => 'Intra-entreprise',
    'date_souhaitee' => '15/11/2026',
    'message' => 'Nous voulons améliorer la relation client de nos 3 agences.',
], 'formation');

$rendered = $mail->render();

echo 'Template email rendu : '.(str_contains($rendered, 'ONG Exemple') && str_contains($rendered, 'Fidélisation') ? 'OK' : 'ECHEC')."\n";
echo 'Sujet : '.$mail->envelope()->subject."\n";
echo 'Destinataire : '.config('mail.from.address')."\n";
echo 'Longueur HTML : '.strlen($rendered)." caracteres\n";

// 2. Les donnees sont-elles bien persistees en base ?
echo "\nDemandes enregistrees en base : ".Lead::count()."\n";
foreach (Lead::orderByDesc('id')->limit(3)->get() as $lead) {
    echo "  - #{$lead->id} [{$lead->type}] ".($lead->data['organisation'] ?? '').' / '.($lead->data['responsable'] ?? '')."\n";
}

// 3. La connexion reelle au serveur SMTP est-elle configuree ?
$smtp = config('mail.mailers.smtp');
echo "\nConfig mail :\n";
echo '  default       = '.config('mail.default')."\n";
echo '  host:port     = '.$smtp['host'].':'.$smtp['port']."\n";
echo '  MAIL_USERNAME = '.(config('mail.username') ?: '(vide)')."\n";
echo '  MAIL_PASSWORD = '.(config('mail.password') ? '(defini, '.strlen((string) config('mail.password')).' caracteres)' : '(vide)')."\n";
echo '  expediteur    = '.config('mail.from.address')."\n";
echo '  destinataire  = '.(config('mail.leads_to') ?: '(vide)')."\n";

// 4. L'envoi reel est-il operationnel ?
echo "\nEnvoi reel : ";
$erreur = null;

try {
    Mail::to(config('mail.leads_to'))->send($mail);
    echo "reussi\n";
} catch (Throwable $e) {
    $erreur = $e->getMessage();
    echo "bloque\n";
    echo '  '.str_replace("\n", "\n  ", Str::limit($erreur, 200))."\n";
}

// 5. Que faire de ce resultat ?
echo "\nConsequence sur le site : ";
echo "aucune. La demande est enregistree en base avant l'envoi, le client\n";
echo "reçoit sa confirmation et la demande reste visible dans /admin.\n";

if ($erreur === null) {
    echo "\n=> nothing to do : l'envoi des demandes fonctionne.\n";
} elseif (! config('mail.username') || ! config('mail.password')) {
    echo "\n=> ETAPE 1 : renseigner MAIL_USERNAME et MAIL_PASSWORD dans geraldoportfolio/.env.\n";
    echo "   Pour Gmail, MAIL_PASSWORD est le mot de passe d'application de\n";
    echo "   16 caracteres (voir README.md section 6), PAS le mot de passe du\n";
    echo "   compte Google. La validation en deux etapes doit etre activee.\n";
} elseif (str_contains($erreur, '535') || str_contains($erreur, 'Authentication') || str_contains($erreur, 'Invalid credentials')) {
    echo "\n=> Identifiants refuses par le serveur. Soit le mot de passe\n";
    echo "   d'application est errone, soit la validation en deux etapes\n";
    echo "   n'est pas activee sur le compte, soit la ligne\n";
    echo "   MAIL_ENCRYPTION ne correspond pas au port (587 = tls).\n";
} elseif (str_contains($erreur, '530')) {
    echo "\n=> Authentification requise : le serveur a refuse la connexion\n";
    echo "   parce que les identifiants manquent ou sont vides.\n";
} elseif (str_contains($erreur, 'Could not authenticate') || str_contains($erreur, 'certificate') || str_contains($erreur, 'Connection refused')) {
    echo "\n=> Connexion au serveur impossible : reseau, pare-feu, ou\n";
    echo "   MAIL_HOST / MAIL_PORT / MAIL_ENCRYPTION a verifier.\n";
}

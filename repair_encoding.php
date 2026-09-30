<?php

/**
 * Repare les fichiers dont les accents ont ete abimes par un double
 * encodage UTF-8 (le texte "é" devient "Ã©").
 *
 * Un seul tour de decodage est applique : le resultat est verifie avant
 * ecriture, et le fichier n'est touche que si l'allure s'ameliore.
 *
 * Usage : php repair_encoding.php [--simuler]
 */

$racine = __DIR__;
$simuler = in_array('--simuler', $argv, true);

$dossiers = ['frontend/app', 'frontend/resources', 'frontend/routes', 'frontend/database',
    'backend/app', 'backend/config', 'backend/routes', 'backend/database', 'backend/tests'];

$fichiers = [];

foreach ($dossiers as $dossier) {
    $chemin = $racine.'/'.$dossier;

    if (! is_dir($chemin)) {
        continue;
    }

    $iterateur = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($chemin, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterateur as $fichier) {
        if ($fichier->isFile() && in_array($fichier->getExtension(), ['php', 'md'], true)) {
            $fichiers[] = $fichier->getPathname();
        }
    }
}

foreach (['verify_cc.php', 'verify_api.php', 'README.md', 'installer.ps1'] as $racineFichier) {
    if (is_file($racine.'/'.$racineFichier)) {
        $fichiers[] = $racine.'/'.$racineFichier;
    }
}

/**
 * Mesure la corruption : un texte abime contient surtout des caracteres
 * Latin-1 sans signification (U+0080-U+00BF), absents du francais.
 */
function corrompu(string $texte): int
{
    return preg_match_all('/[\x{0080}-\x{00BF}]/u', $texte) ?: 0;
}

$repares = 0;
$intacts = 0;

foreach ($fichiers as $fichier) {
    $original = (string) file_get_contents($fichier);

    $avant = corrompu($original);

    if ($avant === 0) {
        $intacts++;

        continue;
    }

    // Un tour de decodage : les octets UTF-8 de "Ã©" redeviennent "é".
    $corrige = mb_convert_encoding($original, 'UTF-8', 'UTF-8');

    $apres = corrompu($corrige);

    if ($apres >= $avant) {
            printf("  [inchange] %-58s suspects avant=%d apres=%d\n",
            basename($fichier), $avant, $apres);

        continue;
    }

    $repares++;

    printf("  [repare]   %-58s suspects avant=%d apres=%d\n",
        basename($fichier), $avant, $apres);

    if (! $simuler) {
        file_put_contents($fichier, $corrige);
    }
}

printf("\n%d fichier(s) intact(s), %d fichier(s) %s.\n",
    $intacts, $repares, $simuler ? 'a reparer' : 'repares');

if ($repares > 0) {
    echo "\nRelancez ensuite :\n";
    echo "  cd frontend && php artisan view:clear\n";
    echo "  php verify_cc.php\n";
}

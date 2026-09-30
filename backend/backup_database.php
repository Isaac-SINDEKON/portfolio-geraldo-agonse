<?php

/**
 * Sauvegarde et restauration du site, sans dependance externe (mysqldump).
 *
 *   php backup_database.php [dossier]              sauvegarde complete
 *   php backup_database.php --restore <fichier.sql> restaure base + photos
 *   php backup_database.php --list                  liste les sauvegardes
 *
 * Une sauvegarde complete comprend deux fichiers :
 *   - <base>_<horodatage>.sql          tables et donnees
 *   - <base>_<horodatage>_photos.zip   photos de la galerie et de profil
 *
 * Les deux vont ensemble : le SQL memorise le chemin des images
 * (uploads/xxx.jpg) et le ZIP contient les fichiers correspondants. Restaurer
 * le SQL sans le ZIP laisserait des images manquantes sur le site.
 */

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$argument = $argv[1] ?? null;

if ($argument === '--list') {
    $dossier = $argv[2] ?? __DIR__.'/storage/backups';
    $fichiers = glob(rtrim($dossier, '/\\').'/*.sql') ?: [];

    if ($fichiers === []) {
        echo "Aucune sauvegarde dans {$dossier}\n";
        exit(0);
    }

    rsort($fichiers);
    echo "Sauvegardes disponibles :\n";

    foreach ($fichiers as $sql) {
        $zip = preg_replace('/\.sql$/', '_photos.zip', $sql);
        $poids = number_format(filesize($sql) / 1024, 1).' Ko';
        $photos = is_file($zip)
            ? number_format(filesize($zip) / 1024, 1).' Ko'
            : 'ABSENTES';

        printf("  %s  (%s, photos : %s)\n", basename($sql), $poids, $photos);
    }

    exit(0);
}

if ($argument === '--restore') {
    $fichier = $argv[2] ?? null;

    if (! $fichier || ! is_file($fichier)) {
        fwrite(STDERR, "Usage : php backup_database.php --restore <fichier.sql>\n");
        exit(1);
    }

    $contenu = (string) file_get_contents($fichier);
    $instructions = 0;

    DB::statement('SET FOREIGN_KEY_CHECKS = 0');

    // Le fichier est produit par ce script : une instruction par ligne,
    // terminee par un point-virgule.
    foreach (explode(";\n", $contenu) as $instruction) {
        $instruction = trim($instruction);

        if ($instruction === '' || str_starts_with($instruction, '--')) {
            continue;
        }

        DB::unprepared($instruction);
        $instructions++;
    }

    DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    echo "Base restauree : {$instructions} instruction(s) depuis ".basename($fichier)."\n";

    // Photos : le ZIP doit porter le meme nom que le SQL.
    $zip = preg_replace('/\.sql$/', '_photos.zip', $fichier);
    $cible = __DIR__.'/storage/app/public';

    if (! is_file($zip)) {
        echo "Photos : aucun ZIP ".basename($zip)." a cote du SQL, images non restaurees.\n";
        exit(0);
    }

    if (! class_exists(ZipArchive::class)) {
        echo "Photos : extension PHP zip absente, extraution impossible.\n";
        exit(1);
    }

    $archive = new ZipArchive;

    if ($archive->open($zip) !== true) {
        fwrite(STDERR, "Photos : impossible d'ouvrir ".basename($zip)."\n");
        exit(1);
    }

    $extraites = 0;

    for ($i = 0; $i < $archive->numFiles; $i++) {
        $nom = $archive->getNameIndex($i);

        if ($nom === false || str_contains($nom, '..')) {
            continue;
        }

        $contenuFichier = $archive->getFromIndex($i);
        $chemin = $cible.'/'.$nom;

        if (! is_dir(dirname($chemin))) {
            mkdir(dirname($chemin), 0755, true);
        }

        file_put_contents($chemin, $contenuFichier);
        $extraites++;
    }

    $archive->close();

    echo "Photos restaurees : {$extraites} fichier(s) dans storage/app/public\n";
    echo "\nRappel : relancez .\demarrer.ps1 pour recreer le lien public/storage.\n";
    exit(0);
}

$destination = $argument ?? __DIR__.'/storage/backups';

if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
    fwrite(STDERR, "Impossible de creer le dossier de sauvegarde : {$destination}\n");
    exit(1);
}

$database = config('database.connections.mysql.database');
$file = sprintf('%s/%s_%s.sql', rtrim($destination, '/\\'), $database, date('Y-m-d_H-i-s'));

$handle = fopen($file, 'wb');

if ($handle === false) {
    fwrite(STDERR, "Impossible d'ouvrir le fichier de sauvegarde.\n");
    exit(1);
}

$write = function (string $line) use ($handle): void {
    fwrite($handle, $line."\n");
};

$write('-- Sauvegarde de la base '.$database);
$write('-- Générée le '.date('Y-m-d H:i:s'));
$write('SET FOREIGN_KEY_CHECKS = 0;');
$write('SET NAMES utf8mb4;');
$write('');

$tables = DB::select('SHOW TABLES');
$tableNames = array_map(fn ($row) => array_values((array) $row)[0], $tables);

$rowsTotal = 0;

foreach ($tableNames as $table) {
    $create = DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
    $sql = (array) $create;
    $statement = $sql['Create Table'] ?? $sql['Create View'] ?? null;

    if (! $statement) {
        continue;
    }

    $write('DROP TABLE IF EXISTS `'.$table.'`;');
    $write($statement.';');

    $columns = array_map(fn ($column) => $column->Field, DB::select('SHOW COLUMNS FROM `'.$table.'`'));

    if ($columns === []) {
        $write('');
        continue;
    }

    $quotedColumns = implode(', ', array_map(fn ($column) => '`'.$column.'`', $columns));

    foreach (DB::table($table)->orderBy($columns[0])->get() as $row) {
        $values = array_map(function ($value) {
            if ($value === null) {
                return 'NULL';
            }

            return DB::getPdo()->quote((string) $value);
        }, (array) $row);

        $write('INSERT INTO `'.$table.'` ('.$quotedColumns.') VALUES ('.implode(', ', $values).');');
        $rowsTotal++;
    }

    $write('');
}

$write('SET FOREIGN_KEY_CHECKS = 1;');
$write('');
$write('-- Fin de la sauvegarde : '.$rowsTotal.' ligne(s) inseree(s).');

fclose($handle);

echo 'Sauvegarde creee : '.$file.' ('.number_format($rowsTotal).' lignes)'."\n";
echo 'Tables : '.count($tableNames)."\n";

// --- Photos -----------------------------------------------------------------
// Le SQL ne contient que le chemin des images. Sans les fichiers, une
// restauration laisserait la galerie et la photo de profil introuvables.
$dossierPhotos = __DIR__.'/storage/app/public';
$archivePhotos = preg_replace('/\.sql$/', '_photos.zip', $file);
$nbPhotos = 0;

if (! class_exists(ZipArchive::class)) {
    echo "Photos : extension PHP zip absente, photos NON sauvegardees.\n";
    echo "  Installer l'extension zip, ou copier storage/app/public a la main.\n";
} else {
    $archive = new ZipArchive;

    if ($archive->open($archivePhotos, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fwrite(STDERR, "Photos : impossible de creer ".basename($archivePhotos)."\n");
    } else {
        $iterateur = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dossierPhotos, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterateur as $element) {
            if (! $element->isFile()) {
                continue;
            }

            $chemin = $element->getPathname();

            if (str_ends_with(strtolower($chemin), '.gitignore')) {
                continue;
            }

            $archive->addFile($chemin, ltrim(str_replace('\\', '/', substr($chemin, strlen($dossierPhotos))), '/'));
            $nbPhotos++;
        }

        $archive->close();

        echo 'Photos archivees : '.$archivePhotos.' ('.$nbPhotos.' fichier(s), '
            .number_format(filesize($archivePhotos) / 1024, 1)." Ko)\n";
        echo "\nLes deux fichiers vont ensemble : conservez le .sql ET le _photos.zip.\n";
        echo "Restauration : php backup_database.php --restore <fichier.sql>\n";
    }
}

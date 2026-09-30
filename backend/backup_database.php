<?php

/**
 * Sauvegarde de la base de donnees MySQL sans dependance externe (mysqldump).
 *
 * Usage : php backup_database.php [dossier-de-destination]
 *
 * Le fichier genere contient les instructions CREATE TABLE et INSERT de toutes
 * les tables de la base configuree dans .env (CC §25 sauvegardes).
 */

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$destination = $argv[1] ?? __DIR__.'/storage/backups';

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

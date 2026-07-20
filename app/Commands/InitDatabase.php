<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Initialise (ou réinitialise) la base de données SQLite de l'application
 * à partir du script `base.sql` situé à la racine du projet.
 *
 * Usage : php spark db:init
 */
class InitDatabase extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:init';
    protected $description = 'Crée/réinitialise la base SQLite à partir de base.sql';

    public function run(array $params)
    {
        $sqlFile = ROOTPATH . 'base.sql';

        if (! is_file($sqlFile)) {
            CLI::error("Fichier introuvable : {$sqlFile}");

            return;
        }

        if (! extension_loaded('sqlite3')) {
            CLI::error('L\'extension PHP sqlite3 n\'est pas activée.');

            return;
        }

        $dbPath = WRITEPATH . 'database.db';

        if (is_file($dbPath)) {
            CLI::write("Un fichier de base existe déjà : {$dbPath}", 'yellow');

            if (CLI::prompt('Voulez-vous le réinitialiser (les données seront perdues) ?', ['y', 'n']) !== 'y') {
                CLI::write('Opération annulée.', 'yellow');

                return;
            }
        }

        $sqlite = new \SQLite3($dbPath);
        $sqlite->exec('PRAGMA foreign_keys = ON;');
        $sqlite->exec(file_get_contents($sqlFile));
        $sqlite->close();

        CLI::write("Base de données initialisée avec succès : {$dbPath}", 'green');
    }
}

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

    protected $options = [
        '--force' => 'Ecrase le fichier de base existant sans demander de confirmation.',
    ];

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
        $force  = array_key_exists('force', $params) || CLI::getOption('force');

        if (is_file($dbPath) && ! $force) {
            CLI::write("Un fichier de base existe déjà : {$dbPath}", 'yellow');

            if (CLI::prompt('Voulez-vous le réinitialiser (les données seront perdues) ?', ['y', 'n']) !== 'y') {
                CLI::write('Opération annulée.', 'yellow');

                return;
            }
        }

        $sqlite = new \SQLite3($dbPath);

        // Les DROP TABLE de base.sql suppriment les tables "parentes"
        // (ex. prefixes_operateur) avant leurs tables "enfants" (ex.
        // clients). Avec les clés étrangères activées, SQLite refuse de
        // supprimer une table encore référencée par des lignes existantes :
        // on désactive donc temporairement la vérification pendant le
        // rechargement complet du schéma, puis on la réactive pour les
        // usages normaux de l'application.
        $sqlite->exec('PRAGMA foreign_keys = OFF;');
        $sqlite->exec(file_get_contents($sqlFile));
        $sqlite->exec('PRAGMA foreign_keys = ON;');
        $sqlite->close();

        CLI::write("Base de données initialisée avec succès : {$dbPath}", 'green');
    }
}

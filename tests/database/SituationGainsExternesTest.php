<?php

use App\Models\OperationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests de la V2 — page "Situation des gains" : séparation entre les gains
 * internes (frais habituels du barème) et les gains "autres opérateurs"
 * (commission inter-opérateur perçue sur les transferts sortants).
 *
 * @internal
 */
final class SituationGainsExternesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = db_connect();
        $prefix       = $this->testDb->DBPrefix;

        foreach (['operations', 'comptes', 'clients', 'types_operation', 'prefixes_operateur'] as $table) {
            $this->testDb->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        $this->testDb->query("
            CREATE TABLE {$prefix}prefixes_operateur (
                id                      INTEGER PRIMARY KEY AUTOINCREMENT,
                prefixe                 VARCHAR(3) NOT NULL UNIQUE,
                libelle                 VARCHAR(50),
                is_internal             BOOLEAN DEFAULT TRUE NOT NULL,
                commission_pourcentage  DECIMAL(5,2) NOT NULL DEFAULT 0,
                date_creation           DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->testDb->query("
            CREATE TABLE {$prefix}types_operation (
                id      INTEGER PRIMARY KEY AUTOINCREMENT,
                code    VARCHAR(20) NOT NULL UNIQUE,
                libelle VARCHAR(50) NOT NULL
            )
        ");

        $this->testDb->query("
            CREATE TABLE {$prefix}clients (
                id                  INTEGER PRIMARY KEY AUTOINCREMENT,
                numero_telephone    VARCHAR(15) NOT NULL UNIQUE,
                prefixe_id          INTEGER NOT NULL,
                date_creation       DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->testDb->query("
            CREATE TABLE {$prefix}comptes (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id  INTEGER NOT NULL UNIQUE,
                solde      DECIMAL(12,2) NOT NULL DEFAULT 0
            )
        ");

        $this->testDb->query("
            CREATE TABLE {$prefix}operations (
                id                     INTEGER PRIMARY KEY AUTOINCREMENT,
                compte_id              INTEGER NOT NULL,
                compte_destinataire_id INTEGER,
                type_operation_id      INTEGER NOT NULL,
                montant                DECIMAL(12,2) NOT NULL,
                frais                  DECIMAL(12,2) NOT NULL DEFAULT 0,
                commission             DECIMAL(12,2) NOT NULL DEFAULT 0,
                date_operation         DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 1 préfixe interne, 2 préfixes externes (Orange, Airtel).
        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '034', 'libelle' => 'Telma', 'is_internal' => 1, 'commission_pourcentage' => 0]);
        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0, 'commission_pourcentage' => 2]);
        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '033', 'libelle' => 'Airtel', 'is_internal' => 0, 'commission_pourcentage' => 1.5]);

        $this->testDb->table('types_operation')->insert(['code' => 'depot', 'libelle' => 'Dépôt']);
        $this->testDb->table('types_operation')->insert(['code' => 'retrait', 'libelle' => 'Retrait']);
        $this->testDb->table('types_operation')->insert(['code' => 'transfert', 'libelle' => 'Transfert']);

        // Clients : 1 interne (émetteur), 1 interne (destinataire interne),
        // 1 externe Orange, 1 externe Airtel.
        $interne1  = $this->creerClient('0341110000', 1, 500000);
        $interne2  = $this->creerClient('0341110001', 1, 0);
        $orange    = $this->creerClient('0321110000', 2, 0);
        $airtel    = $this->creerClient('0331110000', 3, 0);

        // Dépôt (frais = 0) — gains internes.
        $this->creerOperation($interne1['compte_id'], null, 1, 10000, 0, 0);

        // Retrait (frais = 500) — gains internes.
        $this->creerOperation($interne1['compte_id'], null, 2, 20000, 500, 0);

        // Transfert vers un client interne (frais = 100, commission = 0) — gains internes.
        $this->creerOperation($interne1['compte_id'], $interne2['compte_id'], 3, 50000, 100, 0);

        // Transfert vers Orange (frais = 100, commission = 2000) — gains externes.
        $this->creerOperation($interne1['compte_id'], $orange['compte_id'], 3, 100000, 100, 2000);

        // Transfert vers Airtel (frais = 100, commission = 750) — gains externes.
        $this->creerOperation($interne1['compte_id'], $airtel['compte_id'], 3, 50000, 100, 750);
    }

    protected function tearDown(): void
    {
        $prefix = $this->testDb->DBPrefix;

        foreach (['operations', 'comptes', 'clients', 'types_operation', 'prefixes_operateur'] as $table) {
            $this->testDb->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        parent::tearDown();
    }

    /**
     * @return array{client_id: int, compte_id: int}
     */
    private function creerClient(string $numero, int $prefixeId, float $solde): array
    {
        $this->testDb->table('clients')->insert(['numero_telephone' => $numero, 'prefixe_id' => $prefixeId]);
        $clientId = (int) $this->testDb->insertID();

        $this->testDb->table('comptes')->insert(['client_id' => $clientId, 'solde' => $solde]);
        $compteId = (int) $this->testDb->insertID();

        return ['client_id' => $clientId, 'compte_id' => $compteId];
    }

    private function creerOperation(int $compteId, ?int $compteDestinataireId, int $typeOperationId, float $montant, float $frais, float $commission): void
    {
        $this->testDb->table('operations')->insert([
            'compte_id'              => $compteId,
            'compte_destinataire_id' => $compteDestinataireId,
            'type_operation_id'      => $typeOperationId,
            'montant'                => $montant,
            'frais'                  => $frais,
            'commission'             => $commission,
        ]);
    }

    public function testLeRecapExterneNeGroupeQueLesTransfertsVersDesPrefixesExternes(): void
    {
        $model = new OperationModel();
        $recap = $model->recapCommissionParOperateurExterne();

        $this->assertCount(2, $recap);

        $parPrefixe = [];
        foreach ($recap as $ligne) {
            $parPrefixe[$ligne['prefixe']] = $ligne;
        }

        $this->assertSame(1, (int) $parPrefixe['032']['nb_transferts']);
        $this->assertSame(2000.0, (float) $parPrefixe['032']['total_commission']);

        $this->assertSame(1, (int) $parPrefixe['033']['nb_transferts']);
        $this->assertSame(750.0, (float) $parPrefixe['033']['total_commission']);
    }

    public function testLesGainsInternesNIncluentPasLaCommission(): void
    {
        $model = new OperationModel();
        $recap = $model->recapFraisParType();

        $totalInterne = 0.0;
        foreach ($recap as $ligne) {
            $totalInterne += (float) $ligne['total_frais'];
        }

        // 0 (dépôt) + 500 (retrait) + 100 + 100 + 100 (3 transferts, frais barème uniquement) = 800.
        $this->assertSame(800.0, $totalInterne);
    }

    public function testLaPageDesGainsAfficheLesDeuxBlocsAvecLesBonsTotaux(): void
    {
        $result = $this->withSession(['operateur_id' => 1])->get('admin/gains');

        $result->assertOK();
        $result->assertSee('Gains internes');
        $result->assertSee('Gains autres opérateurs');

        // Total interne : 800 Ar (voir test précédent).
        $result->assertSee('800,00');

        // Total externe : 2000 + 750 = 2750 Ar.
        $result->assertSee('2 750,00');

        // Total global : 800 + 2750 = 3550 Ar.
        $result->assertSee('3 550,00');
    }

    public function testLeFiltreParTypeNAffecteQueLeBlocInterne(): void
    {
        $typeDepotId = 1;

        $result = $this->withSession(['operateur_id' => 1])
            ->get('admin/gains', ['type_operation_id' => $typeDepotId]);

        $result->assertOK();
        // Le dépôt n'a pas de frais dans ce jeu de données : gains internes = 0.
        $result->assertSee('0,00');

        // Le bloc externe reste inchangé (2 750 Ar), peu importe le filtre choisi.
        $result->assertSee('2 750,00');
    }
}

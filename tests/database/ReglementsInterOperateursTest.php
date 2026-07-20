<?php

use App\Models\OperationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests de la V2 — page "Situation des montants à envoyer à chaque
 * opérateur" : table de réconciliation par transfert et regroupement par
 * opérateur externe du montant net dû (hors frais et commission).
 *
 * @internal
 */
final class ReglementsInterOperateursTest extends CIUnitTestCase
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

        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '034', 'libelle' => 'Telma', 'is_internal' => 1, 'commission_pourcentage' => 0]);
        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0, 'commission_pourcentage' => 2]);
        $this->testDb->table('prefixes_operateur')->insert(['prefixe' => '033', 'libelle' => 'Airtel', 'is_internal' => 0, 'commission_pourcentage' => 1.5]);

        $this->testDb->table('types_operation')->insert(['code' => 'depot', 'libelle' => 'Dépôt']);
        $this->testDb->table('types_operation')->insert(['code' => 'retrait', 'libelle' => 'Retrait']);
        $this->testDb->table('types_operation')->insert(['code' => 'transfert', 'libelle' => 'Transfert']);

        $interne  = $this->creerClient('0341110000', 1, 500000);
        $orange1  = $this->creerClient('0321110000', 2, 0);
        $orange2  = $this->creerClient('0321110001', 2, 0);
        $airtel   = $this->creerClient('0331110000', 3, 0);

        // 2 transferts vers Orange : 100 000 Ar (commission 2000) et 50 000 Ar (commission 1000).
        $this->creerOperation($interne['compte_id'], $orange1['compte_id'], 3, 100000, 100, 2000);
        $this->creerOperation($interne['compte_id'], $orange2['compte_id'], 3, 50000, 100, 1000);

        // 1 transfert vers Airtel : 20 000 Ar (commission 300).
        $this->creerOperation($interne['compte_id'], $airtel['compte_id'], 3, 20000, 100, 300);
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

    private function creerOperation(int $compteId, int $compteDestinataireId, int $typeOperationId, float $montant, float $frais, float $commission): void
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

    public function testLeRegroupementDonneLeMontantNetDuParOperateurExterne(): void
    {
        $model = new OperationModel();
        $recap = $model->recapMontantsDusParOperateurExterne();

        $this->assertCount(2, $recap);

        $parPrefixe = [];
        foreach ($recap as $ligne) {
            $parPrefixe[$ligne['prefixe']] = $ligne;
        }

        // Orange : 100 000 + 50 000 = 150 000 Ar dus (hors frais/commission).
        $this->assertSame(2, (int) $parPrefixe['032']['nb_transferts']);
        $this->assertSame(150000.0, (float) $parPrefixe['032']['montant_du']);
        $this->assertSame(3000.0, (float) $parPrefixe['032']['total_commission']);

        // Airtel : 20 000 Ar dus.
        $this->assertSame(1, (int) $parPrefixe['033']['nb_transferts']);
        $this->assertSame(20000.0, (float) $parPrefixe['033']['montant_du']);
    }

    public function testLeDetailListeChaqueTransfertExterneAvecSonMontantDu(): void
    {
        $model  = new OperationModel();
        $detail = $model->detailTransfertsExternes();

        $this->assertCount(3, $detail);

        $montants = array_map(static fn ($l) => (float) $l['montant'], $detail);
        sort($montants);
        $this->assertSame([20000.0, 50000.0, 100000.0], $montants);
    }

    public function testLeDetailPeutEtreFiltreParOperateurExterne(): void
    {
        $model = new OperationModel();

        $prefixeAirtelId = (int) $this->testDb->table('prefixes_operateur')->where('prefixe', '033')->get()->getRowArray()['id'];

        $detail = $model->detailTransfertsExternes($prefixeAirtelId);

        $this->assertCount(1, $detail);
        $this->assertSame(20000.0, (float) $detail[0]['montant']);
        $this->assertSame('033', $detail[0]['prefixe']);
    }

    public function testLaPageAffichesLeTotalEtLeRegroupementParOperateur(): void
    {
        $result = $this->withSession(['operateur_id' => 1])->get('admin/reglements');

        $result->assertOK();
        $result->assertSee('Règlements inter-opérateurs');
        $result->assertSee('Orange');
        $result->assertSee('Airtel');

        // Total montant net dû : 150 000 + 20 000 = 170 000 Ar.
        $result->assertSee('170 000,00');

        // Détail par transfert : le montant de chaque transfert individuel apparaît.
        $result->assertSee('100 000,00');
        $result->assertSee('50 000,00');
        $result->assertSee('20 000,00');
    }

    public function testLeFiltrePrefixeIdRestreintLaTableDeDetailSurLaPage(): void
    {
        $prefixeAirtelId = (int) $this->testDb->table('prefixes_operateur')->where('prefixe', '033')->get()->getRowArray()['id'];

        $result = $this->withSession(['operateur_id' => 1])
            ->get('admin/reglements', ['prefixe_id' => $prefixeAirtelId]);

        $result->assertOK();
        $result->assertSee('20 000,00');
        $result->assertDontSee('100 000,00');
    }
}

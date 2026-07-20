<?php

use App\Models\OperationModel;
use App\Models\PrefixeOperateurModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests de la V2 — configuration du % de commission inter-opérateur et de
 * son application lors des transferts sortants vers un préfixe externe.
 *
 * @internal
 */
final class CommissionInterOperateurTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = db_connect();
        $prefix       = $this->testDb->DBPrefix;

        foreach (['operations', 'comptes', 'clients', 'baremes_frais', 'types_operation', 'prefixes_operateur'] as $table) {
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
            CREATE TABLE {$prefix}baremes_frais (
                id                 INTEGER PRIMARY KEY AUTOINCREMENT,
                type_operation_id  INTEGER NOT NULL,
                montant_min        DECIMAL(12,2) NOT NULL,
                montant_max        DECIMAL(12,2) NOT NULL,
                frais              DECIMAL(12,2) NOT NULL
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

        // Données de base : 1 préfixe interne, 1 préfixe externe avec 2% de
        // commission, 1 type d'opération "transfert" et un barème simple.
        $this->testDb->table('prefixes_operateur')->insert([
            'prefixe' => '034', 'libelle' => 'Telma', 'is_internal' => 1, 'commission_pourcentage' => 0,
        ]);
        $this->testDb->table('prefixes_operateur')->insert([
            'prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0, 'commission_pourcentage' => 2,
        ]);

        $this->testDb->table('types_operation')->insert(['code' => 'depot', 'libelle' => 'Dépôt']);
        $this->testDb->table('types_operation')->insert(['code' => 'retrait', 'libelle' => 'Retrait']);
        $this->testDb->table('types_operation')->insert(['code' => 'transfert', 'libelle' => 'Transfert']);

        // Barème transfert : frais fixe de 100 Ar pour tout montant jusqu'à 1 000 000 Ar.
        $this->testDb->table('baremes_frais')->insert([
            'type_operation_id' => 3, 'montant_min' => 0, 'montant_max' => 1000000, 'frais' => 100,
        ]);
    }

    protected function tearDown(): void
    {
        $prefix = $this->testDb->DBPrefix;

        foreach (['operations', 'comptes', 'clients', 'baremes_frais', 'types_operation', 'prefixes_operateur'] as $table) {
            $this->testDb->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        parent::tearDown();
    }

    /**
     * Crée un client + son compte, et renvoie leurs identifiants.
     *
     * @return array{client_id: int, compte_id: int}
     */
    private function creerClient(string $numero, int $prefixeId, float $solde): array
    {
        $this->testDb->table('clients')->insert([
            'numero_telephone' => $numero,
            'prefixe_id'       => $prefixeId,
        ]);
        $clientId = (int) $this->testDb->insertID();

        $this->testDb->table('comptes')->insert([
            'client_id' => $clientId,
            'solde'     => $solde,
        ]);
        $compteId = (int) $this->testDb->insertID();

        return ['client_id' => $clientId, 'compte_id' => $compteId];
    }

    public function testAucuneCommissionNestAppliqueeQuandLePrefixeEstInterne(): void
    {
        $model = new PrefixeOperateurModel();
        $prefixeInterne = $model->where('prefixe', '034')->first();

        $this->assertSame(0.0, $model->calculerCommission((int) $prefixeInterne['id'], 100000));
    }

    public function testLaCommissionEstCalculeeSelonLePourcentageDuPrefixeExterne(): void
    {
        $model = new PrefixeOperateurModel();
        $prefixeExterne = $model->where('prefixe', '032')->first();

        // 2 % de 100 000 Ar = 2 000 Ar.
        $this->assertSame(2000.0, $model->calculerCommission((int) $prefixeExterne['id'], 100000));
    }

    public function testAucuneCommissionQuandLePrefixeEstIntrouvableOuNul(): void
    {
        $model = new PrefixeOperateurModel();

        $this->assertSame(0.0, $model->calculerCommission(null, 100000));
        $this->assertSame(0.0, $model->calculerCommission(999999, 100000));
    }

    public function testLeBackOfficeForceLaCommissionA0PourUnPrefixeInterne(): void
    {
        $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'                => '038',
                'libelle'                => 'Telma 2',
                'is_internal'            => '1',
                'commission_pourcentage' => '5', // ignoré côté serveur car interne
            ]);

        $model    = new PrefixeOperateurModel();
        $prefixe  = $model->where('prefixe', '038')->first();

        $this->assertSame(0.0, (float) $prefixe['commission_pourcentage']);
    }

    public function testLeBackOfficeEnregistreLaCommissionPourUnPrefixeExterne(): void
    {
        $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'                => '033',
                'libelle'                => 'Airtel',
                'is_internal'            => '0',
                'commission_pourcentage' => '1.5',
            ]);

        $model    = new PrefixeOperateurModel();
        $prefixe  = $model->where('prefixe', '033')->first();

        $this->assertSame(1.5, (float) $prefixe['commission_pourcentage']);
    }

    public function testLeTransfertVersUnPrefixeExterneAppliqueFraisEtCommission(): void
    {
        $prefixeModel   = new PrefixeOperateurModel();
        $prefixeInterne = $prefixeModel->where('prefixe', '034')->first();
        $prefixeExterne = $prefixeModel->where('prefixe', '032')->first();

        $expediteur   = $this->creerClient('0341112222', (int) $prefixeInterne['id'], 500000);
        $destinataire = $this->creerClient('0321112222', (int) $prefixeExterne['id'], 0);

        $result = $this->withSession([
            'isClientLoggedIn' => true,
            'client_id'        => $expediteur['client_id'],
            'compte_id'        => $expediteur['compte_id'],
            'numero_telephone' => '0341112222',
        ])->post('client/transfert', [
            'numero_destinataire' => '0321112222',
            'montant'              => '100000',
        ]);

        $result->assertRedirectTo('/client/solde');

        // Solde expéditeur : 500 000 - (100 000 + 100 frais + 2000 commission) = 397 900.
        $soldeExpediteur = $this->testDb->table('comptes')->where('client_id', $expediteur['client_id'])->get()->getRowArray();
        $this->assertSame(397900.0, (float) $soldeExpediteur['solde']);

        // Solde destinataire : crédité uniquement du montant net, sans les frais/commission.
        $soldeDestinataire = $this->testDb->table('comptes')->where('client_id', $destinataire['client_id'])->get()->getRowArray();
        $this->assertSame(100000.0, (float) $soldeDestinataire['solde']);

        $operationModel = new OperationModel();
        $operation       = $operationModel->orderBy('id', 'DESC')->first();

        $this->assertSame(100.0, (float) $operation['frais']);
        $this->assertSame(2000.0, (float) $operation['commission']);
    }

    public function testLeTransfertVersUnPrefixeInterneNAppliqueAucuneCommission(): void
    {
        $prefixeModel   = new PrefixeOperateurModel();
        $prefixeInterne = $prefixeModel->where('prefixe', '034')->first();

        $expediteur   = $this->creerClient('0341113333', (int) $prefixeInterne['id'], 500000);
        $destinataire = $this->creerClient('0341114444', (int) $prefixeInterne['id'], 0);

        $result = $this->withSession([
            'isClientLoggedIn' => true,
            'client_id'        => $expediteur['client_id'],
            'compte_id'        => $expediteur['compte_id'],
            'numero_telephone' => '0341113333',
        ])->post('client/transfert', [
            'numero_destinataire' => '0341114444',
            'montant'              => '100000',
        ]);

        $result->assertRedirectTo('/client/solde');

        $operationModel = new OperationModel();
        $operation       = $operationModel->orderBy('id', 'DESC')->first();

        $this->assertSame(100.0, (float) $operation['frais']);
        $this->assertSame(0.0, (float) $operation['commission']);
    }
}

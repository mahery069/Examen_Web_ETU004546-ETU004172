<?php

use App\Models\PrefixeOperateurModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests de la V2 — configuration des préfixes des autres opérateurs
 * (colonne is_internal, formulaire commun, listes séparées internes/externes).
 *
 * @internal
 */
final class PrefixesInternesExternesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        // Aucune migration CI4 ne décrit ce schéma (il vient de base.sql),
        // on recrée donc la table nécessaire directement sur la base de test.
        // Le nom réel de la table doit inclure le DBPrefix configuré pour le
        // groupe "tests" (voir app/Config/Database.php), sinon le Query
        // Builder utilisé par le Model ne la retrouve pas.
        $this->testDb = db_connect();
        $table        = $this->testDb->DBPrefix . 'prefixes_operateur';
        $this->testDb->query("DROP TABLE IF EXISTS {$table}");
        $this->testDb->query("
            CREATE TABLE {$table} (
                id                      INTEGER PRIMARY KEY AUTOINCREMENT,
                prefixe                 VARCHAR(3) NOT NULL UNIQUE,
                libelle                 VARCHAR(50),
                is_internal             BOOLEAN DEFAULT TRUE NOT NULL,
                commission_pourcentage  DECIMAL(5,2) NOT NULL DEFAULT 0,
                date_creation           DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function tearDown(): void
    {
        $table = $this->testDb->DBPrefix . 'prefixes_operateur';
        $this->testDb->query("DROP TABLE IF EXISTS {$table}");

        parent::tearDown();
    }

    public function testAjoutDunPrefixeInterneEtDunPrefixeExterne(): void
    {
        $resultInterne = $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'     => '034',
                'libelle'     => 'Telma',
                'is_internal' => '1',
            ]);
        $resultInterne->assertRedirectTo('/admin/prefixes');

        $resultExterne = $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'     => '032',
                'libelle'     => 'Orange',
                'is_internal' => '0',
            ]);
        $resultExterne->assertRedirectTo('/admin/prefixes');

        $model   = new PrefixeOperateurModel();
        $interne = $model->where('prefixe', '034')->first();
        $externe = $model->where('prefixe', '032')->first();

        $this->assertNotNull($interne, 'Le préfixe interne aurait dû être enregistré.');
        $this->assertNotNull($externe, 'Le préfixe externe aurait dû être enregistré.');
        $this->assertSame(1, (int) $interne['is_internal']);
        $this->assertSame(0, (int) $externe['is_internal']);
    }

    public function testLeFormulaireAppliqueLeMemeControleDeFormatPourUnPrefixeExterne(): void
    {
        // Même contrôle de format que pour un préfixe interne : exactement 3 chiffres.
        $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'     => 'AB1',
                'libelle'     => 'Opérateur invalide',
                'is_internal' => '0',
            ]);

        $model = new PrefixeOperateurModel();
        $this->assertNull(
            $model->where('prefixe', 'AB1')->first(),
            'Un préfixe externe au mauvais format ne doit pas être enregistré.'
        );
    }

    public function testUnPrefixeExterneEnDoublonEstRefuse(): void
    {
        $model = new PrefixeOperateurModel();
        $model->insert(['prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0]);

        $this->withSession(['operateur_id' => 1])
            ->post('admin/prefixes', [
                'prefixe'     => '032',
                'libelle'     => 'Orange (bis)',
                'is_internal' => '0',
            ]);

        $this->assertSame(1, $model->where('prefixe', '032')->countAllResults());
    }

    public function testLaListeSepareCorrectementLesPrefixesInternesEtExternes(): void
    {
        $model = new PrefixeOperateurModel();
        $model->insert(['prefixe' => '034', 'libelle' => 'Telma', 'is_internal' => 1]);
        $model->insert(['prefixe' => '038', 'libelle' => 'Telma', 'is_internal' => 1]);
        $model->insert(['prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0]);
        $model->insert(['prefixe' => '033', 'libelle' => 'Airtel', 'is_internal' => 0]);

        // Reproduit le filtrage utilisé par app/Views/admin/prefixes/index.php
        $prefixes = $model->orderBy('prefixe', 'ASC')->findAll();
        $internes = array_values(array_filter($prefixes, static fn ($p) => (bool) $p['is_internal']));
        $externes = array_values(array_filter($prefixes, static fn ($p) => ! (bool) $p['is_internal']));

        $this->assertCount(2, $internes);
        $this->assertCount(2, $externes);
        $this->assertSame(['032', '033'], array_column($externes, 'prefixe'));
        $this->assertSame(['034', '038'], array_column($internes, 'prefixe'));
    }

    public function testLaPageDeListeAffichesLesDeuxSectionsInterneEtExterne(): void
    {
        $model = new PrefixeOperateurModel();
        $model->insert(['prefixe' => '034', 'libelle' => 'Telma', 'is_internal' => 1]);
        $model->insert(['prefixe' => '032', 'libelle' => 'Orange', 'is_internal' => 0]);

        $result = $this->withSession(['operateur_id' => 1])->get('admin/prefixes');

        $result->assertOK();
        $result->assertSee('Préfixes internes');
        $result->assertSee('Préfixes externes');
    }
}

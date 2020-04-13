<?php
namespace Core\Tests;

use Core\App\Mvc\Entity;
use Core\Classes\Providers\DB\QueryBuilder;
use Core\Classes\Providers\DB\QueryResult;
use Core\Classes\Providers\DB\Sqlite;
use Core\Core;
use Core\Tests\files\Entities\Voiture;
use Core\Tests\files\Entities\Voiture as VoitureEntity;
use Core\Tests\files\Tables\Voiture as VoitureTable;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    protected $dbFile = '';

	public function setUp() : void
	{
     	$core = new Core();
        $this->dbFile = tempnam (__DIR__ . DS . 'files', 'tempdb_');
        copy(__DIR__ . DS . 'files'. DS . 'test.db', $this->dbFile);
	}

    public function tearDown(): void
    {
        unlink($this->dbFile);
    }

    public function testFind()
    {
        $table = new VoitureTable(new Sqlite( $this->dbFile));
        $qb = $table->find();

        $this->assertInstanceOf(QueryBuilder::class,$qb);

        $res = $qb->all();

        //\HTML::print_r($res);

        $this->assertIsArray($res);
        $this->assertCount(3, $res);

        $this->assertInstanceOf(VoitureEntity::class,$res[0]);
        $this->assertEquals('123-AB-456', $res[0]->immat);
    }

    public function testGet()
    {
        $table = new VoitureTable(new Sqlite( $this->dbFile));

        $voiture = $table->get(2);

        $this->assertEquals('789-BB-456', $voiture->immat);
        $this->assertEquals(1, $voiture->marque);
    }

	public function testInsert()
	{
        $table = new VoitureTable(new Sqlite( $this->dbFile));

        $voiture = new Voiture();
        $voiture->marque = 1;
        $voiture->immat = '999-ZZ-888';
        $queryResult = $table->DBInsert($voiture);

        $this->assertInstanceOf(QueryResult::class, $queryResult);
        $this->assertEquals(false, $queryResult->getError());
        $this->assertEquals(1, $queryResult->getNbLigne());
		$this->assertEquals(4, $voiture->id);

		$voitureall = $table->find()->all();
		$this->assertCount(4, $voitureall);

        $voiture2 = $table->get(['id' => 4]);
        $this->assertEquals('999-ZZ-888', $voiture2->immat);
	}

	public function testUpdate()
	{
		$table = new VoitureTable(new Sqlite( $this->dbFile));
		$voiture = $table->get(['id' => 2]);

		$voiture->immat = '777-TT-888';
		$queryResult = $table->DBUpdate($voiture);

		$this->assertInstanceOf(QueryResult::class, $queryResult);
		$this->assertEquals(false, $queryResult->getError());
		$this->assertEquals(1, $queryResult->getNbLigne());

		$voitureall = $table->find()->all();
		$this->assertCount(3, $voitureall);

		$voiture2 = $table->get(['id' => 2]);
		$this->assertEquals('777-TT-888', $voiture2->immat);
	}

	public function testDuplicate()
	{
		$table = new VoitureTable(new Sqlite( $this->dbFile));
		$voiture = $table->get(['id' => 2]);

		$voiture->immat = '777-TT-888';
		$queryResult = $table->DBInsert($voiture);

		$this->assertInstanceOf(QueryResult::class, $queryResult);
		$this->assertEquals(false, $queryResult->getError());
		$this->assertEquals(1, $queryResult->getNbLigne());

		$voitureall = $table->find()->all();
		$this->assertCount(4, $voitureall);

		$voiture2 = $table->get(['id' => 4]);
		$this->assertEquals('777-TT-888', $voiture2->immat);
	}

	public function testFindWithRelations()
	{
		$table = new VoitureTable(new Sqlite( $this->dbFile));
		$qb = $table->find(['with' => ['Marque']]);

		$this->assertInstanceOf(QueryBuilder::class,$qb);

		//\HTML::print_r($qb);

		$res = $qb->all();

		//\HTML::print_r($res);

		$this->assertIsArray($res);
		$this->assertCount(3, $res);

		$this->assertInstanceOf(VoitureEntity::class,$res[0]);
		$this->assertEquals('123-AB-456', $res[0]->immat);

		$this->assertInstanceOf(Entity::class,$res[0]->marque);
		$this->assertEquals('Honda', $res[0]->marque->libelle);
	}

	public function testFindWithRelationsWithRelations()
	{
		$table = new VoitureTable(new Sqlite( $this->dbFile));
		$qb = $table->find(['with' => ['Marque','Marque.Pays']]);

		$this->assertInstanceOf(QueryBuilder::class,$qb);

		//\HTML::print_r($qb);

		$res = $qb->all();

		//\HTML::print_r($res);

		$this->assertIsArray($res);
		$this->assertCount(3, $res);

		$this->assertInstanceOf(VoitureEntity::class,$res[0]);
		$this->assertEquals('123-AB-456', $res[0]->immat);

		$this->assertInstanceOf(Entity::class,$res[0]->marque);
		$this->assertEquals('Honda', $res[0]->marque->libelle);

		$this->assertInstanceOf(Entity::class,$res[0]->marque->pays);
		$this->assertEquals('JAPON', $res[0]->marque->pays->libelle);
	}
}
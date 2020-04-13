<?php
namespace Core\Tests;

use Core\App\Mvc\Entity;
use Core\Core;
use Core\Tests\files\Entities\Voiture;
use Core\Tests\files\Entities\Voiture as VoitureEntity;
use Core\Tests\files\Tables\Voiture as VoitureTable;
use PHPUnit\Framework\TestCase;

class TableChecksTest extends TestCase
{
    protected $dbFile = '';

	public function setUp() : void
	{
     	$core = new Core();
        //$this->dbFile = tempnam (__DIR__ . DS . 'files', 'tempdb_');
        //copy(__DIR__ . DS . 'files'. DS . 'test.db', $this->dbFile);
	}

    public function tearDown(): void
    {
        //unlink($this->dbFile);
    }

	public function testGoodInsert()
	{
		$entity = new VoitureEntity();
		$table = new VoitureTable();

		$entity->marque = 2;
		$entity->immat = '888-AA-456';

		$this->assertTrue($table->checkInsert($entity));

		$this->assertEmpty($entity->getErrors());

	}

	public function testBadInsert()
	{
		$entity = new VoitureEntity();
		$table = new VoitureTable();

		$entity->marque = 'toto';


		$this->assertFalse($table->checkInsert($entity));

		$errors = $entity->getErrors();
		$this->assertArrayHasKey('marque',$errors);
		$this->assertContains('marque n\'est pas un entier',$errors['marque']);

	}
}
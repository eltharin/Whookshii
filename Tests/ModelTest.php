<?php
namespace Tests\Core;

use Core\Tests\files\EntityCategorieTest;
use Core\Tests\files\EntityCompteTest;
use Core\Tests\files\ModelCategorieTest;
use PHPUnit\Framework\TestCase;

abstract class ModelTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new \Core\Core();

		$_ENV['test'] = true;
		\Config::get('Providers')->add('UnitTests',new \Core\Classes\DB\SqliteMemory());
		\Config::get('Providers')->getConfig('UnitTests')->execFile(__DIR__ . DS . 'files/testDb.sql');
	}

	function testSimpleGet()
	{
		$categorieModel = new ModelCategorieTest();

		$data = $categorieModel->getAll();

		var_dump($data[0]);

		$this->assertCount(2,$data);
		$this->assertInstanceOf(EntityCategorieTest::class,$data[0]);
		$this->assertInstanceOf(EntityCategorieTest::class,$data[1]);

		var_dump($data[0]);

		$this->assertEquals('1',$data[0]->id);
		$this->assertEquals(null,$data[0]->toto);
		$this->assertEquals(1,$data[0]->compte);
		$this->assertEquals('Viande',$data[0]->libelle);
		$this->assertEquals('viande.png',$data[0]->image);

	}

	function testGetWithRelation()
	{
		$categorieModel = new ModelCategorieTest();

		$data = $categorieModel->with('compte')->getAll();

		$this->assertCount(2,$data);
		$this->assertInstanceOf(EntityCategorieTest::class,$data[0]);
		$this->assertInstanceOf(EntityCategorieTest::class,$data[1]);

		$this->assertEquals(1,$data[0]->id);
		$this->assertEquals('viande',$data[0]->libelle);
		$this->assertEquals('viande.png',$data[0]->image);

		$this->assertInstanceOf(EntityCompteTest::class,$data[0]->compte);
		$this->assertEquals(1,$data[0]->compte->id);
		$this->assertEquals('Roman',$data[0]->compte->libelle);
	}
}
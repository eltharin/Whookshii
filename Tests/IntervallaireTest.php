<?php
namespace Tests\Core;

use Core\App\Mvc\Entity;
use Core\Classes\Providers\DB\SQLite;
use Core\Tests\files\EntityCategorieTest;
use Core\Tests\files\EntityCompteTest;
use Core\Tests\files\ModelCategorieTest;
use Core\Tests\files\Tables\Intervallaire;
use PHPUnit\Framework\TestCase;

class IntervallaireTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new \Core\Core();

        $_ENV['test'] = true;
        \Config::get('Providers')->add('UnitTests',new SQLite(':memory:'));
        \Config::get('Providers')->getConfig('UnitTests')->execute('CREATE TABLE testintervallaire (
  TES_INT_ITEM INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  TES_INT_COMPTE int(11) NOT NULL,
  TES_INT_BORNE_MIN int(11) NOT NULL,
  TES_INT_BORNE_MAX int(11) NOT NULL,
  TES_INT_NIVEAU int(11) NOT NULL,
  TES_INT_LIBELLE varchar(255) NOT NULL
);');

        \Config::get('Providers')->getConfig('UnitTests')->execute('INSERT INTO testintervallaire (`TES_INT_ITEM`, `TES_INT_COMPTE`, `TES_INT_BORNE_MIN`, `TES_INT_BORNE_MAX`, `TES_INT_NIVEAU`, `TES_INT_LIBELLE`) VALUES
(1, 1, 1, 8, 0, \'Item 1\'),
(2, 1, 2, 3, 1, \'Item 1.1\'),
(3, 1, 4, 5, 1, \'Item 1.2\'),
(4, 1, 6, 7, 1, \'Item 1.3\'),
(5, 1, 9, 14, 0, \'Item 2\'),
(6, 1, 10, 11, 1, \'Item 2.1\'),
(7, 1, 12, 13, 1, \'Item 2.2\'),
(8, 2, 1, 42, 1, \'Item Compte 2\');');
    }

    function testInitialisation()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $data = $inter->find()->all();

       // var_dump($data);

        $this->assertCount(8,$data);
        $this->assertInstanceOf(Entity::class,$data[0]);
        $this->assertInstanceOf(Entity::class,$data[1]);


        $this->assertEquals(1       ,$data[0]->id);
        $this->assertEquals(1       ,$data[0]->borneMin);
        $this->assertEquals(8       ,$data[0]->borneMax);
        $this->assertEquals(0       ,$data[0]->niveau);
        $this->assertEquals('Item 1',$data[0]->libelle);


        $this->assertEquals(6       ,$data[5]->id);
        $this->assertEquals(10      ,$data[5]->borneMin);
        $this->assertEquals(11      ,$data[5]->borneMax);
        $this->assertEquals(1       ,$data[5]->niveau);
        $this->assertEquals('Item 2.1',$data[5]->libelle);
    }

	function testAjout()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

		$inter->addItem(new Entity(['libelle'=>'Item 1.4','compte'=>'1']), 1);

		$data = $inter->find()->all();

		//  var_dump($data);

		// var_dump(\DEBUG::get_sql());

		$this->assertCount(9,$data);

		$this->assertEquals(9       ,$data[8]->id);
		$this->assertEquals(8      ,$data[8]->borneMin);
		$this->assertEquals(9      ,$data[8]->borneMax);
		$this->assertEquals(1       ,$data[8]->niveau);
		$this->assertEquals('Item 1.4',$data[8]->libelle);

		$this->assertEquals(6       ,$data[5]->id);
		$this->assertEquals(12      ,$data[5]->borneMin);
		$this->assertEquals(13      ,$data[5]->borneMax);
		$this->assertEquals(1       ,$data[5]->niveau);
		$this->assertEquals('Item 2.1',$data[5]->libelle);
	}


	function testCreate()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

		$inter->withCallback(function($qb) {$qb->where('TES_INT_COMPTE = 3');});
		$inter->addItem(new Entity(['libelle'=>'Item 1','compte'=>'3']), null);

		$data = $inter->find()->all();

		//  var_dump($data);

		// var_dump(\DEBUG::get_sql());

		$this->assertCount(9,$data);

		$this->assertEquals(9		,$data[8]->id);
		$this->assertEquals(1		,$data[8]->borneMin);
		$this->assertEquals(2		,$data[8]->borneMax);
		$this->assertEquals(0		,$data[8]->niveau);
		$this->assertEquals('Item 1',$data[8]->libelle);
	}


    function testAjoutSub()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $inter->addItem(new Entity(['libelle'=>'Item 1.1.1','compte'=>'1']), 2);

        $data = $inter->find()->all();

        //var_dump($data);
        //var_dump(\DEBUG::get_sql());

        $this->assertEquals(2       ,$data[1]->id);
        $this->assertEquals(2       ,$data[1]->borneMin);
        $this->assertEquals(5      ,$data[1]->borneMax);
        $this->assertEquals(1       ,$data[1]->niveau);

        $this->assertEquals(9       ,$data[8]->id);
        $this->assertEquals(3       ,$data[8]->borneMin);
        $this->assertEquals(4      ,$data[8]->borneMax);
        $this->assertEquals(2       ,$data[8]->niveau);

        $this->assertEquals(3   ,$data[2]->id);
        $this->assertEquals(6  ,$data[2]->borneMin);
        $this->assertEquals(7  ,$data[2]->borneMax);
        $this->assertEquals(1   ,$data[2]->niveau);
    }

    function testSuppr()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $inter->deleteItem($inter->get(4));

        $data = $inter->find()->all();

        //var_dump($data);
        //var_dump(\DEBUG::get_sql());
        $this->assertCount(7,$data);

        $this->assertEquals(5       ,$data[3]->id);
        $this->assertEquals(7       ,$data[3]->borneMin);
        $this->assertEquals(12      ,$data[3]->borneMax);
        $this->assertEquals(0       ,$data[3]->niveau);
        $this->assertEquals('Item 2',$data[3]->libelle);
    }


    function testModifDown()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $inter->moveItem($inter->get(4),5);

        $data = $inter->find()->all();

        //var_dump(\DEBUG::get_sql());
        //var_dump($data);

        $this->assertEquals(4   ,$data[3]->id);
        $this->assertEquals(12  ,$data[3]->borneMin);
        $this->assertEquals(13  ,$data[3]->borneMax);
        $this->assertEquals(1   ,$data[3]->niveau);

        $this->assertEquals(5       ,$data[4]->id);
        $this->assertEquals(7       ,$data[4]->borneMin);
        $this->assertEquals(14      ,$data[4]->borneMax);
        $this->assertEquals(0       ,$data[4]->niveau);
    }

    function testModifup()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $inter->moveItem($inter->get(7),3);

        $data = $inter->find()->all();

        //var_dump($data);
        //var_dump(\DEBUG::get_sql());

        $this->assertEquals(3   ,$data[2]->id);
        $this->assertEquals(4  ,$data[2]->borneMin);
        $this->assertEquals(7  ,$data[2]->borneMax);
        $this->assertEquals(1   ,$data[2]->niveau);

        $this->assertEquals(7       ,$data[6]->id);
        $this->assertEquals(5       ,$data[6]->borneMin);
        $this->assertEquals(6      ,$data[6]->borneMax);
        $this->assertEquals(2       ,$data[6]->niveau);
    }

	function testModifMulti()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

		$inter->moveItem($inter->get(7),3);
		$inter->moveItem($inter->get(3),2);

		$data = $inter->find()->all();

		//var_dump($data);
		//var_dump(\DEBUG::get_sql());

		$this->assertEquals(2	,$data[1]->id);
		$this->assertEquals(2	,$data[1]->borneMin);
		$this->assertEquals(7	,$data[1]->borneMax);
		$this->assertEquals(1	,$data[1]->niveau);

		$this->assertEquals(3	,$data[2]->id);
		$this->assertEquals(3	,$data[2]->borneMin);
		$this->assertEquals(6	,$data[2]->borneMax);
		$this->assertEquals(2	,$data[2]->niveau);

		$this->assertEquals(7	,$data[6]->id);
		$this->assertEquals(4	,$data[6]->borneMin);
		$this->assertEquals(5	,$data[6]->borneMax);
		$this->assertEquals(3	,$data[6]->niveau);
	}

	function testModifMulti2()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));
		$inter->withCallback(function($qb) {$qb->where('TES_INT_COMPTE = 1');});

		$inter->addItem(new Entity(['libelle'=>'Item 1.1.1','compte'=>'1']), 2);

		$inter->moveItem($inter->get(2),3);

		$data = $inter->find()->all();

		//var_dump($data);
		//var_dump(\DEBUG::get_sql());

		$this->assertEquals(1	,$data[0]->id);
		$this->assertEquals(1	,$data[0]->borneMin);
		$this->assertEquals(10	,$data[0]->borneMax);
		$this->assertEquals(0	,$data[0]->niveau);

		$this->assertEquals(3	,$data[2]->id);
		$this->assertEquals(2	,$data[2]->borneMin);
		$this->assertEquals(7	,$data[2]->borneMax);
		$this->assertEquals(1	,$data[2]->niveau);

		$this->assertEquals(2	,$data[1]->id);
		$this->assertEquals(3	,$data[1]->borneMin);
		$this->assertEquals(6	,$data[1]->borneMax);
		$this->assertEquals(2	,$data[1]->niveau);

		$this->assertEquals(9	,$data[8]->id);
		$this->assertEquals(4	,$data[8]->borneMin);
		$this->assertEquals(5	,$data[8]->borneMax);
		$this->assertEquals(3	,$data[8]->niveau);

		$this->assertEquals(4	,$data[3]->id);
		$this->assertEquals(8	,$data[3]->borneMin);
		$this->assertEquals(9	,$data[3]->borneMax);
		$this->assertEquals(1	,$data[3]->niveau);
	}


	function testModifMulti3()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));
		$inter->withCallback(function($qb) {$qb->where('TES_INT_COMPTE = 1');});

		$inter->addItem(new Entity(['libelle'=>'Item 1.1.1','compte'=>'1']), 2);

		$inter->moveItem($inter->get(2),null);

		$data = $inter->find()->all();

		var_dump($data);
		//var_dump(\DEBUG::get_sql());

		$this->assertEquals(1	,$data[0]->id);
		$this->assertEquals(1	,$data[0]->borneMin);
		$this->assertEquals(6	,$data[0]->borneMax);
		$this->assertEquals(0	,$data[0]->niveau);

		$this->assertEquals(4	,$data[3]->id);
		$this->assertEquals(4	,$data[3]->borneMin);
		$this->assertEquals(5	,$data[3]->borneMax);
		$this->assertEquals(1	,$data[3]->niveau);

		$this->assertEquals(5	,$data[4]->id);
		$this->assertEquals(7	,$data[4]->borneMin);
		$this->assertEquals(12	,$data[4]->borneMax);
		$this->assertEquals(0	,$data[4]->niveau);

		$this->assertEquals(2	,$data[1]->id);
		$this->assertEquals(13	,$data[1]->borneMin);
		$this->assertEquals(16	,$data[1]->borneMax);
		$this->assertEquals(0	,$data[1]->niveau);

		$this->assertEquals(9	,$data[8]->id);
		$this->assertEquals(14	,$data[8]->borneMin);
		$this->assertEquals(15	,$data[8]->borneMax);
		$this->assertEquals(1	,$data[8]->niveau);

	}


	function testModifMulti4()
	{
		$inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));
		$inter->withCallback(function($qb) {$qb->where('TES_INT_COMPTE = 1');});

		$inter->addItem(new Entity(['libelle'=>'Item 3','compte'=>'1']), null);
		$inter->addItem(new Entity(['libelle'=>'Item 2.1.1l','compte'=>'1']), 6);

		$inter->moveItem($inter->get(6),3);

		$data = $inter->find()->all();

		var_dump($data);
		//var_dump(\DEBUG::get_sql());

		$this->assertEquals(1	,$data[0]->id);
		$this->assertEquals(1	,$data[0]->borneMin);
		$this->assertEquals(12	,$data[0]->borneMax);
		$this->assertEquals(0	,$data[0]->niveau);

		$this->assertEquals(3	,$data[2]->id);
		$this->assertEquals(4	,$data[2]->borneMin);
		$this->assertEquals(9	,$data[2]->borneMax);
		$this->assertEquals(1	,$data[2]->niveau);

		$this->assertEquals(5	,$data[4]->id);
		$this->assertEquals(13	,$data[4]->borneMin);
		$this->assertEquals(16	,$data[4]->borneMax);
		$this->assertEquals(0	,$data[4]->niveau);

		$this->assertEquals(9	,$data[8]->id);
		$this->assertEquals(17	,$data[8]->borneMin);
		$this->assertEquals(18	,$data[8]->borneMax);
		$this->assertEquals(0	,$data[8]->niveau);

		$this->assertEquals(6	,$data[5]->id);
		$this->assertEquals(5	,$data[5]->borneMin);
		$this->assertEquals(8	,$data[5]->borneMax);
		$this->assertEquals(2	,$data[5]->niveau);

		$this->assertEquals(10	,$data[9]->id);
		$this->assertEquals(6	,$data[9]->borneMin);
		$this->assertEquals(7	,$data[9]->borneMax);
		$this->assertEquals(3	,$data[9]->niveau);
	}

    function testModifWithCompte()
    {
        $inter = new Intervallaire(\Config::get('Providers')->getConfig('UnitTests'));

        $inter->withCallback(function($qb) {$qb->where('TES_INT_COMPTE = 1');});
        $inter->addItem(new Entity(['libelle'=>'Item 1.1.1','compte'=>'1']), 2);
        $inter->moveItem($inter->get(7),3);

        $data = $inter->find()->all();

        //var_dump($data);
        //var_dump(\DEBUG::get_sql());

        $this->assertEquals(3   ,$data[2]->id);
        $this->assertEquals(6  ,$data[2]->borneMin);
        $this->assertEquals(9  ,$data[2]->borneMax);
        $this->assertEquals(1   ,$data[2]->niveau);

        $this->assertEquals(7       ,$data[6]->id);
        $this->assertEquals(7       ,$data[6]->borneMin);
        $this->assertEquals(8      ,$data[6]->borneMax);
        $this->assertEquals(2       ,$data[6]->niveau);

        $this->assertEquals(8       ,$data[7]->id);
        $this->assertEquals(1       ,$data[7]->borneMin);
        $this->assertEquals(42      ,$data[7]->borneMax);
        $this->assertEquals(1       ,$data[7]->niveau);
    }
}
<?php
namespace Core\Tests;

use Core\Core;
use PHPUnit\Framework\TestCase;
use Core\Classes\DB\QueryBuilder;

class QueryBuilderTest extends TestCase
{
	public function setUp() : void
	{
		$core = new Core();
	}

	public function testSelect()
	{
		$qb = new QueryBuilder();

		$qb->select('champ1, champ2')
			->select('champ3')
			->from('table1 t1')
			->ijoin('table2 t2','t1.champ1 = t2.champ3')
			->ljoin('table3 t3','t1.champ2 = t3.champ4')
			->where(['champ5' => '12', 'champ6' => 'toto'])
			->where('champ10 = 2')
			->where('champ7 = :var',['var'=>'tata']);

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('SELECT champ1, champ2 , champ3 FROM table1 t1 INNER JOIN table2 t2 ON t1.champ1 = t2.champ3 LEFT JOIN table3 t3 ON t1.champ2 = t3.champ4 WHERE champ5 = :champ5 AND champ6 = :champ6 AND champ10 = 2 AND champ7 = :var',$qb->getQuery());
		$this->assertEquals(['champ5' => '12', 'champ6' => 'toto','var' => 'tata'], $qb->getParams());
	}

	public function testInsert1()
	{
		$qb = new QueryBuilder();

		$qb->insert('table1')
		   ->set('champ1','toto')
		   ->set(['champ2'=> 'tata', 'champ3'=>'tutu'])
		;

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('INSERT INTO table1 (champ1, champ2, champ3) VALUES (:set_champ1, :set_champ2, :set_champ3)',$qb->getQuery());
		$this->assertEquals(['set_champ1' => 'toto', 'set_champ2' => 'tata','set_champ3' => 'tutu'], $qb->getParams());
	}

	public function testInsert2()
	{
		$qb = new QueryBuilder();

		$qb->insert()
			->from('table1')
		   ->set('champ1','toto')
		   ->set(['champ2'=> 'tata', 'champ3'=>'tutu'])
		;

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('INSERT INTO table1 (champ1, champ2, champ3) VALUES (:set_champ1, :set_champ2, :set_champ3)',$qb->getQuery());
		$this->assertEquals(['set_champ1' => 'toto', 'set_champ2' => 'tata','set_champ3' => 'tutu'], $qb->getParams());
	}

	public function testUpdate1()
	{
		$qb = new QueryBuilder();

		$qb->update('table1')
		   ->set('champ1','toto')
		   ->set(['champ2'=> 'tata', 'champ3'=>'tutu'])
			->where(['champ1' => 'bloup'])
		;

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('UPDATE table1 SET champ1 = :set_champ1, champ2 = :set_champ2, champ3 = :set_champ3 WHERE champ1 = :champ1',$qb->getQuery());
		$this->assertEquals(['set_champ1' => 'toto', 'set_champ2' => 'tata','set_champ3' => 'tutu','champ1' => 'bloup',], $qb->getParams());
	}

	public function testDelete()
	{
		$qb = new QueryBuilder();

		$qb->delete('table1')
		   ->where('champ1 = :champ1',['champ1' => 'toto'])
		   ->where(['champ2'=> 'tata', 'champ3'=>'tutu'])
		;

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('DELETE FROM table1 WHERE champ1 = :champ1 AND champ2 = :champ2 AND champ3 = :champ3',$qb->getQuery());
		$this->assertEquals(['champ1' => 'toto', 'champ2' => 'tata','champ3' => 'tutu'], $qb->getParams());
	}

	public function testReplace()
	{
		$qb = new QueryBuilder();

		$qb->replace()
		   ->from('table1')
		   ->set('champ1','toto')
		   ->set(['champ2'=> 'tata', 'champ3'=>'tutu'])
		;

		$this->assertInstanceOf(QueryBuilder::class, $qb);
		$this->assertEquals('REPLACE INTO table1 (champ1, champ2, champ3) VALUES (:set_champ1, :set_champ2, :set_champ3)',$qb->getQuery());
		$this->assertEquals(['set_champ1' => 'toto', 'set_champ2' => 'tata','set_champ3' => 'tutu'], $qb->getParams());
	}
}
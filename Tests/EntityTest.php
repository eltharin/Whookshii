<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;

require 'files/EntityUserTest.php';
require 'files/EntityGroupeTest.php';

class EntityTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new \Core\Core();

	}

	public function testBasic()
	{
		$user = new \Tests\Core\EntityUser();

		$user->setName('John');
		$user->setLastName('Doe');
		$user->setAge(45);

		$this->assertEquals('John', $user->getName());
		$this->assertEquals('Doe' , $user->getLastName());
		$this->assertEquals(45 ,$user->getAge());
	}

	public function testBasicError()
	{
		$user = new \Tests\Core\EntityUser();
		$this->expectNotice();
		$this->expectNoticeMessage('varDoesntExist not found in Object');

		$user->getVarDoesntExist();
	}

	public function testBasicWithLink()
	{
		$user = new \Tests\Core\EntityUser();
		$groupe = new \Tests\Core\EntityGroupe();

		$groupe->setId(2);
		$groupe->setName('Toto');

		$user->setName('John');
		$user->setLastName('Doe');
		$user->setGroupe($groupe);

		$this->assertEquals('John', $user->getName());
		$this->assertEquals('Doe' , $user->getLastName());
		//$this->assertEquals('2' ,$user->getGroupe());
		$this->assertEquals(2 ,$user->getGroupe()->getId());
		$this->assertEquals('Toto' ,$user->getGroupe()->getName());
	}

	public function testBasicWithProperties()
	{
		$user = new \Tests\Core\EntityUser();

		$user->setName('John');
		$user->setLastName('Doe');
		$user->setAge(45);
		$user->setFieldDoesntExist('Valeur');

		$this->assertEquals('John', $user->getName());
		$this->assertEquals('Doe' , $user->getLastName());
		$this->assertEquals(45 ,$user->getAge());

		$reflection = new \ReflectionClass( get_class($user));
		$fields = $reflection->getProperty("properties");
		$fields->setAccessible(true);

		$this->assertArrayHasKey('fieldDoesntExist',$fields->getValue($user));
		$this->assertEquals('Valeur' ,$user->getFieldDoesntExist());
	}

	public function testModifiedValues()
	{
		$user = new \Tests\Core\EntityUser([
				'name' => 'John',
				'lastName' => 'Doe',
		]);

		\HTML::print_r($user);

		$this->assertEquals('John', $user->getName());
		$this->assertEquals('Doe' , $user->getLastName());
		$this->assertFalse($user->isModified());

		$user->setAge(45);
		$this->assertEquals(45 , $user->getAge());
		$this->assertTrue($user->isModified());

		$user->setName('Jim');
		$this->assertEquals('Jim', $user->getName());
		$this->assertEquals('John', $user->oldName());

	}

	/*
	public function testFieldsInserted()
	{
		$reflection = new \ReflectionClass( get_class($this->entity1));
		$fields = $reflection->getProperty("fields");
		$fields->setAccessible(true);

		$this->assertArrayHasKey('nom',$fields->getValue($this->entity1));
		$this->assertArrayHasKey('prenom',$fields->getValue($this->entity1));
		$this->assertArrayHasKey('age',$fields->getValue($this->entity1));
		$this->assertArrayNotHasKey('clenonpresente',$fields->getValue($this->entity1));
	}

	public function testValuesInserted()
	{
		$this->entity1->age = 12;
		$this->entity1->nom = 'mon_nom';
		$this->entity1->clequinexistepas = 'j\{ai le droit d\'exister';

		$this->assertEquals(12, $this->entity1->age);
		$this->assertEquals('mon_nom', $this->entity1->nom);
		$this->assertEquals('j\{ai le droit d\'exister', $this->entity1->clequinexistepas);
	}

	public function testDefaultValue()
	{
		$this->entity1->getDefaultValues();



		$this->assertEquals(0, $this->entity1->age);
		$this->assertEquals('mon_nom', $this->entity1->nom);
		$this->assertEquals(null, $this->entity1->prenom);
	}


	public function testValidateValues()
	{
		$this->entity1->age = 'valeur_incorrecte';
		$this->entity1->nom = 'mon_nom';

		$this->assertEquals(false, $this->entity1->validate());
		$this->assertArrayHasKey('age',$this->entity1->getErrors());

		$this->entity1->age = '12';
		$this->assertEquals(true, $this->entity1->validate());
		$this->assertEquals(null,$this->entity1->getErrors());

		$this->entity1->age = '-5';
		$this->assertEquals(false, $this->entity1->validate());
		$this->assertArrayHasKey('age',$this->entity1->getErrors());
	}

	public function testRenderValues()
	{
		$reflection = new \ReflectionClass( get_class($this->entity1));
		$fields = $reflection->getProperty("fields");
		$fields->setAccessible(true);

		$this->entity1->created_at = '31/08/1986';
		$this->assertEquals('31/08/1986', $this->entity1->created_at);
		$this->assertEquals('525830400', ($fields->getValue($this->entity1))['created_at']);
	}*/

}
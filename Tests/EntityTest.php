<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;

require 'files/EntityTest1.php';

class EntityTest extends TestCase
{
	private $entity1;

	public function setUp(): void
	{
		$this->app = new \Core\Core();
		$this->entity1 = new \Tests\Core\EntityTest1();
	}

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

	public

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
	}

}
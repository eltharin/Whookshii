<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;

require 'files/Entities/EntityUserTest.php';
require 'files/Entities/EntityGroupeTest.php';

class EntityTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new \Core\Core();

	}

	public function testBasic()
	{
		$user = new \Tests\Core\EntityUser();

		$user->name = 'John';
		$user->lastName = 'Doe';
		$user->age = 45;

		$this->assertEquals('John', $user->name);
		$this->assertEquals('Doe' , $user->lastName);
		$this->assertEquals(45 ,$user->age);
	}

	public function testBasicWithLink()
	{
		$user = new \Tests\Core\EntityUser();
		$groupe = new \Tests\Core\EntityGroupe();

		$groupe->id = 2;
		$groupe->name = 'Toto';

		$user->name = 'John';
		$user->lastName = 'Doe';
		$user->groupe = $groupe;

		$this->assertEquals('John', $user->name);
		$this->assertEquals('Doe' , $user->lastName);
		$this->assertEquals(2 ,$user->groupe->id);
		$this->assertEquals('Toto' ,$user->groupe->name);
	}

	public function testModifiedValues()
	{
		$user = new \Tests\Core\EntityUser([
				'name' => 'John',
				'lastName' => 'Doe',
		]);

		\HTML::pr($user);

		$this->assertEquals('John', $user->name);
		$this->assertEquals('Doe' , $user->lastName);
		$this->assertFalse($user->isModified());

		$user->age = 45;

		\HTML::pr($user);

		$this->assertEquals(45 , $user->age);
		$this->assertTrue($user->isModified());

		$user->name = 'Jim';
		$this->assertEquals('Jim', $user->name);
		$this->assertEquals('John', $user->getOldValue('name'));

	}
}
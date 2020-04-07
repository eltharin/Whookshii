<?php
namespace Tests\Core;

use Core\App\Mvc\Entity;

class EntityUser extends Entity
{
	public function init() : void
	{
		$this->addField('id',[]);
		$this->addField('name',[]);
		$this->addField('lastName',[]);
		$this->addField('age',[]);
		$this->addField('groupe',[]);
	}
}
<?php
namespace Tests\Core;

use Core\App\Mvc\Entity;

class EntityGroupe extends Entity
{
	public function init() : void
	{
		$this->key = 'id';

		$this->addField('id',[]);
		$this->addField('name',[]);
	}
}
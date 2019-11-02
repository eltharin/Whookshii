<?php
namespace Tests\Core;

use Core\App\Mvc\Entity;

class EntityTest1 extends Entity
{

	private $toto = 2;

	public function setFields() : void
	{
		$this->addField('id');
		$this->addField('nom',['default' => 'mon_nom']);
		$this->addField('prenom');
		$this->addField('age',['default' => 0]);
		$this->addField('created_at', ['type' => 'date']);
	}


}
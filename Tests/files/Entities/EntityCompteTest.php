<?php
namespace Core\Tests\files;

use Core\App\Mvc\Entity;

class EntityCompteTest extends Entity
{

	public function init() : void
	{
		$this->addField('id',['field' => 'CON_COM_COMPTE']);
		$this->addField('libelle',['field' => 'CON_COM_LIBELLE']);
	}
}

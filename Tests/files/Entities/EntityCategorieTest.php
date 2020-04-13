<?php
namespace Core\Tests\files;

use Core\App\Mvc\Entity;

class EntityCategorieTest extends Entity
{

	public function init() : void
	{
		$this->addField('id',['field' => 'CON_CAT_CATEGORIE']);
		$this->addField('compte',['field' => 'CON_CAT_COMPTE']);
		$this->addField('libelle',['field' => 'CON_CAT_LIBELLE']);
		$this->addField('image',['field' => 'CON_CAT_IMAGE']);
	}
}

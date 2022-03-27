<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;

class Pays extends Table
{
	public function init()
	{
		$this->table = 'pays';
		$this->fieldPrefixe = 'VOI_PAY_';
		$this->fieldForce   = 'Camel';

		$this->addField('VOI_PAY_PAYS'      , ['PK' => 'AI', 'entityField' => 'id']);
		$this->addField('VOI_PAY_LIBELLE' , []);
	}
}
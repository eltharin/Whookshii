<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;
use Core\App\Mvc\TableLink\Rel;

class Marque extends Table
{
	public function init()
	{
		$this->table = 'marque';
		$this->fieldPrefixe = 'VOI_MA_';
		$this->fieldForce   = 'Camel';

		$this->addField('VOI_MA_MARQUE'      , ['PK' => 'AI', 'entityField' => 'id']);
		$this->addField('VOI_MA_PAYS' , []);
		$this->addField('VOI_MA_LIBELLE' , []);

		$this->addLink('Pays', new Rel(['table' => Pays::class, 'joinOn' => [ 'FK' => 'VOI_MA_PAYS', 'PK' => 'VOI_PAY_PAYS']]));
	}
}
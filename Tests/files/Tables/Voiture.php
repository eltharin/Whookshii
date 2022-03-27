<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;
use Core\App\Mvc\TableLink\Rel;

class Voiture extends Table
{
	protected $entityClassName = \Core\Tests\files\Entities\Voiture::class;

	public function init()
	{
		$this->table = 'voiture';
		$this->fieldPrefixe = 'VOI_VOI_';
		$this->fieldForce   = 'Camel';

		$this->addField('VOI_VOI_VOITURE'      , ['PK' => 'AI', 'entityField' => 'id']);
		$this->addField('VOI_VOI_MARQUE' , []);
		$this->addField('VOI_VOI_IMMATRICULATION' , ['entityField' => 'immat']);

		$this->addLink('Marque' , new Rel(['table' => Marque::class, 'joinOn' => ['FK' => 'VOI_VOI_MARQUE', 'PK' => 'VOI_MA_MARQUE']]));
	}
}
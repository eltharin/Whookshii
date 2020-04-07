<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;

class Voiture extends Table
{
	protected $entityClassName = \Core\Tests\files\Entities\Voiture::class;

	public function init()
	{
		$this->fields = [
			'VOI_VOI_VOITURE' => ['entityField' => 'id'],
			'VOI_VOI_MARQUE'  => ['entityField' => 'marque'],
			'VOI_VOI_IMMATRICULATION' => ['entityField' => 'immat'],
		];

		$this->links = [
			'Marque' => ['type' => 'rel', 'table' => Marque::class, 'joinOn' => ['FK' => 'VOI_VOI_MARQUE', 'PK' => 'VOI_MA_MARQUE']]
			];


		$this->PKs = ['VOI_VOI_VOITURE'];
		$this->PKAI = 'VOI_VOI_VOITURE';
	}
}
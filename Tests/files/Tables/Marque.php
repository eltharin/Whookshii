<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;

class Marque extends Table
{
	public function init()
	{
		$this->fields = [
			'VOI_MA_MARQUE' 	=> ['entityField' => 'id'],
			'VOI_MA_PAYS'  		=> ['entityField' => 'pays'],
			'VOI_MA_LIBELLE'  	=> ['entityField' => 'libelle'],
		];

		$this->links = [
			'Pays' => ['type' => 'rel', 'table' => Pays::class, 'joinOn' => [ 'FK' => 'VOI_MA_PAYS', 'PK' => 'VOI_PAY_PAYS']]
		];

		$this->PKs = ['VOI_MA_MARQUE'];
		$this->PKAI = 'VOI_MA_MARQUE';
	}
}
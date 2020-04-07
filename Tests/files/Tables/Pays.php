<?php
namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;

class Pays extends Table
{
	public function init()
	{
		$this->fields = [
			'VOI_PAY_PAYS' 		=> ['entityField' => 'id'],
			'VOI_PAY_LIBELLE'  	=> ['entityField' => 'libelle'],
		];

		$this->PKs = ['VOI_PAY_PAYS'];
		$this->PKAI = 'VOI_PAY_PAYS';
	}
}
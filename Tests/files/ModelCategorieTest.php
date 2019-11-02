<?php
namespace Core\Tests\files;

use Core\App\Mvc\Model;

class ModelCategorieTest extends Model
{
	protected $provider = 'UnitTests';
	protected $table = 'categorie';
	protected $entity = EntityCategorieTest::class;

	public function init()
	{
		$this->AddRelation('compte',[	'type' => 'Entity',
										'class' => EntityCompteTest::class,
								]);
	}

	public function with($links)
	{
		if(!is_array($links))
		{
			$links = [$links];
		}


	}
}
<?php

namespace Core\App\Mvc;

class Service
{
	/**
	 * @var Table
	 */
	protected $tableClass = null;

	final public function __construct($provider = null)
	{
		/*$rfl = new \ReflectionClass($this);

		$this->queryPrefixe  = $rfl->getShortName();
		$this->table = strtolower($rfl->getShortName());
*/
		$this->init();

	}

	public function init() {}

	public function findAll()
	{
		return (new $this->tableClass)->findWithRel();
	}

	/*public function add(Array $data)
	{
		$entite = new Entity();
		$table  = new \Specs\Tables\Type();

		$ret = new \stdClass();
		$ret->plant = $entite;
		$ret->errors    = [];

		foreach($table->getFields() as $field)
		{
			if(isset($field['PK']))
			{
				continue;
			}

			$entite->{$field['entityField']} = $data[$field['entityField']] ?? $field['defaultValue'] ?? '';
		}

		$result = $table->DBInsert($entite);

		if($result->hasError())
		{
			$ret->errors[] = 'Impossible d\'ajouter le plant';
			$ret->errors[] = implode(' - ' , $result->getError());
			return $ret;
		}

		return $ret;
	}*/
}
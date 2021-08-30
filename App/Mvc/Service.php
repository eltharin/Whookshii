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

	public function expects($args, $cond) : \Core\App\Mvc\ServicesQR\Reponse2
	{
		$reponse = (new \Core\App\Mvc\ServicesQR\Reponse2);

		foreach($cond as $key => $list)
		{
			foreach($list as $l)
			{
				switch($l)
				{
					case 'required' : if(!array_key_exists($key, $args)) {$reponse->addError ($key . ' est requis');};break;
					case 'float'    : if(!is_numeric($args[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas un nombre');};break;
					case 'int'      : if(!is_int($args[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas un entier');};break;
					case 'int'      : if(!is_string($args[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas une chaine de caractères');};break;
					case 'notEmpty' : if(empty($args[$key] ?? null)) {$reponse->addError ($key . ' est vide');};break;
					default : throw new \Exception ($l . ' is not a valid condition');
				}
			}
		}

		return $reponse;
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
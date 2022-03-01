<?php

namespace Core\App\Mvc;

use Core\App\Exception\HttpException;

class Service
{
	/**
	 * @var Table
	 */
	protected static $tableClass = null;

	final public function __construct($provider = null)
	{
		trigger_error('service ' . get_called_class() . ' non static in ' . implode(BRN,
				array_map(function($a){return $a['function'] . ' in ' . ($a['file']??'') . '(' . ($a['line']??0) . ')';}, debug_backtrace())),E_USER_NOTICE);
		/*$rfl = new \ReflectionClass($this);

		$this->queryPrefixe  = $rfl->getShortName();
		$this->table = strtolower($rfl->getShortName());
*/
		$this->init();

	}

	public function init() {}

	public static function findAll()
	{
		return (new static::$tableClass)->findWithRel();
	}

	public static function expects($data, $cond) : \Core\App\Mvc\ServicesQR\Result
	{
		$reponse = (new \Core\App\Mvc\ServicesQR\Result);

		foreach($cond as $key => $list)
		{
			foreach($list as $l)
			{
				if(is_callable($l))
				{
					$ret = call_user_func ($l, $data);
					if($ret != '')
					{
						$reponse->addError ($ret);
					}
				}
				else
				{
					switch($l)
					{
						case 'required' : if(!array_key_exists($key, $data)) {$reponse->addError ($key . ' est requis');};break;
						case 'float'    : if(!is_numeric($data[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas un nombre');};break;
						case 'int'      : if(!is_int($data[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas un entier');};break;
						case 'int'      : if(!is_string($data[$key] ?? null)) {$reponse->addError ($key . ' n\'est pas une chaine de caractères');};break;
						case 'notEmpty' : if(empty($data[$key] ?? null)) {$reponse->addError ($key . ' est vide');};break;
						default : throw new \Exception ($l . ' is not a valid condition');
					}
				}
			}
		}

		return $reponse;
	}

	public static function expectsOrDie(array $data, array $array)
	{
		$check = static::expects ($data, $array);

		if($check->hasErrors ())
		{
			throw new HttpException('Arguments incorrects : ' . implode(BRN, $check->getErrors ()), 500);
		}
	}
}
<?php

namespace Core\App\Mvc;

class Hydrator
{
	private $objectClass = null;
	private $fields = [];
	private $etages = [];
	private $racine = null;

	public function setObjectClass(String $class)
	{
		$this->objectClass = $class;
	}

	public function addEtage(string $getPrefixe, string $classObj, String $parent, string $linkOn = '')
	{
		$this->etages[$getPrefixe] = ['classObj' => $classObj, 'parent' => $parent, 'linkOn' => $linkOn];
		return $this;
	}

	public function addField($field, $propertyName = null, $parent = '', callable $callback = null, ?string $defaultValue = '')
	{
		if(is_array($field))
		{
			$this->fields = array_merge($this->fields, $field);
		}
		else
		{
			$this->fields[$field] = ['propertyName' => $propertyName, 'parent' => $parent, 'callback' => $callback, 'defaultValue' => $defaultValue];
		}
		return $this;
	}

	public function hydrate($data)
	{
		if(empty($this->etages) || empty($this->racine) || !isset($this->etages[$this->racine]['classObj']))
		{
			return $data;
		}

		$objs = [$this->racine => new ($this->etages[$this->racine]['classObj'])()];

		foreach($this->etages as $etageName => $etage)
		{
			if(!array_key_exists($etageName, $objs))
			{
				$objs[$etageName] = new ($this->etages[$etageName]['classObj'])();
			}

			if($etage['parent'] != '' && !array_key_exists($etage['parent'], $objs))
			{
				$objs[$etage['parent']] = new ($this->etages[$etage['parent']]['classObj'])();
			}
		}

		foreach($data as $key => $val)
		{
			if(!array_key_exists ($key, $this->fields))
			{
				$objs[$this->racine]->$key = $val;
			}
			else
			{
				if(array_key_exists ($this->fields[$key]['parent'], $this->etages))
				{
					$racine = $this->fields[$key]['parent'];
				}
				else
				{
					$racine = $this->racine;
				}

				if($this->fields[$key]['callback'] !== null)
				{
					$val = call_user_func ($this->fields[$key]['callback'], $val);
				}
			/*	if(!array_key_exists($racine, $objs))
				{
					$objs[$racine] = new ($this->etages[$racine]['classObj'])();
				}*/

				if($this->fields[$key]['propertyName'] != '')
				{
					$key = $this->fields[$key]['propertyName'];
				}
				$objs[$racine]->$key = $val;
			}
		}

		foreach($objs as $racine => $objet)
		{
			if($racine != $this->racine)
			{
				if(!array_key_exists ($this->etages[$racine]['parent'], $this->etages))
				{
					$this->etages[$racine]['parent'] = $this->racine;
				}
				//$parent = end($this->etages[$racine]['parents']);
				$parent = $this->etages[$racine]['parent'];
				$linkOn = $this->etages[$racine]['linkOn'] ?: $racine;

				if(isset($objs[$parent]->$linkOn))
				{
					$objet->setRemplacementValue($objs[$parent]->$linkOn);
				}
				$objs[$parent]->$linkOn = $objet;
			}
		}

		$obj = $objs[$this->racine];

		return $obj;
	}

	public function setRacine(string $getPrefixe, string $classObj)
	{
		$this->addEtage ($getPrefixe, $classObj, '');
		$this->racine = $getPrefixe;
	}

	public function createEmpty() : Entity
	{
		return $this->hydrate(array_map(function ($a) {return $a['defaultValue'];}, $this->fields));
	}

    public function clean()
    {
        $this->objectClass = null;
        $this->fields = [];
        $this->etages = [];
        $this->racine = null;
    }
}
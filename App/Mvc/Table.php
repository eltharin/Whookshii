<?php

namespace Core\App\Mvc;

use Core\Classes\Providers\DB\QueryBuilder;
use Core\Classes\Providers\DB\QueryResult;

class Table
{
	public $provider        	= null;
	protected $entityClassName 	= null;
	protected $table           	= '';
	protected $fields			= [];
	protected $fieldPrefixe     = '';
	protected $fieldForce       = null;

	protected $queryPrefixe     = '';
	protected $links 			= [];

	protected $PKs 				= [];
	protected $PKAI 			= null;

	protected $fieldsToProperties = [];
	protected $propertiesToFields = [];

	protected $rel = [];

	final public function __construct($provider = null)
	{
		$rfl = new \ReflectionClass($this);

		$this->provider = $provider ?? \Config::get('Providers')->getConfig('default');
		$this->queryPrefixe  = $rfl->getShortName();
		$this->table = $rfl->getShortName();
		$this->init();

		foreach($this->fields as $key => $field)
		{
			if(!isset($field['entityField']))
			{
				$field['entityField'] = $key;
			}

			$this->fieldsToProperties[$key] = $field['entityField'];
			$this->propertiesToFields[$field['entityField']] = $key;
		}
	}

	public function __debugInfo() {
		return [];
	}

	public function init() { }

	public function getTable($forSQL = false)
	{
		return $this->table . ' as ' . $this->queryPrefixe;
	}

	public function setPrefixe($prefixe)
	{
		$this->queryPrefixe = $prefixe;
	}

	public function getPrefixe()
	{
		return $this->queryPrefixe;
	}

	public function getPrefixedField($field)
	{
		return $this->queryPrefixe . '__' . $field;
	}

	public function getEntityClassName()
	{
		return $this->entityClassName;
	}

	public function addField($fieldName, $params=[])
	{
		if(in_array('PK',$params))
		{
			$this->PKs[] = $fieldName;
			if(in_array('AI',$params))
			{
				$this->PKAI = $fieldName;
			}
		}

		if($this->fieldPrefixe == '')
		{
			$this->fields[$fieldName] = $params;
			return;
		}

		if(substr($fieldName,0,strlen($this->fieldPrefixe)) == $this->fieldPrefixe)
		{
			$entityField = substr($fieldName,strlen($this->fieldPrefixe));

			if($this->fieldForce !== null)
			{
				if($this->fieldForce == 'Camel')
				{
					$entityField = lcfirst(ucwords(strtolower($entityField)));
				}
				elseif(is_callable($this->fieldForce))
				{
					$entityField = call_user_func($this->fieldForce, $entityField);
				}
			}
			$this->fields[$fieldName] = array_merge(['entityField' => $entityField], $params);
			return;
		}

		$this->fields[$fieldName] = $params;
		return;
	}

	protected function addLink($linkNAme, $params)
	{
		$this->links[$linkNAme] = $params;
	}

	protected function newQueryBuilder()
	{
		$qb = new QueryBuilder($this->provider);
		$qb->setCallback([$this, 'hydrateEntity']);

		return $qb;
	}

	public function find($params = [])
	{
		$qb = $this->newQueryBuilder();
		$qb->select($this->getAllFields())->from($this->getTable(true));

		if(isset($params['with']))
		{
			$arrWith = [];
			foreach($params['with'] as $with)
			{
				$arr = explode('.' , $with);

				$container = &$arrWith;

				foreach($arr as $k => $a)
				{
					if(!isset($container[$a]))
					{
						$container[$a] = ['show' => ($k == count($arr)-1), 'data' => []];
					}
					$container = &$container[$a]['data'];
				}
			}
			$this->getJoins($arrWith, $qb);
		}

		return $qb;
	}

	public function findWithRel()
	{
		return $this->find(['with' => $this->getRelationNames()]);
	}

	public function getRelationNames($parent='')
	{
		$ret = [];
		foreach($this->links as $name => $link)
		{
			if($parent != '')
			{
				$name = $parent . '.' . $name;
			}
			$ret[] = $name;
			$ret = array_merge($ret, (new $link['table'])->getRelationNames($name));
		}
		return $ret;
	}

	public function getJoins(array $relation, QueryBuilder $qb)
	{
		foreach($relation as $relName => $rel)
		{
			if(!isset($this->links[$relName]))
			{
				throw new \Exception('Relation ' . $relName . ' inconnue');
			}

			$myRel = $this->links[$relName];

			if($myRel['type'] == 'rel')
			{
				$tmpClass = new $myRel['table']($this->provider);
				$tmpClass->setPrefixe($this->queryPrefixe . '__' . $relName);

				if($rel['show'] == true)
				{
					if(!isset($myRel['fields']))
					{
						$qb->select($tmpClass->getAllFields());
					}
					else
					{
						foreach($myRel['fields'] as $f)
						{
							$qb->select($this->queryPrefixe . '.' . $f . ' ' . $this->getPrefixedField($f));
						}
					}
				}

				$joinOn = $this->getPrefixe() . '.' . $myRel['joinOn']['FK'] . ' = ' . $tmpClass->getPrefixe() . '.' . $myRel['joinOn']['PK'];


				if($myRel['join']??'left' == 'inner')
				{
					$qb->ijoin($tmpClass->getTable(true), $joinOn);
				}
				else
				{
					$qb->ljoin($tmpClass->getTable(true), $joinOn);
				}

				unset($joinOn);

				$this->rel[$tmpClass->getPrefixedField('')] = [
					'relation' => $relName,
					'object' => $tmpClass,
					'FK' => $myRel['joinOn']['FK'],
					'data' => new \stdClass()
				];
				$tmpClass->getJoins($rel['data'],$qb);
				unset($tmpClass);
			}
		}
	}

	public function get($pks)
	{
		$qb = $this->newQueryBuilder();
		$qb->select($this->getAllFields())
		   ->from($this->table);

		if(!is_array($pks))
		{
			$pks = [$pks];
		}

		foreach(array_combine($this->PKs,$pks) as $key => $val)
		{
			$qb->where([$key => $val]);
		}

		return $qb->first();
	}

	public function getAllFields()
	{
		$ret = [];
		foreach($this->fields as $key => $val)
		{
			$ret[] = $this->queryPrefixe . '.' . $key . ' ' . $this->getPrefixedField($key);
		}
		return implode(', ', $ret);
	}

	public function getFieldFromProperty($property) : string
	{
		return $this->propertiesToFields[$property];
	}

	public function getPropertyFromField($field) : string
	{
		return $this->fieldsToProperties[$field];
	}

	public function hydrateEntity($data)
	{
		foreach($data as $k => $v)
		{
			if(substr($k,0,strlen($this->getPrefixe())) == $this->getPrefixe() && isset($this->rel[substr($k,0,strpos($k,'__',strlen($this->getPrefixe())+3)+2)]))
			{
				$this->rel[substr($k,0,strpos($k,'__',strlen($this->getPrefixe())+3)+2)]['data']->$k = $v;
				unset($data->$k);
			}
		}

		foreach($this->fields as $key => $val)
		{
			$prefixedKey = $this->getPrefixedField($key);
			if(isset($data->$prefixedKey))
			{
				if(isset($val['entityField']))
				{
					$data->{$val['entityField']} = $data->$prefixedKey;
				}
				else
				{
					$data->{$key} = $data->$prefixedKey;
				}
				unset($data->$prefixedKey);
			}
		}

		foreach($this->rel as $rel)
		{
			$oldVal = $data->{$this->getPropertyFromField($rel['FK'])};
			$data->{$this->getPropertyFromField($rel['FK'])} = $rel['object']->hydrateEntity($rel['data']);
			$data->{$this->getPropertyFromField($rel['FK'])}->__id = $oldVal;
		}

		$entityClass = $this->entityClassName ?? Entity::class;
		return new $entityClass($data);
	}

	public function createEmpty()
	{
		$data = [];

		foreach($this->fields as $key => $field)
		{
			if(!isset($field['entityField']))
			{
				$field['entityField'] = $key;
			}
			$data[$field['entityField']] = $field['defaultValue'] ?? '';
		}

		return $this->hydrateEntity($data);
	}

	public function checkInsert(Entity $entity)
	{
		return true;
	}

	public function checkUpdate(Entity $entity)
	{
		return true;
	}

	public function checkAll(Entity $entity)
	{
		return true;
	}

	public function DBInsert(Entity $entity) : QueryResult
	{
		$qb = $this->newQueryBuilder()
					->insert($this->table);

		foreach($this->fields as $key => $val)
		{
			if($key == $this->PKAI)
			{
				continue;
			}

			$qb->set($key, $entity->{$this->getPropertyFromField($key)});
		}

		$result = $qb->exec();

		if($this->PKAI !== null)
		{
			$entity->{$this->getPropertyFromField($this->PKAI)} = $qb->lastInsertId();
		}

		return $result;
	}

	public function DBUpdate(Entity $entity) : QueryResult
	{
		$qb = $this->newQueryBuilder()
				   ->update($this->table);

		foreach($this->fields as $field => $val)
		{
			if(in_array($field, $this->PKs) || !isset($entity->{$this->getPropertyFromField($field)}))
			{
				//@TODO: rajouter si le champs n'est pas modifié
				continue;
			}

			if($entity->{$this->getPropertyFromField($field)} instanceof Entity)
			{
				$qb->set($field, $entity->{$this->getPropertyFromField($field)}->__id);
			}
			else
			{
				$qb->set($field, $entity->{$this->getPropertyFromField($field)});
			}
		}

		foreach($this->PKs as $key)
		{
			$qb->where([$key => $entity->getOldValue($this->getPropertyFromField($key))]);
		}

		return $qb->exec();
	}
}
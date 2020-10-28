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
	protected $hydratationColumns = [];

	protected $rel = [];

	final public function __construct($provider = null)
	{
		$rfl = new \ReflectionClass($this);

		$this->provider = $provider ?? \Config::get('Providers')->getConfig('db.default');
		$this->queryPrefixe  = $rfl->getShortName();

		$this->table = strtolower(preg_replace('#([\w])([A-Z]+)#','${1}_${2}',$rfl->getShortName()));
		$this->init();

		foreach($this->fields as $key => $field)
		{
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

	public function getTableName()
	{
		return $this->table;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setPrefixe($prefixe)
	{
		$this->queryPrefixe = $prefixe;
	}

	public function getProvider()
	{
		return $this->provider;
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
		if(isset($params['PK']))
		{
			$this->PKs[] = $fieldName;
			if($params['PK'] == 'AI')
			{
				$this->PKAI = $fieldName;
			}
		}

		if($this->fieldPrefixe == '')
		{
			$this->fields[$fieldName] =  array_merge(['entityField' => $fieldName], $params);
			return;
		}

		if(substr($fieldName,0,strlen($this->fieldPrefixe)) == $this->fieldPrefixe)
		{
			$entityField = substr($fieldName,strlen($this->fieldPrefixe));

			if($this->fieldForce !== null)
			{
				if($this->fieldForce == 'Camel')
				{
					$entityField = preg_replace_callback('#\_([a-z]{1})#',function($m){return ucfirst($m[1]);}, strtolower($entityField));
				}
				elseif(is_callable($this->fieldForce))
				{
					$entityField = call_user_func($this->fieldForce, $entityField);
				}
			}
			$this->fields[$fieldName] = array_merge(['entityField' => $entityField], $params);
			return;
		}

		$this->fields[$fieldName] = array_merge(['entityField' => $fieldName], $params);
		return;
	}

	protected function addLink($linkNAme, \Core\App\Mvc\TableLink\TableLinkInterface $params)
	{
		$params->setName($linkNAme);
		$this->links[$linkNAme] = $params;
	}

	public function newQueryBuilder()
	{
		$qb = new QueryBuilder($this->provider);
		$qb->setCallback([$this, 'getArrayDataHydrated']);

		return $qb;
	}

	public function find($params = [])
	{
		$qb = $this->newQueryBuilder();
		$qb->select($this->getAllFields())->from($this->getTable(true));

		$hydratationColumns = $this->getHydratationColumns();

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
			$this->getJoins($arrWith, $qb, $hydratationColumns);
		}

		$this->hydratationColumns = $hydratationColumns;

		if(isset($params['addQuery']))
		{
			call_user_func($params['addQuery'], $qb);
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
			if($link->getAutomaticWith())
			{
				if($parent != '')
				{
					$name = $parent . '.' . $name;
				}

				$ret[] = $name;

				if($link->hasRelations())
				{
					$linkCls = $link->getTable();
					$ret = array_merge($ret, (new $linkCls)->getRelationNames($name));
				}
			}
		}
		return $ret;
	}

	public function getJoins(array $relation, QueryBuilder $qb, &$hydratationColumns, $parentsArray=[] )
	{
		$parentsArray[] = $this->getPrefixe();

		foreach($relation as $relName => $rel)
		{
			if(!isset($this->links[$relName]))
			{
				throw new \Exception('Relation ' . $relName . ' inconnue');
			}

			$this->links[$relName]->getJoins($this,$relName,$rel,$qb,$hydratationColumns,$parentsArray);

			$hydratationColumns = array_merge($hydratationColumns,$this->links[$relName]->getHydratationColumns($parentsArray));

			$this->addRel($this->getPrefixedField($relName).'__',['relation' => $relName]);
		}
	}

	public function get($pks, $params = [])
	{
		$qb = $this->find($params);

		if(!is_array($pks))
		{
			$pks = [$pks];
		}

		foreach(array_combine($this->PKs,$pks) as $key => $val)
		{
			$qb->where([($this->getPrefixe().'.'.$key) => $val]);
		}

		return $qb->first();
	}

	public function getWithRel($pks, $params = [])
	{
		return $this->get($pks, array_merge($params, ['with' => $this->getRelationNames()]));
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

	public function getHydratationColumns($prefixes = [])
	{
		$ret = [];
		foreach($this->fields as $key => $val)
		{
			$ret[$this->getPrefixedField($key)] = [array_merge($prefixes,[$this->getPrefixe()]), $key];
		}

		return $ret;
	}

	public function getFieldFromProperty($property) : string
	{
		return $this->propertiesToFields[$property];
	}

	public function getPropertyFromField($field) : string
	{
		return $this->fieldsToProperties[$field];
	}

	public function addRel($name, $val)
	{
		$this->rel[$name] = $val;
	}


	public function hydrateEntity($data)
	{
		$values = [];

		if(isset($data['data']))
		{
			foreach($data['data'] as $k => $v)
			{
				if(isset($this->fields[$k]))
				{
					$values[$this->fields[$k]['entityField']] = $v;
				}
				else
				{
					$values[$k] = $v;
				}
			}
		}

		if(isset($data['rel']))
		{
			foreach($data['rel'] as $k => $v)
			{
				$myLink = $this->links[$this->rel[$k . '__']['relation']];
				$subEntity = $myLink->hydrateEntity($v);

				if(($linkTo = $myLink->getLinkTo()) !== null)
				{
					$values[$linkTo] = $subEntity;
				}
				elseif(isset($this->fieldsToProperties[$myLink->getFK()]) && array_key_exists($this->getPropertyFromField($myLink->getFK()),$values))
				{
					$oldVal = $values[$this->getPropertyFromField($myLink->getFK())];
					$values[$this->getPropertyFromField($myLink->getFK())] = $subEntity;
					$values[$this->getPropertyFromField($myLink->getFK())]->__id = $oldVal;
				}
				else
				{
					$values[$this->links[$this->rel[$k . '__']['relation']]->getFK()] = $subEntity;
				}
			}
		}

		return $this->createNewEntity($values);
	}

	public function getArrayDataHydrated($data)
	{
		$ret = ['rel' => [$this->getPrefixe()]];

		foreach($data as $k => $v)
		{
			if(isset($this->hydratationColumns[$k]))
			{
				$adr = &$ret;

				foreach($this->hydratationColumns[$k][0] as $v1)
				{
					$adr = &$adr['rel'][$v1];
				}
				$adr['data'][$this->hydratationColumns[$k][1]] = $v;
			}
			else
			{
				$ret['rel'][$this->getPrefixe()]['data'][$k] = $v;
			}
		}

		return $this->hydrateEntity($ret['rel'][$this->getPrefixe()]);
	}


	public function createNewEntity($data = [])
	{
		$entityClass = $this->entityClassName ?? Entity::class;
		return new $entityClass($data);
	}

	public function createEmpty($values=[])
	{
		$data = $this->createNewEntity();

		foreach($this->fields as $key => $field)
		{
			$data->{$this->fieldsToProperties[$key]} = $field['defaultValue'] ?? '';
		}

		foreach($values as $k => $v)
		{
			$data->$k = $v;
		}

		return $data;
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

		foreach($this->fields as $fieldName => $val)
		{
			if($fieldName == $this->PKAI)
			{
				continue;
			}

			if(isset($entity->{$this->getPropertyFromField($fieldName)}))
			{
				$qb->set($fieldName, $entity->{$this->getPropertyFromField($fieldName)});
			}
			elseif(isset($val['defaultValue']))
			{
				$qb->set($fieldName, $val['defaultValue']);
			}

		}

		$result = $qb->exec();

		if($this->PKAI !== null)
		{
			$entity->{$this->getPropertyFromField($this->PKAI)} = $qb->lastInsertId();
		}

		return $result;
	}

	public function DBUpdate(Entity $entity, ?Callable $qbCallback = null) : QueryResult
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

		if($qbCallback !== null)
		{
			$qbCallback($qb);
		}

		return $qb->exec();
	}

	public function DBReplace(Entity $entity) : QueryResult
	{
		$qb = $this->newQueryBuilder()
				   ->replace($this->table);

		foreach($this->fields as $fieldName => $val)
		{
			if(isset($entity->{$this->getPropertyFromField($fieldName)}))
			{
				$qb->set($fieldName, $entity->{$this->getPropertyFromField($fieldName)});
			}
			elseif(isset($val['defaultValue']))
			{
				$qb->set($fieldName, $val['defaultValue']);
			}

		}

		$result = $qb->exec();

		if($this->PKAI !== null)
		{
			$entity->{$this->getPropertyFromField($this->PKAI)} = $qb->lastInsertId();
		}

		return $result;
	}
}
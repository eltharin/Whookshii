<?php

namespace Core\App\Mvc;

use Core\Classes\DB\QueryBuilder;
use Core\Classes\DB\QueryResult;

class Table
{
	protected $provider        = null;
	protected $entityClassName = null;
	protected $table           = '';
	protected $prefixe         = '';
	protected $fields	= [];
	protected $links = [];

	protected $PKs = [];
	protected $PKAI = null;

	protected $fieldsToProperties = [];
	protected $propertiesToFields = [];

	protected $rel = [];

	final public function __construct($provider = null)
	{
		$rfl = new \ReflectionClass($this);

		$this->provider = $provider ?? \Config::get('Providers')->get('default');
		$this->prefixe  = $rfl->getShortName();
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
		return $this->table . ' as ' . $this->prefixe;
	}

	public function setPrefixe($prefixe)
	{
		$this->prefixe = $prefixe;
	}

	public function getPrefixe()
	{
		return $this->prefixe;
	}

	public function getPrefixedField($field)
	{
		return $this->prefixe . '__' . $field;
	}

	public function getEntityClassName()
	{
		return $this->entityClassName;
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
			foreach($params['with'] as $with)
			{
				$with = explode('.' , $with);
				$this->getRelations($with, $qb);
			}
		}

		return $qb;
	}

	public function getRelations(array $relation, QueryBuilder $qb)
	{
		if($relation == [])
		{
			return null;
		}

		if(!isset($this->links[$relation[0]]))
		{
			throw new \Exception('Relation ' . $relation[0] . ' inconnue');
		}

		$myRel = $this->links[$relation[0]];

		if($myRel['type'] == 'rel')
		{
			$tmpClass = new $myRel['table']($this->provider);
			$tmpClass->setPrefixe($this->prefixe . '__' . $relation[0]);

			if(!isset($myRel['fields']))
			{
				$qb->select($tmpClass->getAllFields());
			}

			$joinOn = $this->getPrefixedField($myRel['joinOn']['FK']) . ' = ' . $tmpClass->getPrefixedField($myRel['joinOn']['PK']);


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
												'relation' => $relation[0],
												'object' => $tmpClass,
												'FK' => $myRel['joinOn']['FK'],
												'data' => new \stdClass()
											];
			$tmpClass->getRelations(array_slice($relation,1),$qb);
			unset($tmpClass);
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
			$ret[] = $this->prefixe . '.' . $key . ' ' . $this->getPrefixedField($key);
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
			if(isset($data->{$this->getPrefixedField($key)}) && isset($val['entityField']))
			{
				$data->{$val['entityField']} = $data->{$this->getPrefixedField($key)};
				unset($data->{$this->getPrefixedField($key)});
			}
		}

		foreach($this->rel as $rel)
		{
			$data->{$this->getPropertyFromField($rel['FK'])} = $rel['object']->hydrateEntity($rel['data']);
		}

		$entityClass = $this->entityClassName ?? Entity::class;
		return new $entityClass($data);
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

		foreach($this->fields as $key => $val)
		{
			if(in_array($key, $this->PKs))
			{
				continue;
			}

			$qb->set($key, $entity->{$this->getPropertyFromField($key)});
		}

		foreach($this->PKs as $key)
		{
			$qb->where([$key => $entity->getOldValue($this->getPropertyFromField($key))]);
		}

		return $qb->exec();
	}
}
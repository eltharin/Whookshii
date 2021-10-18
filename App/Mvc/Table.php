<?php

namespace Core\App\Mvc;

use Core\App\Exception\HttpException;
use Core\App\Mvc\TableLink\Query;
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

	public function getPrefixedFieldAlias($field)
	{
		return $this->queryPrefixe != '' ? $this->queryPrefixe . '__' . $field : $field;
	}

	public function getPrefixedFieldName($field)
	{
		return $this->queryPrefixe != '' ? $this->queryPrefixe . '.' . $field : $field;
	}

	public function getEntityClassName()
	{
		return $this->entityClassName ?? Entity::class;
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

		if(!isset($params['defaultValue']) && isset($params['type']) && $params['type'] == 'int')
		{
			$params['defaultValue'] = 0;
		}

		$entityField = $fieldName;

		if($this->fieldPrefixe != '' && substr($fieldName,0,strlen($this->fieldPrefixe)) == $this->fieldPrefixe)
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
		}

		$this->fields[$fieldName] = array_merge(['entityField' => $entityField], $params);

		$this->fieldsToProperties[$fieldName] = $this->fields[$fieldName]['entityField'];
		$this->propertiesToFields[$this->fields[$fieldName]['entityField']] = $fieldName;
	}

	protected function addLink($linkName, \Core\App\Mvc\TableLink\TableLinkInterface $linkObject)
	{
		$linkObject->setName($linkName);
		$this->links[$linkName] = $linkObject;
	}

	public function newQueryBuilder($withTable = false) : QueryBuilder
	{
		$qb = new QueryBuilder($this->provider);
		if($withTable)
		{
			$qb->from($this->getTableName ());
		}
		$qb->getHydrator()->setRacine($this->getPrefixe(), $this->getEntityClassName());

		return $qb;
	}

	public function find($params = [])
	{
		$qb = $this->newQueryBuilder();
		$qb->select()->from($this->getTable(true));
		$this->addFieldToQb($qb, $params['fields'] ?? null);

		//$hydratationColumns = $this->getHydratationColumns();

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
						$container[$a] = ['show' => ($k == count($arr)-1), 'subRels' => []];
					}
					$container = &$container[$a]['subRels'];
				}
			}

			$this->addRelationsToQB($qb, $arrWith, []);
		}

		if(isset($params['addQuery']))
		{
			call_user_func($params['addQuery'], $qb);
		}

		return $qb;
	}

	public function findWithRel() : QueryBuilder
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

	public function addRelationsToQB(QueryBuilder $qb, array $withRelations)
	{
		foreach($withRelations as $relName => $rel)
		{
			if(!isset($this->links[$relName]))
			{
				throw new \Exception('Relation ' . $relName . ' inconnue');
			}

			$this->links[$relName]->getSubJoins($this,$rel,$qb);
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
			$qb->where([($this->getPrefixedFieldName($key)) => $val]);
		}

		return $qb->first();
	}

	public function getWithRel($pks, $params = [])
	{
		return $this->get($pks, array_merge($params, ['with' => $this->getRelationNames()]));
	}

	public function addFieldToQb(QueryBuilder $qb, $fields = null)
	{
		if($fields === null)
		{
			$fields = array_keys($this->fields);
		}

		foreach($fields as $fieldName)
		{
			$qb->select($this->getPrefixedFieldName ($fieldName) . ' ' . $this->getPrefixedFieldAlias($fieldName));
			$qb->getHydrator()->addField($this->getPrefixedFieldAlias($fieldName),$this->fields[$fieldName]['entityField'], $this->getPrefixe(), null, $this->fields[$fieldName]['defaultValue'] ?? null);
		}
		return null;
	}

	public function getAllFields()
	{
		$ret = [];
		foreach($this->fields as $key => $val)
		{
			$ret[] = $this->getPrefixedFieldName ($key) . ' ' . $this->getPrefixedFieldAlias($key);
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

	public function addRel($name, $val)
	{
		$this->rel[$name] = $val;
	}

	public function createNewEntity($data = [])
	{
		$entityClass = $this->getEntityClassName();
		return new $entityClass($data);
	}

	public function createEmpty($values=[]) : Entity
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

	public function DBInsert(Entity $entity, bool $withException = true) : QueryResult
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

		if($withException && $result->hasError ())
		{
			throw new HTTPException (implode(BRN, $result->getError ()), 500);
		}

		if($this->PKAI !== null)
		{
			$entity->{$this->getPropertyFromField($this->PKAI)} = $qb->lastInsertId();
		}

		return $result;
	}

	public function DBUpdate(Entity $entity, bool $withException = true) : QueryResult
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
				$qb->set($field, $entity->{$this->getPropertyFromField($field)}->getRemplacementValue());
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

		$result = $qb->exec();

		if($withException && $result->hasError ())
		{
			throw new HTTPException (implode(BRN, $result->getError ()), 500);
		}

		return $result;
	}

	public function DBReplace(Entity $entity, bool $withException = true) : QueryResult
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

		if($withException && $result->hasError ())
		{
			throw new HTTPException (implode(BRN, $result->getError ()), 500);
		}

		if($this->PKAI !== null)
		{
			$entity->{$this->getPropertyFromField($this->PKAI)} = $qb->lastInsertId();
		}

		return $result;
	}

    public function DBDelete(Entity $entity, bool $withException = true) : QueryResult
    {
        $qb = $this->newQueryBuilder()
                    ->delete($this->table);

        foreach($this->fields as $fieldName => $val)
        {
            if(isset($entity->{$this->getPropertyFromField($fieldName)}))
            {
                $qb->where([$fieldName => (string)$entity->{$this->getPropertyFromField($fieldName)}]);
            }
        }

        $result = $qb->exec();

		if($withException && $result->hasError ())
		{
			throw new HTTPException (implode(BRN, $result->getError ()), 500);
		}

        return $result;
    }
}
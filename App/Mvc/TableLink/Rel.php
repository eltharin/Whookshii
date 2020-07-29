<?php


namespace Core\App\Mvc\TableLink;


use Core\App\Mvc\Table;
use Core\Classes\Providers\DB\QueryBuilder;

class Rel extends TableLinkInterface
{
	protected $type = 'rel';
	protected $hasRel = true;

	/**
	 * @var \Core\App\Mvc\Table
	 */
	protected $obj = null;

	public function getName() : string
	{
		return $this->properties['table'];
	}

	public function getTable() : string
	{
		return $this->properties['table'];
	}

	public function getFK() : string
	{
		if(is_array($this->properties['joinOn']['FK']))
		{
			return $this->name;
		}
		return $this->properties['joinOn']['FK'];
	}


	public function getCompleteName()
	{
		return $this->obj->getPrefixedField('');
	}

	public function getJoins(Table $table, String $relName, $rel, QueryBuilder $qb, array &$hydrationColumns, $parentArray)
	{
		$tmpClassNAme = $this->getTable();
		$tmpClass = new $tmpClassNAme($table->getProvider());
		$tmpClass->setPrefixe($table->getPrefixedField($relName));

		if($rel['show'] == true)
		{
			if(!isset($this->properties['fields']))
			{
				$qb->select($tmpClass->getAllFields());
			}
			else
			{
				foreach($this->properties['fields'] as $f)
				{
					$qb->select($table->getPrefixe() . '.' . $f . ' ' . $table->getPrefixedField($f));
				}
			}
		}

		if(is_array($this->properties['joinOn']['FK']))
		{
			$joinArr = [];
			foreach($this->properties['joinOn']['FK'] as $k => $v)
			{
				$joinArr[] = $table->getPrefixe() . '.' . $v . ' = ' . $tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['PK'][$k];
			}
			$joinOn = implode(' AND ', $joinArr);
		}
		else
		{
			$joinOn = $table->getPrefixe() . '.' . $this->properties['joinOn']['FK'] . ' = ' . $tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['PK'];
		}

		if($this->properties['join']??'left' == 'inner')
		{
			$qb->ijoin($tmpClass->getTable(true), $joinOn);
		}
		else
		{
			$qb->ljoin($tmpClass->getTable(true), $joinOn);
		}

		unset($joinOn);

		$tmpClass->getJoins($rel['data'],$qb,$hydrationColumns,$parentArray);
		$this->obj = $tmpClass;
/*
 * 		$table->addRel($tmpClass->getPrefixedField(''),[
			'relation' => $relName,
			'object' => $tmpClass,
			'FK' => $this->properties['joinOn']['FK'],
			'data' => new \stdClass()
		]);
 */



	}

	public function hydrateEntity(array $data)
	{
		return $this->obj->hydrateEntity($data);
	}

	public function getHydratationColumns(array $prefixes = []) : array
	{
		return $this->obj->getHydratationColumns($prefixes);
	}
}
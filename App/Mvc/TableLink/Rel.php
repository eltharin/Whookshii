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

	public function getOfficalName() : string
	{
		return $this->name;
	}

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

	public function getSubJoins(Table $table, $rel, QueryBuilder $qb)
	{
		$tmpClassName = $this->getTable();
		$tmpClass = new $tmpClassName($table->getProvider());
		$tmpClass->setPrefixe($table->getPrefixedField($this->name));

		if($rel['show'] == true)
		{
			$tmpClass->addFieldToQb($qb, $this->properties['fields'] ?? null);
		}

		$qb->getHydrator()->addEtage($tmpClass->getPrefixe(), $tmpClass->getEntityClassName(), $table->getPrefixe (), $this->getLinkTo() ?? $table->getPropertyFromField($this->properties['joinOn']['FK']));


		$joinArr = [];
		if(is_array($this->properties['joinOn']['FK']))
		{
			foreach($this->properties['joinOn']['FK'] as $k => $v)
			{
				$joinArr[] = $table->getPrefixe() . '.' . $v . ' = ' . $tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['PK'][$k];
			}
		}
		else
		{
			$joinArr[] = $table->getPrefixe() . '.' . $this->properties['joinOn']['FK'] . ' = ' . $tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['PK'];
		}

		if(isset($this->properties['joinOn']['addJoin']))
		{
			$joinArr[] = str_replace(['#LOCAL_PREFIXE#','#FOREIGN_PREFIXE#'],[$table->getPrefixe(), $tmpClass->getPrefixe()],$this->properties['joinOn']['addJoin']);
		}

		$joinOn = implode(' AND ', $joinArr);

		if($this->properties['join']??'left' == 'inner')
		{
			$qb->ijoin($tmpClass->getTable(true), $joinOn);
		}
		else
		{
			$qb->ljoin($tmpClass->getTable(true), $joinOn);
		}

		unset($joinOn);

		$tmpClass->addRelationsToQB($qb,$rel['subRels']);
	}
}
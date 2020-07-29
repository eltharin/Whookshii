<?php


namespace Core\App\Mvc\TableLink;


use Core\App\Mvc\Table;
use Core\Classes\Providers\DB\QueryBuilder;

class Query extends TableLinkInterface
{
	protected $type = 'query';

	public function isFieldLink()
	{
		return false;
	}

	public function getCompleteName()
	{
		return '';
	}


	public function getJoins(Table $table, String $relName, $rel, QueryBuilder $qb)
	{
		$prefixe = $table->getPrefixedField($this->getName());

		foreach($this->properties['qb']->getSelect() as $select)
		{
			if(strpos($select, ' as ') !== false)
			{
				$select = explode(' as ', $select);
				$select = $select[1];
			}
			$qb->select($prefixe . '.' . $select . ' as ' . $select);
		}

		if(is_array($this->properties['joinOn']['FK']))
		{
			$joinArr = [];
			foreach($this->properties['joinOn']['FK'] as $k => $v)
			{
				$joinArr[] = $table->getPrefixe() . '.' . $this->properties['joinOn']['PK'][$k] . ' = ' . $prefixe . '.' . $v;
				$this->properties['qb']->select($v);
			}
			$joinOn = implode(' AND ', $joinArr);
		}
		else
		{
			$joinOn = $table->getPrefixe() . '.' . $this->properties['joinOn']['PK'] . ' = ' . $prefixe . '.' . $this->properties['joinOn']['FK'];
			$this->properties['qb']->select($this->properties['joinOn']['FK']);
		}

		$qb->ljoin('(' . $this->properties['qb']->getQuery() . ') as ' . $prefixe, $joinOn);
		$qb->setParam($this->properties['qb']->getPArams());
	}
}
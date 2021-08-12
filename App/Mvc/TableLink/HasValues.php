<?php

namespace Core\App\Mvc\TableLink;


use Core\App\Mvc\Table;
use Core\Classes\Providers\DB\QueryBuilder;

class HasValues extends TableLinkInterface
{
	protected $hydrationColumns = [];
	public function getFK() : string
	{
		return $this->properties['joinOn']['PK'];
	}

	public function getJoins(Table $table, String $relName, $rel, QueryBuilder $qb, array &$hydrationColumns, $parentArray)
	{
		$relObj = new ($this->properties['table']);
		foreach($relObj->getAllValueFields(['type' => $table->getTableName()]) as $champId => $champName)
		{
			$relObj->addValueField($table, $qb, $champId, $champName, $this, $hydrationColumns,$parentArray);
		}
	}

	public function getHydratationColumns(array $prefixes = []) : array
	{
		return $this->hydrationColumns;
	}
}
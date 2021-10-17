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

	public function getSubJoins(Table $table, $rel, QueryBuilder $qb)
	{
		$relObj = new ($this->properties['table']);
		$relObj->setPrefixe($table->getPrefixe ());

		foreach($relObj->getAllValueFields(['type' => $table->getTableName(), 'rel' => $this]) as $champId => $champName)
		{
			$relObj->addValueField($table, $qb, $champId, $champName, $this);
		}
	}
}
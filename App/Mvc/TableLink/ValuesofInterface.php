<?php


namespace Core\App\Mvc\TableLink;


interface ValuesofInterface
{
	public function getAllValueFields(array $params);

	public function addValueField(\Core\App\Mvc\Table $table,\Core\Classes\Providers\DB\QueryBuilder $qb, string $fieldId, string $fieldName, \Core\App\Mvc\TableLink\HasValues $rel);
}
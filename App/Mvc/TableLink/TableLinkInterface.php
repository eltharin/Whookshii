<?php
namespace Core\App\Mvc\TableLink;


use Core\App\Mvc\Table;
use Core\Classes\Providers\DB\QueryBuilder;

abstract class TableLinkInterface
{
	protected $properties = [];
	protected $hasRel = false;
	protected $type = '';
	protected $name = '';
	protected $obj = null;

	public function __construct($data)
	{
		$this->properties = $data;
	}

	public function getObj()
	{
		return $this->obj;
	}

	public function setName($name)
	{
		$this->name = $name;
	}
	public function getName()
	{
		return $this->name;
	}

	public function haveToHydrate()
	{
		return true;
	}

	public function getType()
	{
		return $this->type;
	}

	public function hasRelations()
	{
		return $this->hasRel;
	}

	public function getJoins(Table $table, String $relName, $rel, QueryBuilder $qb, array &$hydrationColumns, $parentArray)
	{

	}

	public function hydrateEntity(array $data)
	{

	}

	public function getHydratationColumns(array $prefixes = []) : array
	{
		return [];
	}


	public function getFK() : string
	{
		return $this->name;
	}

	public function getLinkTo()
	{
		return  $this->properties['linkTo'] ?? null;
	}
}
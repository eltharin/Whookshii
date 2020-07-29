<?php
namespace Core\App\Mvc\TableLink;


use Core\App\Mvc\Table;
use Core\Classes\Providers\DB\QueryBuilder;

class HasMany extends TableLinkInterface
{
	protected $type = 'hasMany';
	protected $hasRel = false;
	protected $fieldName = '';
	protected $prefixe = '';
	/**
	 * @var \Core\App\Mvc\Table
	 */
	protected $obj = null;

	public function getJoins(Table $table, String $relName, $rel, QueryBuilder $qb, array &$hydrationColumns, $parentArray)
	{
		$tmpClassNAme = $this->getTable();
		$tmpClass = new $tmpClassNAme($table->getProvider());

		$this->prefixe = $table->getPrefixedField($relName);

		$qb2 = $tmpClass->findWithRel();

		$tabSelect = [];

		foreach($qb2->getSelect() as $sel)
		{
			$sel = explode(' ', $sel);
			$tabSelect[] = '\'' . $sel[1] . '\', ' . $sel[0];
		}

		$str = 'concat(\'[\',GROUP_CONCAT(JSON_OBJECT(' . implode(', ', $tabSelect) . ') SEPARATOR \',\'),\']\')';

		$qb2->select($str . ' as json', true);

		if(is_array($this->properties['joinOn']['FK']))
		{
			$joinArr = [];
			foreach($this->properties['joinOn']['FK'] as $k => $v)
			{
				$qb2->select($tmpClass->getPrefixe() . '.' . $v . ' as ' . $tmpClass->getPrefixedField($v));
				$joinArr[] = $table->getPrefixe() . '.' . $this->properties['joinOn']['PK'][$k] . ' = ' . $tmpClass->getPrefixe() . '.' . $v;
			}
			$joinOn = implode(' AND ', $joinArr);
		}
		else
		{
			$qb2->select($tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['FK'] . ' as ' . $tmpClass->getPrefixedField($this->properties['joinOn']['FK']));
			$qb2->groupby($tmpClass->getPrefixe() . '.' . $this->properties['joinOn']['FK']);

			$joinOn = $table->getPrefixe() . '.' . $this->properties['joinOn']['PK'] . ' = ' . $tmpClass->getPrefixe() . '.' . $tmpClass->getPrefixedField($this->properties['joinOn']['FK']);
		}


		$qb->select($tmpClass->getPrefixe().'.json as '  . $tmpClass->getPrefixedField('json'));
		$qb->ljoin('(' . $qb2->getQuery() . ') as ' . $tmpClass->getPrefixe(), $joinOn);

		$this->fieldName = $tmpClass->getPrefixedField('json');

		$this->obj = $tmpClass;
	}


	public function getTable() : string
	{
		return $this->properties['table'];
	}

	public function hydrateEntity(array $data)
	{
		if($data['data']['json'] == null)
		{
			return [];
		}

		$ret = [];
		foreach(json_decode($data['data']['json']) as $data)
		{
			$ret[] = $this->obj->getArrayDataHydrated($data);
		}
		return $ret;
	}

	public function getHydratationColumns(array $prefixes = []) : array
	{
		return [$this->fieldName => [array_merge($prefixes,[$this->prefixe]), 'json']];
	}


}
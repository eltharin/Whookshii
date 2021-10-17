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

	public function getSubJoins(Table $table, $rel, QueryBuilder $qb)
	{
		$tmpClassNAme = $this->getTable();
		$tmpClass = new $tmpClassNAme($table->getProvider());

		$this->prefixe = $table->getPrefixedFieldAlias($this->name);

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
				$qb2->select($tmpClass->getPrefixedFieldName($v) . ' as ' . $tmpClass->getPrefixedFieldAlias($v));
				$joinArr[] = $table->getPrefixedFieldName($this->properties['joinOn']['PK'][$k]) . ' = ' . $tmpClass->getPrefixedFieldAlias($v);
			}
			$joinOn = implode(' AND ', $joinArr);
		}
		else
		{
			$qb2->select($tmpClass->getPrefixedFieldName($this->properties['joinOn']['FK']) . ' as ' . $tmpClass->getPrefixedFieldAlias($this->properties['joinOn']['FK']));
			$qb2->groupby($tmpClass->getPrefixedFieldName($this->properties['joinOn']['FK']));

			$joinOn = $table->getPrefixedFieldName($this->properties['joinOn']['PK']) . ' = ' . $tmpClass->getPrefixedFieldName($tmpClass->getPrefixedFieldAlias($this->properties['joinOn']['FK']));
		}


		$qb->select($tmpClass->getPrefixedFieldName('json') . ' as '  . $tmpClass->getPrefixedFieldAlias('json'));
		$qb->ljoin('(' . $qb2->getQuery() . ') as ' . $tmpClass->getPrefixe(), $joinOn);

		$qb->getHydrator()->addField($tmpClass->getPrefixedFieldAlias('json'),$this->properties['linkTo'], $table->getPrefixe(), function ($a) use ($qb2) {$ret = [] ; foreach(json_decode($a) as $val){ $ret[] = call_user_func ([$qb2->getHydrator(), 'hydrate'], $val);} return $ret;});

	}


	public function getTable() : string
	{
		return $this->properties['table'];
	}

}
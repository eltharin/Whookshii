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

		$str = 'JSON_ARRAYAGG( JSON_OBJECT(' . implode(', ', $tabSelect) . '))';

		$qb2->select($str . ' as json', true);

		$joinArr = [];

		if(is_array($this->properties['joinOn']['FK']))
		{
			foreach($this->properties['joinOn']['FK'] as $k => $v)
			{
				$qb2->select($tmpClass->getPrefixedFieldName($v) . ' as ' . $tmpClass->getPrefixedFieldAlias($v));
				$joinArr[] = $table->getPrefixedFieldName($this->properties['joinOn']['PK'][$k]) . ' = ' . $tmpClass->getPrefixedFieldAlias($v);
			}
		}
		else
		{
			$qb2->select($tmpClass->getPrefixedFieldName($this->properties['joinOn']['FK']) . ' as ' . $tmpClass->getPrefixedFieldAlias($this->properties['joinOn']['FK']));
			$qb2->groupby($tmpClass->getPrefixedFieldName($this->properties['joinOn']['FK']));

			$joinArr[] = $table->getPrefixedFieldName($this->properties['joinOn']['PK']) . ' = ' . $tmpClass->getPrefixedFieldName($tmpClass->getPrefixedFieldAlias($this->properties['joinOn']['FK']));
		}

		if(isset($this->properties['joinOn']['filter']))
		{
			$qb2->where(str_replace(['#LOCAL_PREFIXE#','#FOREIGN_PREFIXE#'],[$table->getPrefixe(), $tmpClass->getPrefixe()],$this->properties['joinOn']['filter']));
		}

		if(isset($this->properties['joinOn']['addJoin']))
		{
			foreach($this->properties['joinOn']['addJoin'] as $col => $value)
			{
				$qb2->select($tmpClass->getPrefixedFieldName($col) . ' as ' . $tmpClass->getPrefixedFieldAlias($col));
				$qb2->groupby($tmpClass->getPrefixedFieldName($col));
				$joinArr[] = $tmpClass->getPrefixedFieldName($tmpClass->getPrefixedFieldAlias($col)) . ' = ' . str_replace(['#LOCAL_PREFIXE#','#FOREIGN_PREFIXE#'],[$table->getPrefixe(), $tmpClass->getPrefixe()],$value);
			}

		}

		$joinOn = implode(' AND ', $joinArr);

		$qb->select($tmpClass->getPrefixedFieldName('json') . ' as '  . $tmpClass->getPrefixedFieldAlias('json'));
		$qb->ljoin('(' . $qb2->getQuery() . ') as ' . $tmpClass->getPrefixe(), $joinOn);

		$qb->getHydrator()->addField($tmpClass->getPrefixedFieldAlias('json'),$this->properties['linkTo'], $table->getPrefixe(), function ($a) use ($qb2) {$ret = [] ; if($json = json_decode($a)) {foreach($json as $val){$ret[] = call_user_func ([$qb2->getHydrator(), 'hydrate'], $val);}} return $ret;});

	}


	public function getTable() : string
	{
		return $this->properties['table'];
	}

}
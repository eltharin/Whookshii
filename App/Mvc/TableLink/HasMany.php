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

		$this->prefixe = $table->getPrefixedField($this->name);

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

		$qb->getHydrator()->addField($tmpClass->getPrefixedField('json'),$this->properties['linkTo'], $table->getPrefixe(), function ($a) use ($qb2) {$ret = [] ; foreach(json_decode($a) as $val){ $ret[] = call_user_func ([$qb2->getHydrator(), 'hydrate'], $val);} return $ret;});

	}


	public function getTable() : string
	{
		return $this->properties['table'];
	}

}
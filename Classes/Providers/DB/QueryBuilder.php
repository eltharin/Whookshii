<?php
namespace Core\Classes\Providers\DB;

use Core\App\Mvc\Hydrator;

class QueryBuilder implements \Iterator
{
	private $query = '';
	private $type = 'select';
	private $params = array();
	private $queryElements;

	private $isQueryBuiled = false;
	/**
	 * @var PDO | null
	 */
	private $provider = null;
	private $fetchMode = null;
	private $callback = null;
	private $hydrator = null;

	private $lastError = null;

    private $iteratorStmt = null;
	private $iteratorResult = null;
	private $iteratorKey = null;
	private $iteratorValid = false;

	public function __construct($provider = null, ?string $query = null, array $params = [])
	{
		$this->queryElements = new \stdClass();
		$this->queryElements->options = new \stdClass();
		$this->provider = $provider;
		$this->hydrator = new Hydrator();

		if($query !== null)
		{
			$this->setQuery ($query, $params);
		}
	}

	public function __clone()
	{
		$this->queryElements = clone $this->queryElements;
	}

	function setParam($array)
	{
		$this->params = array_merge($this->params,$array);
		return $this;
	}

	function getQuery()
	{
		if(!$this->isQueryBuiled)
		{
			$this->buildQuery();
		}

		return $this->query;
	}

	function setQuery($query, $params = []) : QueryBuilder
	{
		$this->query = $query;
		$this->params = $params;
		$this->isQueryBuiled = true;

		return $this;
	}

	function getParams()
	{
		return $this->params;
	}

	function getHydrator()
	{
		return $this->hydrator;
	}

	public function withoutExceptions() : QueryBuilder
	{
		$this->provider->setWithoutExceptions(true);
		return $this;
	}

	private function buildQuery()
	{
		switch($this->type)
		{
			case 'select' :
					if (!isset($this->queryElements->select) || $this->queryElements->select === '')
					{
						$this->queryElements->select[] = '*';
					}

					$query  = 'SELECT ' . implode(' , ', $this->queryElements->select);

					if (isset($this->queryElements->from))
					{
						$query .= ' FROM ' . implode(', ', $this->queryElements->from );
					}
				break;
			case 'insert' :
					$this->query  = 'INSERT ' . (($this->queryElements->options->ignore??false) ? 'IGNORE ' : '' ) . 'INTO ' . $this->queryElements->from[0] .
								' (' . implode(', ',array_column($this->queryElements->set??[], 'champ')) .
								') VALUES (' . implode(', ',array_column($this->queryElements->set??[], 'key')) . ')';
					return true;
				break;
			case 'update' :
					$table = implode(', ', $this->queryElements->from );
					$query  = 'UPDATE ' . $table;
				break;
			case 'replace' :
					$this->query  = 'REPLACE INTO ' . $this->queryElements->from[0] .
						' (' . implode(', ',array_column($this->queryElements->set, 'champ')) .
						') VALUES (' . implode(', ',array_column($this->queryElements->set, 'key')) . ')';
				return true;
			case 'delete' :
					$table = implode(', ', $this->queryElements->from );
					$query  = 'DELETE';

					if (isset($this->queryElements->alias) && $this->provider->allowDeleteAliasTable() == true)
					{
						$query .= ' ' . implode(', ', $this->queryElements->alias );
					}

					if (isset($this->queryElements->from))
					{
						$query .= ' FROM ' . implode(', ', $this->queryElements->from );
					}
				break;

		}

		if (isset($this->queryElements->join))
		{
			$query  .= ' ' . implode(' ', $this->queryElements->join);
		}

		if (isset($this->queryElements->crossApply))
		{
			$query .= ' CROSS APPLY ' . $this->queryElements->crossApply;
		}

		if (isset($this->queryElements->set))
		{
			$query .= ' SET ' . implode(', ', array_map(function ($a) {return $a['champ'] . ' = ' . $a['key'];}, $this->queryElements->set) );
		}

		if (isset($this->queryElements->where))
		{
			$query .= ' WHERE ' . implode(' AND ', $this->queryElements->where );
		}

		if (isset($this->queryElements->groupby))
		{
			$query  .= ' GROUP BY ' . implode(' , ', $this->queryElements->groupby);
		}

		if (isset($this->queryElements->having))
		{
			$query .= ' HAVING ' . implode(' AND ', $this->queryElements->having );
		}

		if (isset($this->queryElements->order))
		{
			$query  .= ' ORDER BY ' . implode(' , ', $this->queryElements->order);
		}

		if (isset($this->queryElements->limit))
		{
			$query  .= ' LIMIT ' . $this->queryElements->limit;

			if (isset($this->queryElements->offset))
			{
				$query  .= ' OFFSET ' . $this->queryElements->offset;
			}
		}

		if (isset($this->queryElements->free))
		{
			$query  .= ' ' . $this->queryElements->free;
		}

		$this->query = $query;
		$this->isQueryBuiled = true;
		return $this->query;
	}

    //-----------------------------------------------------------------------------------------------------------------
	//-- SQL Query Elements
    //-----------------------------------------------------------------------------------------------------------------

	public function select($str = null,$clearBefore=false,$placeFirst=false)
	{
		$this->type = 'select';
		$this->isQueryBuiled = false;

		if ($str === null)
		{
			$this->queryElements->select = [];
		}
		else
		{
			if(!isset($this->queryElements->select))
			{
				$this->queryElements->select = [];
			}
			if ($clearBefore == true)
			{
				$this->queryElements->select = [];
			}
			if($placeFirst == false)
			{
				$this->queryElements->select = array_merge($this->queryElements->select,array_map('trim', explode(',',$str)));
			}
			else
			{
				array_unshift($this->queryElements->select,$str);
			}
		}
		return $this;
	}

	public function getSelect()
	{
		return $this->queryElements->select;
	}

	public function insert($table = null, $options = [])
	{
		$this->isQueryBuiled = false;

		$this->type = 'insert';
		if($table !== null)
		{
			$this->from($table);
		}

		if(isset($options['ignore']))
		{
			$this->queryElements->options->ignore = $options['ignore'];
		}

		return $this;
	}

	public function update($table = null)
	{
		$this->isQueryBuiled = false;

		$this->type = 'update';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function delete($table = null, $tableAlias = null)
	{
		$this->isQueryBuiled = false;

		$this->type = 'delete';
		if($table !== null)
		{
			$this->from($table);
		}
		if($tableAlias !== null)
		{
			$this->alias($tableAlias);
		}
		return $this;
	}

	public function replace($table = null)
	{
		$this->isQueryBuiled = false;

		$this->type = 'replace';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function set($params, $val=null, $valSql = false)
	{
		$this->isQueryBuiled = false;

		if(!is_array($params))
		{
			if($valSql === false)
			{
				$this->queryElements->set[] = ['champ' => $params, 'key' => ':set_' . $params];
				$this->setParam(['set_' . $params => $val]);
			}
			else
			{
				$this->queryElements->set[] = ['champ' => $params, 'key' => $val];
			}
			return $this;
		}

		foreach($params as $key => $val)
		{
			$this->set($key, $val);
		}
		return $this;
	}

	public function from($table)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->from[] = $table;
		return $this;
	}

	public function alias($tableAlias)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->alias[] = $tableAlias;
		return $this;
	}

	public function join($table, $param)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->join[] = 'JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function ijoin($table, $param)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->join[] = 'INNER JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function ljoin($table, $param)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->join[] = 'LEFT JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function rjoin($table, $param)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->join[] = 'RIGHT JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function crossApply($param)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->crossApply = $param;
		return $this;
	}

	public function where($condition,array $vars=array())
	{
		$this->isQueryBuiled = false;

		if(is_array($condition))
        {
			foreach ($condition as $k => $v)
			{
				$k2 = str_replace('.','_', $k);
				$this->where($k . ' = :' . $k2,array($k2 => $v));
			}
        }
		else
        {
		    if ($condition == '')
            {
                $conditionArray = [];
                foreach ($vars as $k => $v)
                {
                    $conditionArray[] = $k . ' = :' . str_replace('.','_', $k);
                }
                $condition = implode(' AND ', $conditionArray);
            }

            $this->queryElements->where[] = $condition;
            $this->setParam($vars);
		}
		return $this;
	}

	public function wherein($field,$vars=array(),$notin = false)
	{
		$this->isQueryBuiled = false;

		$condition = $field . ' ' . ($notin ? 'NOT ' : '') . 'IN (';
		$params = [];
		$tabcondition = [];

		if(!empty($vars))
		{
			foreach($vars as $k => $v)
			{
				$field = str_replace('.','_', $field);
				$tabcondition[] = ':' . $field.'__'.$k;
				$params[$field.'__'.$k] = $v;
			}

			$condition .= implode(',',$tabcondition);
		}
		else
		{
			$condition .= 'NULL';
		}
		$condition .= ')';

		$this->queryElements->where[] = $condition;
		$this->setParam($params);

		return $this;
	}

	public function having($condition,$vars=array())
	{
		$this->isQueryBuiled = false;

		$this->queryElements->having[] = $condition;
		$this->setParam($vars);
		return $this;
	}

	public function groupby($str)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->groupby[] = $str;
		return $this;
	}

	public function order($str)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->order[] = $str;
		return $this;
	}

	public function limit($str)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->limit = $str;
		return $this;
	}

	public function offset($str)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->offset = $str;
		return $this;
	}

	public function free($str)
	{
		$this->isQueryBuiled = false;

		$this->queryElements->free = $str;
		return $this;
	}

	public function fetchMode(...$mode)
	{
		$this->isQueryBuiled = false;

		$this->fetchMode = $mode;
        return $this;
	}

	public function getFetchMode()
	{
		return $this->fetchMode[0];
	}
	/*
	public function fetchClass($className , $args = [])
    {
    	$this->fetchMode = function($stmt) use ($className, $args) {$stmt->setFetchMode( \PDO::FETCH_CLASS, $className, $args);};
        return $this;
    }

    public function fetchInto($objet)
    {
    	$this->fetchMode = function($stmt) use ($objet) {$stmt->setFetchMode( \PDO::FETCH_INTO, $objet);};
        return $this;
    }*/
	protected function execute()
	{
		$result = $this->provider->execute($this);

		$stmt = $result->getStmt();

		if($this->fetchMode !== null && $stmt !== null)
		{
			call_user_func_array([$stmt,'setFetchMode'], $this->fetchMode);
		}

		return $stmt;
	}

	public function all()
	{
		$stmt = $this->execute();

		if($stmt === null)
		{
			return false;
		}

		if($this->fetchMode !== null)
		{
			$all = call_user_func_array([$stmt, 'fetchAll'], $this->fetchMode);
		}
		else
		{
			$all = $stmt->fetchAll();
		}

		/*if($this->callback == null)
		{
			return $all;
		}*/
		if($this->fetchMode !== null && ($this->fetchMode[0] == \PDO::FETCH_KEY_PAIR || $this->fetchMode[0] != \PDO::FETCH_OBJ && $this->fetchMode[0] & (\PDO::FETCH_OBJ | \PDO::FETCH_GROUP) == 0))
		{
			return $all;
		}

		if($this->fetchMode !== null && ($this->fetchMode[0] & \PDO::FETCH_GROUP) > 0 && ($this->fetchMode[0] & \PDO::FETCH_UNIQUE) != \PDO::FETCH_UNIQUE)
		{
			return array_map(function($group)
								{
									return array_map(function($elem) {return call_user_func([$this->hydrator, 'hydrate'], $elem);},$group);
								}
							, $all);
		}
		return array_map([$this->hydrator, 'hydrate'], $all);
	}

	protected function fetch($stmt)
	{
		if($stmt === null)
		{
			return false;
		}

		$next = $stmt->fetch();
		if($next === false)
		{
			return null;
		}

		/*if($this->callback == null)
		{
			return $next;
		}*/

		if($this->fetchMode !== null && ($this->fetchMode[0] != \PDO::FETCH_OBJ))
		{
			return $next;
		}

		return $this->hydrator->hydrate($next);//call_user_func($this->callback, $next);
	}

	public function first()
	{
		$this->rewind();
		return $this->iteratorResult;
	}

	public function exec() : \Core\Classes\Providers\DB\QueryResult
	{
		return $this->provider->execute($this);
	}

    public function setCallback(?Callable $callback)
	{
		$this->callback = $callback;
		return $this;
	}

	public function lastInsertId()
	{
		return $this->provider->lastInsertId();
	}

	public function groupRetBy($col)
	{
		$this->select($col, false, true);
		$this->fetchMode(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_OBJ );
		return $this;
	}


	public function getKeyPair(string $key, string $val)
	{
		$this->select($key,  true);
		$this->select($val);

		$this->fetchMode(\PDO::FETCH_KEY_PAIR );
		return $this->all();
	}

	public function cleanHydrator()
	{
		$this->getHydrator()->clean();
		return $this;
	}

    //-----------------------------------------------------------------------------------------------------------------
    //-- Iterator Elements
    //-----------------------------------------------------------------------------------------------------------------

	public function current(): mixed
    {
        return $this->iteratorResult;
    }

    public function next(): void
    {
        $this->iteratorKey++;
        $this->iteratorResult = $this->fetch($this->iteratorStmt);
        if (null == $this->iteratorResult)
        {
            $this->iteratorValid = false;
            return;
			//return null;
        }
		return;
        //return $this->iteratorResult;
    }

    public function key(): mixed
    {
        return $this->iteratorKey;
    }

    public function valid(): bool
    {
        return $this->iteratorValid;
    }

    public function rewind(): void
    {
		if(!$this->iteratorValid)
		{
			$this->buildQuery();
			$this->iteratorStmt = $this->execute();
			$this->iteratorResult = $this->fetch($this->iteratorStmt);
			$this->iteratorValid = $this->iteratorResult != false;
		}
        $this->iteratorKey = 0;
		return;
    }
}
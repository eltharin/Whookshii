<?php
namespace Core\Classes\Providers\DB;

class QueryBuilder implements \Iterator
{
	private $query = '';
	private $type = 'select';
	private $params = array();
	private $queryElements;
	/**
	 * @var PDO | null
	 */
	private $provider = null;
	private $fetchMode = null;
	private $callback = null;

    private $iteratorStmt = null;
	private $iteratorResult = null;
	private $iteratorKey = null;
	private $iteratorValid = false;

	public function __construct($provider = null)
	{
		$this->queryElements = new \stdClass();
		$this->provider = $provider;
	}

	function setParam($array)
	{
		$this->params = array_merge($this->params,$array);
		return $this;
	}

	function getQuery()
	{
		$this->buildQuery();
		return $this->query;
	}

	function getParams()
	{
		return $this->params;
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
								' (' . implode(', ',array_column($this->queryElements->set, 'champ')) .
								') VALUES (' . implode(', ',array_column($this->queryElements->set, 'key')) . ')';
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
		return $this->query;
	}

    //-----------------------------------------------------------------------------------------------------------------
	//-- SQL Query Elements
    //-----------------------------------------------------------------------------------------------------------------

	public function select($str,$clearBefore=false,$placeFirst=false)
	{
		$this->type = 'select';
		if ($str === null)
		{
			$this->queryElements->select = array();
		}
		else
		{
			if ($clearBefore == true)
			{
				$this->queryElements->select = array();
			}
			if($placeFirst == false)
			{
				$this->queryElements->select[] = $str;
			}
			else
			{
				array_unshift($this->queryElements->select,$str);
			}
		}
		return $this;
	}

	public function insert($table = null)
	{
		$this->type = 'insert';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function update($table = null)
	{
		$this->type = 'update';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function delete($table = null)
	{
		$this->type = 'delete';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function replace($table = null)
	{
		$this->type = 'replace';
		if($table !== null)
		{
			$this->from($table);
		}
		return $this;
	}

	public function set($params, $val=null)
	{
		if(!is_array($params))
		{
			$this->queryElements->set[] = ['champ' => $params, 'key' => ':set_' . $params];
			$this->setParam(['set_' . $params => $val]);
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
		$this->queryElements->from[] = $table;
		return $this;
	}

	public function join($table, $param)
	{
		$this->queryElements->join[] = 'JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function ijoin($table, $param)
	{
		$this->queryElements->join[] = 'INNER JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function ljoin($table, $param)
	{
		$this->queryElements->join[] = 'LEFT JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function rjoin($table, $param)
	{
		$this->queryElements->join[] = 'RIGHT JOIN ' . $table . ' ON ' . $param;
		return $this;
	}

	public function crossApply($param)
	{
		$this->queryElements->crossApply = $param;
		return $this;
	}

	public function where($condition,array $vars=array())
	{
		if(is_array($condition))
        {
			foreach ($condition as $k => $v)
			{
				$this->where($k . ' = :' . $k,array($k => $v));
			}
        }
		else
        {
		    if ($condition == '')
            {
                $conditionArray = [];
                foreach ($vars as $k => $v)
                {
                    $conditionArray[] = $k . ' = :' . $k;
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
		$condition = $field . ' ' . ($notin ? 'NOT ' : '') . 'IN (';
		$params = [];
		$tabcondition = [];

		if(!empty($vars))
		{
			foreach($vars as $k => $v)
			{
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
		$this->queryElements->having[] = $condition;
		$this->setParam($vars);
		return $this;
	}

	public function groupby($str)
	{
		$this->queryElements->groupby[] = $str;
		return $this;
	}

	public function order($str)
	{
		$this->queryElements->order[] = $str;
		return $this;
	}

	public function limit($str)
	{
		$this->queryElements->limit = $str;
		return $this;
	}

	public function offset($str)
	{
		$this->queryElements->offset = $str;
		return $this;
	}

	public function free($str)
	{
		$this->queryElements->free = $str;
		return $this;
	}

	public function fetchMode(...$mode)
	{
		$this->fetchMode = $mode;
		$this->callback = null;
        return $this;
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

		if($this->fetchMode !== null)
		{
			call_user_func_array([$stmt,'setFetchMode'], $this->fetchMode);
		}

		return $stmt;
	}

	public function all()
	{
		$stmt = $this->execute();

		if($this->callback == null)
		{
			return $stmt->fetchAll();
		}

		return array_map($this->callback, $stmt->fetchAll());
	}

	protected function fetch($stmt)
	{
		$next = $stmt->fetch();
		if($next === false)
		{
			return false;
		}

		if($this->callback == null)
		{
			return $next;
		}

		return call_user_func($this->callback, $next);
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
	}

	public function lastInsertId()
	{
		return $this->provider->lastInsertId();
	}

    //-----------------------------------------------------------------------------------------------------------------
    //-- Iterator Elements
    //-----------------------------------------------------------------------------------------------------------------

	public function current()
    {
        return $this->iteratorResult;
    }

    public function next()
    {
        $this->iteratorKey++;
        $this->iteratorResult = $this->fetch($this->iteratorStmt);
        if (false === $this->iteratorResult)
        {
            $this->iteratorValid = false;
            return null;
        }
        return $this->iteratorResult;
    }

    public function key()
    {
        return $this->iteratorKey;
    }

    public function valid()
    {
        return $this->iteratorValid;
    }

    public function rewind()
    {
		if(!$this->iteratorValid)
		{
			$this->buildQuery();
			$this->iteratorStmt = $this->execute();
			$this->iteratorResult = $this->fetch($this->iteratorStmt);
			$this->iteratorValid = $this->iteratorResult === false ? false : true;
		}
        $this->iteratorKey = 0;
    }
}
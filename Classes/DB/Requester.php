<?php
namespace Core\Classes\DB;

use Iterator;
use Core\Classes\Timer;

class Requester implements Iterator
{
	/**
	 * @var string
	 */
	private $query = '';
	/**
	 * @var string
	 */
	private $type = '';
	/**
	 * @var array
	 */
	private $params = array();
	/**
	 * @var object
	 */
	private $query_elements;
	
	
	function __construct($parent)
	{
		$this->parent = $parent;
		$this->query_elements = new \stdClass();
	}

	function print_query()
	{
		$this->make_query();
		echo $this->query . BRN;
		var_dump ($this->params) . BRN;
		return $this;
	}
	
	function get_query($onlyquery = false)
	{
		$this->make_query();
		
		if ($onlyquery === false)
		{
			return array('query'=>$this->query,
						'params'=>$this->params);
		}
		else
		{
			return $this->query;
		}
	}
	
	/**
	 * $mode=\PDO::FETCH_ASSOC =>  normal
	 * $mode=\PDO::FETCH_ASSOC
	 */
	function get($mode=\PDO::FETCH_ASSOC,$fetch_argument = null)
	{
		$this->make_query();
		$this->parent->set_vars($this->query,$this->params);
		return $this->parent->get($mode,$fetch_argument);
	}
	function findFirst($mode=\PDO::FETCH_ASSOC,$fetch_argument = null)
	{
		$this->make_query();
		$this->parent->set_vars($this->query,$this->params);
		return $this->parent->findFirst($mode,$fetch_argument);
	}		
	

	function fetch()
	{
		$this->make_query();
		$this->parent->set_vars($this->query,$this->params);
		return $this->parent->fetch();
	}	
	function exec()
	{
		$this->make_query();
		$this->parent->set_vars($this->query,$this->params);
		return $this->parent->exec();
	}
	
	/**
	 * Focntion de creation de la requete et es arguments
	 * @param string $type {'select','insert','delete'}
	 * @return type
	 */
	private function make_query()
	{
		switch($this->type)
		{
			case 'delete' : 
					$table = implode(', ', $this->query_elements->from );
					//$query  = 'DELETE ' . substr($table,0,strlen($table) - strpos($table,' as ')) . ' ';
					//$query  = 'DELETE ' . $table . ' ';
					$query  = 'DELETE ';
				break;
			case 'update' : 
					$table = implode(', ', $this->query_elements->update );
					//$query  = 'DELETE ' . substr($table,0,strlen($table) - strpos($table,' as ')) . ' ';
					$query  = 'UPDATE ' . $table . ' ';
				break;
			case 'select' : 
				
				if ($this->query_elements->select === '')
					$this->query_elements->select[] = '*';

				$query  = 'SELECT ' . implode(' , ', $this->query_elements->select) . ' ';
				break;
		}
		
		if (isset($this->query_elements->from))
		{
			$query .= 'FROM ' . implode(', ', $this->query_elements->from ). ' ';
		}

		if (isset($this->query_elements->join))
		{
			$query  .= implode(' ', $this->query_elements->join) . ' ';	
		}
		
		if (isset($this->query_elements->set))
		{
			$query .= 'SET ' . implode(', ', $this->query_elements->set ). ' ';
		}
		
		if (isset($this->query_elements->where))
		{
			$query .= 'WHERE ' . implode(' AND ', $this->query_elements->where ). ' ';
		}

		if (isset($this->query_elements->groupby))
		{
			$query  .= 'GROUP BY ' . implode(' , ', $this->query_elements->groupby) . ' ';	
		}

									
		if (isset($this->query_elements->having))
		{
			$query .= 'HAVING ' . implode(' AND ', $this->query_elements->having ). ' ';
		}
		
		if (isset($this->query_elements->order))
		{
			$query  .= 'ORDER BY ' . implode(' , ', $this->query_elements->order) . ' ';	
		}

		if (isset($this->query_elements->limit))
		{
			$query  .= 'LIMIT ' . $this->query_elements->limit . ' ';	

			if (isset($this->query_elements->offset))
			{
				$query  .= 'OFFSET ' . $this->query_elements->offset . ' ';	
			}
		}

		if (isset($this->query_elements->free))
		{
			$query  .= $this->query_elements->free . ' ';	
		}

		$this->query = $query;
		return $this->query;
	}
	
	/**
	 * Ajout d'un element de selection
	 * @param type $str
	 */
	public function select($str,$clearbefore=false,$placefirst=false) 
	{
		$this->type = 'select';
		if ($str === null)
		{
			$this->query_elements->select = array();
		}
		else
		{
			if ($clearbefore == true)
			{
				$this->query_elements->select = array();
			}
			if($placefirst == false)
			{
				$this->query_elements->select[] = $str;
			}
			else
			{
				array_unshift($this->query_elements->select,$str);
			}
		}
		return $this;
	}

	public function delete()
	{
		$this->type = 'delete';
		return $this;
	}
	
	public function update($str)
	{
		$this->type = 'update';

		$this->query_elements->update[] = $str;

		return $this;
	}
	
	/**
	 * Ajout d'une table dans le from
	 * @param string $table table à joinde
	 */
	public function from($table)
	{
		$this->query_elements->from[] = $table;
		return $this;
	}
	
	/**
	 * Ajout d'une table dans le from
	 * @param string $table table à joinde
	 */
	public function set($array,$params=array())
	{
		if (is_array($array))
		{
			foreach ($array as $k => $v)
			{
				//$this->where($k . ' = :' . $k,array($k => $v));
				$this->query_elements->set[] = $k . ' = :' . $k;
				$this->set_param(array($k => $v));
			}
		}
		else
		{
			$this->query_elements->set[] = $array;
			$this->set_param($params);
		}
		return $this;
	}
	/**
	 * Fonction de jointures
	 * @param string $table table a joindre
	 * @param string $param option de jointure
	 */
	public function join($table, $param)
	{
		$this->query_elements->join[] = ' JOIN ' . $table . ' ON ' . $param . ' ';
		return $this;
	}
	
	public function ijoin($table, $param)
	{
		$this->query_elements->join[] = ' INNER JOIN ' . $table . ' ON ' . $param . ' ';
		return $this;
	}
	
	public function ljoin($table, $param)
	{
		$this->query_elements->join[] = ' LEFT JOIN ' . $table . ' ON ' . $param . ' ';
		return $this;
	}

	public function rjoin($table, $param)
	{
		$this->query_elements->join[] = ' RIGHT JOIN ' . $table . ' ON ' . $param . ' ';
		return $this;
	}

	/**
	 * Fonction d'ajout de condition
	 * @param mixed $param condition : string : condition libre | array : $key = $val
	 * @param string $sign operateur de condition par défaut =
	 * @param string $type operateur de comparaison par défaut AND
	 */
	public function where($condition,$vars=array())
	{
		if ($condition == '')
		{
			foreach ($vars as $k => $v)
			{
				$this->where($k . ' = :' . $k,array($k => $v));
			}
		}
		else
		{
			$this->query_elements->where[] = $condition;
			$this->set_param($vars);
		}
		return $this;
	}

	public function whereor($condition,$vars=array())
	{
		if ($condition == '')
		{
			foreach ($vars as $k => $v)
			{
				$this->whereor($k . ' = :' . $k,array($k => $v));
			}
		}
		else
		{
			foreach($vars as $k => $v)
			{
				$oldk = $k;					
				while (array_key_exists($k, $this->params))
				{
					$k .= '0';
				}			
				if ($k !== $oldk)
				{
					$condition = str_replace(':'.$oldk,':'.$k,$condition);
					unset($vars[$oldk]);
					$vars[$k] = $v;
				}
			}
			
			$this->query_elements->where[count($this->query_elements->where) -1 ] = '(' . $this->query_elements->where[count($this->query_elements->where) -1 ] . ' OR ' . $condition . ')';
			$this->set_param($vars);
			
		}
		return $this;
	}
	
	public function having($condition,$vars=array())
	{
		$this->query_elements->having[] = $condition;
		$this->set_param($vars);
		return $this;
	}
	/**
	 * Fonction d'ajout d'element de Group
	 * @param string $str element à grouper
	 */
	public function groupby($str)
	{
		$this->query_elements->groupby[] = $str;
		return $this;
	}
	
	/**
	 * Fonction d'ajout d'element de Tri
	 * @param string $str element à trier
	 */
	public function order($str)
	{
		$this->query_elements->order[] = $str;
		return $this;
	}
	
	/**
	 * Fonction d'ajout d'element de Limit
	 * @param string $str
	 */
	public function limit($str)
	{
		$this->query_elements->limit = $str;
		return $this;
	}

	/**
	 * Fonction d'ajout d'element d'offset
	 * @param string $str
	 */
	public function offset($str)
	{
		$this->query_elements->offset = $str;
		return $this;
	}
	/**
	 * Fonction d'ajout d'element libres
	 * @param string $str
	 */
	public function free($str)
	{
		$this->query_elements->free = $str;
		return $this;
	}

	function set_param($array)
	{
		$this->params = array_merge($this->params,$array);
		return $this;
	}


	private $fetching = false;
	private $iteratorStmt = null;

	private $iteratorResult = null;
	private $iteratorKey = null;
	private $iteratorValid = false;

	public function current()
    {
        return $this->iteratorResult;
    }

    public function next()
    {
        $this->iteratorKey++;
        $this->iteratorResult = $this->iteratorStmt->fetch( \PDO::FETCH_ASSOC );
        if (false === $this->iteratorResult)
        {
            $this->iteratorValid = false;
            return null;
        }
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
			$this->make_query();
			$this->parent->set_vars($this->query,$this->params);
			$this->iteratorStmt = $this->parent->prepare_and_execute();
			$this->iteratorResult = $this->iteratorStmt->fetch( \PDO::FETCH_ASSOC );
			$this->iteratorValid = true;
		}
        $this->iteratorKey = 0;
    }
}

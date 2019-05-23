<?php

namespace Core\Classes\DB;

class pdodb
{
	protected $host = '';
	protected $user = '';
	protected $pass = '';
	protected $db = '';
	protected $lasterror = '';
	
	private $dbh;
	private $_connected = false;
	private $query = '';
	private $params = array();
	
	function __construct($host = null,$user = null,$pass = null,$db = null)
	{
		$this->set_params($host,$user,$pass,$db);
	}
	
	function set_params($host = null,$user = null,$pass = null,$db = null)
	{
		if ($host !== null) {$this->host = $host;}
		if ($user !== null) {$this->user = $user;}
		if ($pass !== null) {$this->pass = $pass;}
		if ($db !== null) {$this->db = $db;}			
	}
	
	function connect($host = null,$user = null,$pass = null,$db = null)
	{
		$this->set_params($host,$user,$pass,$db);
		
		try 
		{
			$this->dbh = $this->create_pdo();
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->_connected = true;
		} 
		catch( \PDOException $e ) 
		{
			\HTTP::error_page('500',$e->getMessage());
			//\error::set('ERROR',$e->getMessage());
		} 
	}
	
	function create_pdo()
	{
		return new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->pass,array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	}
	
	function request($query,$params=array(),$getparam=\PDO::FETCH_ASSOC)
	{
		$this->query = $query;
		$this->params = $params;
		return $this->get($getparam);
	}
	
	function query($query,$params=array())
	{
		$this->query = $query;
		$this->params = $params;
		
		if ($stmt = $this->prepare_and_execute())
		{
			$Rows = $stmt->rowCount();
			return $Rows;
		}
	}
	
	function new_req()
	{
		if (!($this->_connected))
		{
			$this->connect();
		}
		//-- on cree une nouvelle requete
		$this->requester = new requester($this);
		return $this->requester;
	}		
	
	function select($arg='*')
	{
		if (!($this->_connected))
		{
			$this->connect();
		}
		//-- on cree une nouvelle requete
		$this->requester = new Requester($this);
		return $this->requester->select($arg);
	}
		
	function delete()
	{
		if (!($this->_connected))
		{
			$this->connect();
		}
		//-- on cree une nouvelle requete
		$this->requester = new Requester($this);
		return $this->requester->delete();
	}
	
	function set_vars($query = null,$params = null)
	{
		if ($query !== null) {$this->query = $query;}
		if ($params !== null) {$this->params = $params;}
	}
	
	private function prepare_and_execute()
	{
		if (!($this->_connected))
		{
			$this->connect();
		}
		try 
		{
			\timer::start();
			$stmt = $this->dbh->prepare($this->query);
			$stmt->execute($this->params);
			\Debug::sql($this->query,\timer::gettime(),'',$this->params);
			return $stmt;
		} 
		catch( \PDOException $e ) 
		{
			\debug::sql($this->query,\timer::gettime(),$e->getMessage(),$this->params);
			$this->lasterror = $e->getMessage();
			return null;
		} 
	}
	
	function get($mode = \PDO::FETCH_ASSOC)
	{
		//$mode = \PDO::FETCH_ASSOC|\PDO::FETCH_GROUP;
		if ($stmt = $this->prepare_and_execute())
		{
			$Rows = $stmt->fetchAll($mode);
			if (method_exists($this,'convert_val'))
			{
				array_walk_recursive($Rows, array($this,'convert_val'));
			}
			return $Rows;
		}
		return null;
	}
	
	public function findFirst()
	{
		if ($stmt = $this->prepare_and_execute())
		{
			$Rows = $stmt->fetch(\PDO::FETCH_ASSOC);
			return $Rows;
		}
		return null;
	}	
	
	public function fetch()
	{
		if ($stmt = $this->prepare_and_execute())
		{
			return new pdofetcher($stmt);
		}
		return null;
	}
	
	public function exec($query=null)
	{
		if ($query !== null)
		{
			return $this->dbh->exec($query);
		}
		else
		{
			if ($stmt = $this->prepare_and_execute())
			{
				$Rows = $stmt->rowCount();
				return $Rows;
			}
		}
	}
	
	private function _request_insert()
	{
		$stmt = $this->prepare_and_execute();
		if ($stmt !== null)
		{
			if (get_class($this) != 'DB\\mssql')
			{
				return $this->dbh->lastInsertId(); 
			}
			else
			{
				$data = $this->select('IDENT_CURRENT(\'' . $table . '\') id')->findfirst();
				return $data['id'];
			}
		}
		else
		{
			return null;
		}
	}

	public function insert($table,$values,$params = array(),$valuesNotPrepared=[])
	{
		$ignore = (in_array('IGNORE',$params))?'IGNORE':'';
				
		$sqlvalues = array_map(function($a){return ':'.$a;},array_keys($values));
		
		$this->query = 'INSERT ' . $ignore. ' INTO ' . $table . ' (' . implode(', ',array_merge(array_keys($values),array_keys($valuesNotPrepared))) . ') 
						VALUES ( ' . implode(', ',array_merge($sqlvalues,$valuesNotPrepared)) . ')';
		$this->params = $values;
		return $this->_request_insert();
	}

	public function replace($table,$values,$params = array())
	{
		$this->query = 'REPLACE  INTO ' . $table . ' (' . implode(', ',array_keys($values)) . ') VALUES ( :' . implode(', :',array_keys($values)) . ')';
		$this->params = $values;
		return $this->_request_insert();
	}
	
	public function qinsert($query,$params = array())
	{
		$this->query = $query;
		$this->params = $params;
		return $this->_request_insert();
	}
	
	public function update($table,$values=null,$condition=null)
	{
		if ( ($values === null) && ($condition === null) )
		{
			if (!($this->_connected))
			{
				$this->connect();
			}
			//-- on cree une nouvelle requete
			$this->requester = new requester($this);
			return $this->requester->update($table);
		}
		else
		{
			foreach(array_keys($values) as $k)
			{
				$val[] = $k . ' = :' . $k;
			}
			foreach(array_keys($condition) as $k)
			{
				$cond[] = $k . ' = :' . $k;
			}

			$this->query = 'UPDATE ' . $table . ' SET ' . implode(', ',$val) . ' WHERE ' . implode('AND ',$cond);
			$this->params = array_merge($values,$condition);
			
			if ($stmt = $this->prepare_and_execute())
			{
				$Rows = $stmt->rowCount();
				return $Rows;
			}
			return false;
			//echo $this->query;
		}
	}
	
	public function get_error()
	{
		return $this->lasterror;
	}
}

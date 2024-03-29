<?php
namespace Core\Classes\Providers\DB;

use Core\Classes\Providers\DB\QueryBuilder;
use Core\Classes\Timer;

abstract class PDO
{
	protected $host = '';
	protected $user = '';
	protected $pass = '';
	protected $db = '';

	protected $lasterror = '';
	private $dbh;
	private $_connected = false;
	private $withoutException = false;

	abstract function createPdo() : \PDO;

	function __construct($host = null,$user = null,$pass = null,$db = null)
	{
		$this->setParams($host,$user,$pass,$db);
	}

	public function setWithoutExceptions($val)
	{
		$this->withoutException = $val;
	}

	public function allowDeleteAliasTable()
	{
		return true;
	}

	function setParams($host = null,$user = null,$pass = null,$db = null)
	{
		if ($host !== null) {$this->host = $host;}
		if ($user !== null) {$this->user = $user;}
		if ($pass !== null) {$this->pass = $pass;}
		if ($db !== null) {$this->db = $db;}
	}

	function connect($host = null,$user = null,$pass = null,$db = null)
	{
		$this->setParams($host,$user,$pass,$db);

		$this->dbh = $this->createPdo();
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ );

		$this->_connected = true;
	}

	public function setAttribute($key, $val)
	{
		$this->dbh->setAttribute($key, $val);
	}

	public function execute(QueryBuilder|string $qb,array $params = []) : QueryResult
	{
		if (!($this->_connected))
		{
			$this->connect();
		}

		if(is_string($qb))
		{
			$qb = new QueryBuilder($this, $qb, $params);
		}

		Timer::start();

		try
		{
			$stmt = $this->dbh->prepare($qb->getQuery());
		}
		catch(\Exception $e)
		{
			$ret = new QueryResult(['qb' => $qb,
				                      'stmt' => null,
				                      'time' => Timer::gettime(),
				                      'errorCode' => $this->dbh->errorCode(),
				                      'errorInfo' => $this->dbh->errorInfo(),
				                      'nbLigne' => 0]);
			if(!$this->withoutException)
			{
				throw $e;
			}

			return $ret;
		}

		try
		{
			$stmt->execute($qb->getParams());

			return new QueryResult(['qb' => $qb,
									'stmt' => $stmt,
									'time' => Timer::gettime(),
									'errorCode' => null,
									'errorInfo' => null,
									'nbLigne' => $stmt->rowCount()]);
		}
		catch(\Exception $e)
		{
			$ret =  new QueryResult(['qb' => $qb,
				                       'stmt' => $stmt,
				                       'time' => Timer::gettime(),
				                       'errorCode' => $stmt->errorCode(),
				                       'errorInfo' => $stmt->errorInfo(),
				                       'nbLigne' => $stmt->rowCount()]);

			if(!$this->withoutException)
			{
				throw $e;
			}

			return $ret;
		}
	}

	public function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}

	public function importFile(string $filename)
	{
		$ret = [];
		$op_data = '';
		$lines = file($filename);
		foreach ($lines as $line)
		{
			if (substr($line, 0, 2) == '--' || $line == '')
			{
				continue;
			}
			$op_data .= $line;
			if (substr(trim($line), -1, 1) == ';')//Breack Line Upto ';' NEW QUERY
			{
				$ret[] = $this->execute ($op_data);

				$op_data = '';
			}
		}

		return $ret;
	}
}
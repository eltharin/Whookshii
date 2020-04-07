<?php
namespace Core\Classes\Providers\DB;

use Core\Classes\DB\QueryBuilder;
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

	abstract function createPdo() : \PDO;

	function __construct($host = null,$user = null,$pass = null,$db = null)
	{
		$this->setParams($host,$user,$pass,$db);
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
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT );
		$this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ );

		$this->_connected = true;
	}

	public function execute(QueryBuilder $qb)
	{
		if (!($this->_connected))
		{
			$this->connect();
		}

			Timer::start();
			$stmt = $this->dbh->prepare($qb->getQuery());
			if(!$stmt)
			{
				\Debug::sql($qb->getQuery(),Timer::gettime(),$this->dbh->errorInfo(),$qb->getParams());
				return null;
			}
			$stmt->execute($qb->getParams());
			\Debug::sql($qb->getQuery(),Timer::gettime(),'',$qb->getParams());
			return $stmt;
	}

	public function fetchAll($mode)
	{
		return $stmt->fetchAll($mode);
	}

	public function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}
}
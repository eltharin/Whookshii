<?php
namespace Core\Classes\Providers\DB;

class SQLite extends PDO
{
	function createPdo() : \PDO
	{
		$options = array(\PDO::SQLSRV_ATTR_ENCODING => \PDO::SQLSRV_ENCODING_UTF8);
		$pdo = new \PDO('sqlsrv:Server=' . $this->host . ';Database=' . $this->db, $this->user,$this->pass, $options);
		return $pdo;
	}
}
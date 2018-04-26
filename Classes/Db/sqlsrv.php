<?php

namespace Core\Classes\DB;

class sqlsrv extends pdodb
{
	function create_pdo()
	{
		$options = array(\PDO::SQLSRV_ATTR_ENCODING => \PDO::SQLSRV_ENCODING_UTF8);
		$pdo = new \PDO('sqlsrv:Server=' . $this->host . ';Database=' . $this->db, $this->user,$this->pass, $options);
		return $pdo;
	}
}

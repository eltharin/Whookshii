<?php
namespace Core\Classes\Providers\DB;

class Mysql extends PDO
{
	function createPdo() : \PDO
	{
		return new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->pass,array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	}
}
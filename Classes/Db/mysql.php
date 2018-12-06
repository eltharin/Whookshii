<?php

namespace Core\Classes\DB;

class Mysql extends Pdodb
{
	function create_pdo()
	{
		return new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->pass,array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	}	
}

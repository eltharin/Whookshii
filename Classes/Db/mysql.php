<?php

namespace Core\Classes\DB;

class mysql extends pdodb
{
	function create_pdo()
	{
		return new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->pass,array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	}	
}

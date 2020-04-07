<?php
namespace Core\Classes\Providers\DB;

class SQLite extends PDO
{
	function createPdo() : \PDO
	{
		return new \PDO('sqlite:' . $this->host, null, null);
	}
}
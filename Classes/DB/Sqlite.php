<?php

namespace Core\Classes\DB;

class Sqlite extends pdodb
{
	function create_pdo()
	{
		return new \PDO('sqlite:' . $this->host, null, null);
	}
}

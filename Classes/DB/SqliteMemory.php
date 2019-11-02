<?php

namespace Core\Classes\DB;

class SqliteMemory extends Pdodb
{
	function create_pdo()
	{
		return new \PDO('sqlite::memory:', null, null);
	}
}

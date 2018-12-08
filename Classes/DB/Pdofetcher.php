<?php

namespace Core\Classes\DB;

class pdofetcher
{
	private $stmt;
	function __construct($stmt)
	{
		$this->stmt = $stmt;
	}

	function next($mode = \PDO::FETCH_ASSOC)
	{
		if ($this->stmt !== null)
		{
			return $this->stmt->fetch($mode);
		}
		return null;
	}
}

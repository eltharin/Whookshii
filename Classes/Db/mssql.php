<?php

namespace Core\Classes\DB
{
	class mssql extends pdodb
	{
		function create_pdo()
		{
			$pdo = new \PDO('odbc:Driver={SQL Server};Server=' . $this->host . ';Database=' . $this->db . ';charset=UTF-8;client_charset=UTF-8;', $this->user,$this->pass);
			return $pdo;
		}
		
		function convert_val(&$val,$key)
		{
			$val = iconv("iso-8859-2","utf-8",$val);
		}
	}
}
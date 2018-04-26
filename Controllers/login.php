<?php
namespace Core\Controllers;

class login extends \Core\App\Mvc\Controller
{
	function Action_connect()
	{
		\core::$auth->connect();
	}

	function Action_disconnect()
	{
		\auth::disconnect();
	}

	function connect($user,$pass)
	{
		if ($this->login->connect_test($user,$pass))
		{
			return true;
		}
		else
		{
			\core::$auth->clean();
			return false;
		}
	}
}

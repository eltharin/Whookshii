<?php
namespace Core\App\Middleware;

class Launcher extends MiddlewareAbstract
{
	private $schema = 'controller/model';
	private $namespace = '';
	private $controller = '';
	private $action = '';
	private $params = [];

	public function beforeProcess()
	{
		$controller = \Core::$request->schema->controller;
		$action     = strtolower(\Core::$request->schema->action??'index');
		$params     = \Core::$request->schema->params;
		
		if($action == '')
		{
			$action = 'index';
		}		
		
		session_write_close();
		$controller->launch_function($action,$params);
		$s = $_SESSION;
		session_start();
		$_SESSION = $s;
		unset($s);
	}
}

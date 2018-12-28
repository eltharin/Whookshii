<?php

class Core
{
	public static $request = null;
	public static $response = null;
	public static $config = null;
	
	private static $dispatcher = null;
	public static $db = null;



	public static function init()
	{
		self::$request = new \Core\App\Request();
		self::$response = new \Core\App\Response();

		define('BASE_URL',self::$request->get_subfolder());

		class_alias('\\Core\\App\\Http','HTTP');
		class_alias('\\Core\\App\\Html','HTML');
		class_alias('\\Core\\App\\Auth','Auth');
		class_alias('\\Core\\App\\ACL' ,'ACL');

		\Auth::init();
		\config::init();
		
		self::$config = new \config();
	}

	public static function launch_middleware()
	{
		self::init();

		self::$dispatcher = new \Core\App\Dispatcher('Core\\App\\Middleware\\Router');

		self::$dispatcher->add_middleware('Core\\App\\Middleware\\Subfolder');
		self::$dispatcher->add_middleware('Core\\App\\Middleware\\FileLoader');
		self::$dispatcher->add_middleware('Core\\App\\Middleware\\Config');

		self::$dispatcher->add_middleware('Core\\App\\Middleware\\Xhprof');

		//self::$dispatcher->add_middleware('Core\\App\\Middleware\\DebugBar');
		self::$dispatcher->add_middleware('Core\\App\\Middleware\\Templater');
		

		try
		{
			self::$dispatcher->handle();
		}
		catch(\Exception $e)
		{
		}
		self::$response->render();
	}
	
	public function add_middleware(String $middleware)
	{
		self::$dispatcher->add_middleware($middleware);
	}
	
	public static function stop()
	{
		//throw new \Core\App\Exception\Stop();
	}
}

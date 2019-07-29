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

		class_alias(\Core\App\Http::class,'HTTP');
		class_alias(\Core\App\Html::class,'HTML');
		class_alias(\Core\App\Auth::class,'Auth');
		class_alias(\Core\App\ACL::class,'ACL');

		\Auth::init();
		\config::init();
		
		self::$config = new \config();
	}

	public static function launch_middleware()
	{
		self::init();

		self::$dispatcher = new \Core\App\Dispatcher(\Core\App\Middleware\Launcher::class);

		self::$dispatcher->add_middleware(\Core\App\Middleware\Subfolder::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\FileLoader::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\Config::class);
		//self::$dispatcher->add_middleware(\Core\App\Middleware\Templater::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\Router::class);
		
		self::$dispatcher->handle();
		
		self::$response->render();
	}
	
	public static function add_middleware(String $middleware)
	{
		self::$dispatcher->add_middleware($middleware);
	}
	
	public static function stop()
	{
		//throw new \Core\App\Exception\Stop();
	}

	public static function doBreak()
	{
		throw new \Core\App\Exception\DoBreak();
	}
}

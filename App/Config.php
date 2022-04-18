<?php
namespace Core\App;

use Core\App\Config\AbstractConfigElement;
use Core\App\Config\Errors;
use Core\App\Config\Middlewares;
use Core\App\Config\Providers;
use Core\App\Config\Response;
use Core\App\Config\Routes;
use Core\App\Config\Vars;

class Config
{
	private static $elements;

	public static function init()
	{
		self::$elements = new \stdClass;
		
		self::createElement('Vars', Vars::class);
		self::createElement('Middlewares', Middlewares::class);
		self::createElement('Routes', Routes::class);
		self::createElement('Providers', Providers::class);
		self::createElement('Response', Response::class);


		\Config::get('Vars')->LoadConfig();
		\Config::get('Middlewares')->LoadConfig();
		\Config::get('Routes')->LoadConfig();
		\Config::get('Providers')->LoadConfig();

		if(file_exists(CONFIG . 'app.config.php'))
		{
			require_once(CONFIG . DS . 'app.config.php');
		}

		/*if(file_exists(SPECS . 'config.inc.php'))
		{
			\Config::get('Vars')->addConfig(SPECS . 'config.inc.php',false);
		}*/


	}

	public static function createElement($name, $class)
	{
		self::$elements->$name = new $class();
	}

	public static function set($configName, $value)
	{
		self::$elements->$configName = $value;
	}

	public static function get($configName) : ?AbstractConfigElement
	{
		if(isset(self::$elements->$configName))
		{
			return self::$elements->$configName;
		}
		return null;
	}

	public static function getVars() : Vars
	{
		return self::get('Vars');
	}

	public static function getErrors() : Errors
	{
		return self::get('Errors');
	}

	public static function getMiddlewares() : Middlewares
	{
		return self::get('Middlewares');
	}

	public static function getRoutes() : Routes
	{
		return self::get('Routes');
	}

	public static function getProviders() : Providers
	{
		return self::get('Providers');
	}

	public static function getResponse() : Response
	{
		return self::get('Response');
	}
	
	public static function noLimit()
	{
		ini_set('memory_limit',-1);
		set_time_limit(-1);
	}
}
<?php
namespace Core\App;

class Config
{
	private static $elements;

	public static function init()
	{
		self::$elements = new \stdClass;
		
		self::createElement('Vars',\Core\App\Config\Vars::class);
		self::createElement('Middlewares',\Core\App\Config\Middlewares::class);
		self::createElement('Routes',\Core\App\Config\Routes::class);
		self::createElement('Providers',\Core\App\Config\Providers::class);
		self::createElement('Response',\Core\App\Config\Response::class);
	}

	public static function createElement($name, $class)
	{
		self::$elements->$name = new $class();
	}

	public static function set($configName, $value)
	{
		self::$elements->$configName = $value;
	}

	public static function get($configName)
	{
		if(isset(self::$elements->$configName))
		{
			return self::$elements->$configName;
		}
		return null;
	}
	
	
	public static function noLimit()
	{
		ini_set('memory_limit',-1);
		set_time_limit(-1);
		
	}
}
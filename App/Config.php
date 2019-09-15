<?php
namespace Core\App;

class Config
{
	private static $elements;

	public static function init()
	{
		self::$elements = new \stdClass;
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
}
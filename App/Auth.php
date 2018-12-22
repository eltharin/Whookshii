<?php

namespace Core\App;

class auth
{
	static $infos = null;
	static $goawwaymessage = 'Vous devez vous connecter pour arriver sur cette page.';

	public static function init()
	{
		if (!isset($_SESSION['_auth']))
		{
			$_SESSION['_auth'] = array();
		}
		self::$infos = &$_SESSION['_auth'];
		
		if (!isset(self::$infos['connected']))
		{
			self::$infos['connected'] = false;
		}
		call_user_func_array('static::_init', func_get_args());
	}
	public static function _init(){}
	
	public static function connect($data=[])
	{
		if(!empty($data))
		{
			self::set_infos($data);
		}
		
		$connector = (strpos(get_called_class(), '\\') ? '\\' . get_called_class() : get_called_class());
		if (!self::is_connected())
		{
			if (call_user_func_array('static::_connect', func_get_args()))
			{
				self::$infos['connected'] = 1;
				self::$infos['connector'] = $connector;
			}
		}
	}

	public static function _connect()
	{
		return true;
	}

	public static function _disconnect()
	{
		//return false;
	}

	public static function is_connected()
	{
		return $_SESSION['_auth']['connected'];
	}

	public static function kill_anonyme()
	{
		if (!self::is_connected())
		{
			\HTTP::error_page('403',self::$goawwaymessage);
		}
	}

	public static function set_infos($data)
	{
		self::$infos = array_merge(self::$infos,$data);
	}

	public static function disconnect()
	{
		call_user_func_array(self::$infos['connector'] . '::_disconnect', func_get_args());
		self::$infos = array('connected'=>false);
		
	}
	
	public static function get($k)
	{
		if(in_array($k,array_keys(self::$infos)))
		{
			return self::$infos[$k];
		}
		else
		{
			return null;
		}
	}
}

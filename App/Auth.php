<?php

namespace Core\App;

class auth
{
	static $infos = [];
	static $goawwaymessage = 'Vous devez vous connecter pour arriver sur cette page.';

	public static function init()
	{
	    if(session_status () == \PHP_SESSION_NONE )
        {
            session_start();
        }

		if (!isset($_SESSION['_auth']))
		{
			$_SESSION['_auth'] = array();
		}
		static::$infos = &$_SESSION['_auth'];
		
		if (!isset(static::$infos['connected']))
		{
			static::$infos['connected'] = false;
		}
		call_user_func_array('static::_init', func_get_args());
	}

	public static function _init(){}

	public static function connect($data=[])
	{
		if(!empty($data))
		{
			static::set_infos($data);
		}

		$connector = (strpos(get_called_class(), '\\') ? '\\' . get_called_class() : get_called_class());
		if (!static::is_connected())
		{
			if (call_user_func_array('static::_connect', func_get_args()))
			{
				static::$infos['connected'] = 1;
				static::$infos['connector'] = $connector;
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
		return static::$infos['connected'] == true;
	}

	public static function kill_anonyme()
	{
		if (!static::is_connected())
		{
			\HTTP::errorPage('403',static::$goawwaymessage);
		}
	}

	public static function set_infos($data)
	{
		static::$infos = array_merge(static::$infos,$data);
	}

	public static function disconnect()
	{
		call_user_func_array(static::$infos['connector'] . '::_disconnect', func_get_args());
		static::$infos = array('connected'=>false);

	}
	
	public static function get($k)
	{
		if(in_array($k,array_keys(static::$infos)))
		{
			return static::$infos[$k];
		}
		else
		{
			return null;
		}
	}
}

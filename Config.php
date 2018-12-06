<?php

class Config
{
	use \Core\App\Config\HTMLPage;

	//public static $template;
	public static $routes;
	static private $testmode = false;

	public static function init()
	{
		self::$routes = new \Core\App\Config\Routes();
		//self::$template = new \Core\App\Config\HTMLElements();
	}
	
	public static function set_test($val)
	{
		self::$testmode = $val;
	}

	public static function is_test()
	{
		return self::$testmode;
	}
	
	public static function set_snap_after_redirect($msg, $color="yellow", $duration=5000)
	{
		$_SESSION['_snap_after_redirect'][] = ['msg' => str_replace('\'','\\\'',$msg), 'color' => $color, 'duration' => $duration];
	}
	
	public static function get_snap()
	{
		if(isset($_SESSION['snap_after_redirect']))
		{
			foreach($_SESSION['snap_after_redirect'] as $snap)
			{
				echo 'ohSnap(\''.$snap['msg'].'\', {color: \''.$snap['color'].'\', duration: \''.$snap['duration'].'\'}); '.RN;
			}
			unset($_SESSION['snap_after_redirect']);
		}
		if(isset($_SESSION['_snap_after_redirect']))
		{
			$_SESSION['snap_after_redirect'] = $_SESSION['_snap_after_redirect'];
			unset($_SESSION['_snap_after_redirect']);
		}
	}
	
	public static function noLimit()
	{
		ini_set('memory_limit',-1);
		set_time_limit(-1);
		
	}
}
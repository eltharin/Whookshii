<?php

class Config
{
	private static $instance;
	public static $routes;
	static private $testmode = false;

	public function __construct()
	{
		self::$instance = $this;
		self::$routes = new \Core\App\Config\Routes();
	}
	
	public function __invoke(?string $element = null)
	{
		return $element?self::$instance->$element:self::$instance;
	}
	
	public static function init()
	{
		self::$routes = new \Core\App\Config\Routes();
		
		//$className = get_class();
		//s/elf::$instance = new $className();
		//self::$template = new \Core\App\Config\HTMLElements();
	}
	
	public function addElement(string $name,Core\App\Config\ConfigAbstract $element)
	{
		$this->$name = $element;
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
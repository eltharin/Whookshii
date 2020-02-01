<?php
namespace Core\Classes;

class Timer
{
	//! Timer value
	private static $timer=0;

	function __construct()
	{
		$this->start();
	}
	//! Start the Timer
	static function start ()
	{
		self::$timer = microtime(true);

		return true;
	}
	//! Set the Timer
	static function set ($time)
	{
		self::$timer = $time;

		return true;
	}	
	/**
	* Get the current time of the timer
	*
	* @param $decimals number of decimal of the result
	* @return time past from start
	*
	*/
	static function getTime ($decimals = 3)
	{
		// $decimals will set the number of decimals you want for your milliseconds.
		return number_format(microtime(true) - self::$timer,$decimals,'.',' ');
	}
}
?>

<?php
namespace Core\App;

use Core\Classes\Debug;

class Errors
{
	public static function printError($errno, $errstr, $errfile, $errline,$errcontext)
	{
		\debug::error($errno, $errstr, $errfile, $errline,$errcontext);
	}
	
	public static function logError($errno, $errstr, $errfile, $errline,$errcontext)
	{
		\debug::error($errno, $errstr, $errfile, $errline,$errcontext);
	}
}
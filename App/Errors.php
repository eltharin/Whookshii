<?php
namespace Core\App;

class Errors
{
	public static function print_error($errno, $errstr, $errfile, $errline,$errcontext)
	{
		\debug::error($errno, $errstr, $errfile, $errline,$errcontext);
	}
	
	public static function log_error($errno, $errstr, $errfile, $errline,$errcontext)
	{
		\debug::error($errno, $errstr, $errfile, $errline,$errcontext);
	}
}
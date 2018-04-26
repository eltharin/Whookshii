<?php
namespace Core\Classes;

class regex
{
	static function integer()
	{
		return '([0-9]+)';
	}
	
	static function size($max,$min=0)
	{
		return '(.{'. $min . ','. $max . '})';
	}
	
	static function notcontain($val)
	{
		return '((?!' . $val .').)*';
	}
}
<?php

namespace Core\Classes\Functions;

class Date
{
	static function date($format, $timestamp)
	{
		if ($timestamp == '')
		{
			return '';
		}

		return date($format, $timestamp);
	}
}
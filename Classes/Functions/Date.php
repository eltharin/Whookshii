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

	static function fromFormat($format, $str)
	{
		if ($str == '')
		{
			return '';
		}

		return date_create_from_format($format, $str)->format('U');
	}

	public static function fromSemaine($semaine, $annee, $jour = 1)
	{
		return date_isodate_set (date_create ('@-62169984000'), $annee, $semaine, $jour)->format ('U');
	}
}
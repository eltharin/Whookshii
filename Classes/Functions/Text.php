<?php
namespace Core\Classes\Functions;

class Text
{
	static function slugify($string, $delimiter = '-')
	{
		$oldLocale = setlocale(LC_ALL, '0');
		setlocale(LC_ALL, 'en_US.UTF-8');

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower($clean);
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		$clean = trim($clean, $delimiter);

		setlocale(LC_ALL, $oldLocale);

		return $clean;
	}
}
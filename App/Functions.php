<?php

namespace func
{

	function phparray($min, $max, $pas = 1)
	{
		$ret = array();
		for ($i = $min; $i <= $max; $i += $pas)
		{
			$ret[$i] = $i;
		}
		return $ret;
	}

	function uniqid()
	{
		return sprintf('%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff));
	}

}

namespace func\liste
{

	function month()
	{
		$ret = array();
		for ($i = 1; $i <= 12; $i++)
		{
			$ret[substr('00' . $i, -2, 2)] = ucfirst(utf8_encode(strftime('%B', mktime(0, 0, 0, $i))));
		}

		return $ret;
	}

}

namespace
{

	function suppr_accents($str)
	{
		$replace = array('à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
			'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
			'õ' => 'o', 'ö' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
			'ý' => 'y', 'ÿ' => 'y', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A',
			'Ä' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
			'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O',
			'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
			'Ü' => 'U', 'Ý' => 'Y',);
		return str_replace(array_keys($replace), array_values($replace), $str);
	}

	function checksecu($secu)
	{
		
		if (strlen($secu) != 15)
		{
			
			return false;
		}
		$nir = substr($secu, 0, 13);
		$cle = substr($secu, -2);

		return (97 - (fmod($nir, 97))) == $cle;
	}

	function checksiret($siret)
	{
		if (strlen($siret) != 14)
		{
			return false;
		}
		return checkLuhn(substr($siret, 0, 9)) && checkLuhn($siret);
	}

	function checkLuhn($val)
	{
		$len = strlen($val);
		$total = 0;
		for ($i = 1; $i <= $len; $i++)
		{
			$chiffre = substr($val, -$i, 1);
			if ($i % 2 == 0)
			{
				$total += 2 * $chiffre;
				if ((2 * $chiffre) >= 10)
					$total -= 9;
			}
			else
				$total += $chiffre;
		}
		if ($total % 10 == 0)
			return true;
		else
			return false;
	}

	function timestamp_from_format($format, $str)
	{
		$date = \DateTime::createFromFormat($format, $str);
		return ($date == null ? null : $date->format('U'));
	}
	
	function datetime($format, $date)
	{
		$datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
		return ($datetime == null ? null : $datetime->format($format));
	}

}
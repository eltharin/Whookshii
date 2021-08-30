<?php

namespace Core\Classes;

class Func
{
	public static function int2str($nb, $genre = 'M', $allowZero = true)
	{
		if($nb < 0)
		{
			$str = 'moins ' . self::int2str (-$nb, $genre);
		}
		elseif($nb == 0)
		{
			$str =  'zéro';
		}
		elseif($nb > 999999999)
		{
			$str =  self::int2str(intdiv($nb ,1000000000)) . '-milliard' .  ( $nb >= 2000000000 ? 's' : '' ) . ( $nb % 1000000000 != 0 ? '-' . self::int2str($nb % 1000000000) : '' );
		}
		elseif($nb > 999999)
		{
			$str =  self::int2str(intdiv($nb ,1000000)) . '-million' .  ( $nb >= 2000000 ? 's' : '' ) . ( $nb % 1000000 != 0 ? '-' . self::int2str($nb % 1000000) : '' );
		}
		elseif($nb > 1999)
		{
			$str =  self::int2str(intdiv($nb ,1000)) . '-mille' .  ( $nb % 1000 != 0 ? '-' . self::int2str($nb % 1000) : '' );
		}
		elseif($nb > 1000)
		{
			$str =  'mille-' . self::int2str($nb % 1000);
		}
		elseif($nb == 1000)
		{
			$str =  'mille';
		}
		elseif($nb > 199)
		{
			$str =  self::int2str(intdiv($nb ,100)) . '-cents' .  ( $nb % 100 != 0 ? '-' . self::int2str($nb % 100) : '' );
		}
		elseif($nb > 100)
		{
			$str =  'cent-' . self::int2str($nb%100);
		}
		elseif($nb == 100)
		{
			$str =  'cent';
		}
		elseif(($nb >= 70 && $nb <= 79) || ($nb >= 90 && $nb <= 99))
		{
			$str =  self::int2str(intdiv($nb, 10)*10-10) . '-' . (($nb%10 == 1) && (intdiv($nb, 10) != 9 ) ? 'et-' : '') . self::int2str(($nb % 10) +10);
		}
		elseif($nb > 16 && (($nb%10) != 0))
		{
			$str =  self::int2str(intdiv($nb, 10)*10) . '-' . ((($nb%10) == 1)  && (intdiv($nb, 10) != 8 ) ? 'et-' : '') . self::int2str($nb % 10);
		}
		else
		{
			switch($nb)
			{
				case 1 : $str =  'un';break;
				case 2 : $str =  'deux';break;
				case 3 : $str =  'trois';break;
				case 4 : $str =  'quatre';break;
				case 5 : $str =  'cinq';break;
				case 6 : $str =  'six';break;
				case 7 : $str =  'sept';break;
				case 8 : $str =  'huit';break;
				case 9 : $str =  'neuf';break;
				case 10 : $str =  'dix';break;
				case 11 : $str =  'onze';break;
				case 12 : $str =  'douze';break;
				case 13 : $str =  'treize';break;
				case 14 : $str =  'quatorze';break;
				case 15 : $str =  'quinze';break;
				case 16 : $str =  'seize';break;
				case 20 : $str =  'vingt';break;
				case 20 : $str =  'vingt';break;
				case 30 : $str =  'trente';break;
				case 40 : $str =  'quarante';break;
				case 50 : $str =  'cinquante';break;
				case 60 : $str =  'soixante';break;
				case 80 : $str =  'quatre-vingts';break;
			}
		}

		return str_replace(['cents-','quatre-vingts-'], ['cent-','quatre-vingt-'], $str);
	}
}
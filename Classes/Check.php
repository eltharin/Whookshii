<?php
namespace Core\Classes;

class Check
{
	static function email($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL) == false)
		{return false;}
		else	{return true;}
	}
	
	static function length($str,$size,$ope = 'inf')
	{
		switch($ope)
		{
			case 'inf' :	$val = (strlen($str) < $size);
							break;
			case 'ine' :	$val = (strlen($str) <= $size);
							break;
			case 'sup' :	$val = (strlen($str) > $size);
							break;
			case 'sue' :	$val = (strlen($str) >= $size);
							break;
			case 'eq' :	    $val = (strlen($str) == $size);
							break;
			case 'dif' :	$val = (strlen($str) != $size);
							break;
			default :       $val = false;
							break;
		}
		
		return $val;
	}
	
	static function integer($val)
	{
		return (is_int((int)$val) && ((int)$val == $val));
	}
	
	static function val($val,$min=null,$max=null)
	{
		if (($min !== null) && ($val < $min)) {return false;}
		if (($max !== null) && ($val > $max)) {return false;}
		
		return true;
	}
	
	static function yopmail($str)
	{
		if (preg_match('/(yopmail)/',$str))
		{return false;}
		else	{return true;}
	}
	
}

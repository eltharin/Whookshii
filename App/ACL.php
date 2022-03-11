<?php
namespace Core\App;

use Core\App\Exception\HttpException;

class Acl
{
	protected static $roles = array();
	protected static $admin = false;
	protected static $rights = array('allow' => array(),'deny' => array());
	
	public static function debug()
	{
		return self::$roles;
	}

	public static function set($right,$bool=true)
	{
		self::$roles[$right] = $bool;
	}
	
	public static function get($right)
	{
		$args = is_array($right)?$right:func_get_args();
		$ret = false;
		
		foreach($args as $right)
		{
			if (((array_key_exists($right,self::$roles)) && ((is_callable(self::$roles[$right]) ? call_user_func(self::$roles[$right]) : self::$roles[$right]) == true)) || (self::$admin == true))
			{
				$ret = true;
			}
		}

		return $ret;	

	}
	
	public static function check($right, $msg = null)
	{
		$args = is_array($right)?$right:func_get_args();
		
		if (self::get($args))
		{
			return true;
		}
		
		throw new HttpException($msg ?: 'Vous n\'avez pas acces à cette partie', '403');
		
	}
		
	public static function set_admin($bool)
	{
		self::$admin = $bool;
	}
	
	public static function allow($ctrl,$action,$profile)
	{
		self::$rights['allow'][$ctrl][$action][] = $profile;
	}
	
	public static function deny($ctrl,$action,$profile)
	{
		self::$rights['deny'][$ctrl][$action][] = $profile;
	}
			
	
				
	public static function have_right($ctrl,$action)
	{
		if(isset(self::$rights['deny'][$ctrl][$action]) && self::test_right($ctrl,$action,'deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow'][$ctrl][$action]) && self::test_right($ctrl,$action,'allow'))
		{
			return true;
		}
		elseif(isset(self::$rights['deny'][$ctrl][$action]) && self::test_right_all($ctrl,$action,'deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow'][$ctrl][$action]) && self::test_right_all($ctrl,$action,'allow'))
		{
			return true;
		}
		
		elseif(isset(self::$rights['deny'][$ctrl]['*']) && self::test_right($ctrl,'*','deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow'][$ctrl]['*']) && self::test_right($ctrl,'*','allow'))
		{
			return true;
		}		
		elseif(isset(self::$rights['deny'][$ctrl]['*']) && self::test_right_all($ctrl,'*','deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow'][$ctrl]['*']) && self::test_right_all($ctrl,'*','allow'))
		{
			return true;
		}
		
		elseif(isset(self::$rights['deny']['*']['*']) && self::test_right('*','*','deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow']['*']['*']) && self::test_right('*','*','allow'))
		{
			return true;
		}
		elseif(isset(self::$rights['deny']['*']['*']) && self::test_right_all('*','*','deny'))
		{
			return false;
		}
		elseif(isset(self::$rights['allow']['*']['*']) && self::test_right_all('*','*','allow'))
		{
			return true;
		}
		return true;
	}
	
	public static function test_right($ctrl,$action,$right)
	{
		foreach(self::$rights[$right][$ctrl][$action] as $act)
		{
			if(\ACL::get($act))
			{
				\debug::msg($ctrl . ' ' . $action . ' ' . $right . ' ' . '1');
				return true;
			}
		}
		\debug::msg($ctrl . ' ' . $action . ' ' . $right . ' ' . '0');
		return false;
	}
	
	public static function test_right_all($ctrl,$action,$right)
	{
		if (in_array('*',self::$rights[$right][$ctrl][$action]))
		{
			\debug::msg($ctrl . ' ' . $action . ' ' . $right . ' ' . '* 1');
			return true;
		}
		\debug::msg($ctrl . ' ' . $action . ' ' . $right . ' ' . '* 0');
		return false;
	}
	
	
	public static function have_rightold($ctrl,$right,$action)
	{
		\debug::msg('try : ' . $ctrl . ' - ' . $right . ' - ' . $action);
		
		//if(!isset(self::$rights[$ctrl]))
		if(($right == 'allow') && !in_array('*',array_keys(self::$rights[$right])) && !in_array($ctrl,array_keys(self::$rights[$right])))
		{
			\debug::msg($right . ' - ' . $action . ' - ' . '0');
			return true;
		}
		
		if(($right == 'deny') && !in_array('*',array_keys(self::$rights[$right])) && !in_array($ctrl,array_keys(self::$rights[$right])))
		{
			\debug::msg($right . ' - ' . $action . ' - ' . '0');
			return false;
		}
		
		if(isset(self::$rights[$right][$ctrl]))
		{
			$tabright = self::$rights[$right][$ctrl];
			\debug::msg($right . ' - ' . $action . ' - ' . print_r($tabright,1));
			if(($right == 'allow') && !in_array('*',array_keys($tabright)) && !in_array($action,array_keys($tabright)))
			{
				\debug::msg($right . ' - ' . $action . ' - ' . '1');
				return true;
			}

			if(isset($tabright[$action]))
			{
				foreach($tabright[$action] as $act)
				{
					if($act == '*' || \ACL::get($act))
					{
						\debug::msg($right . ' - ' . $action . ' - ' . '2');
						return true;
					}
				}
			}

			if(isset($tabright['*']))
			{
				foreach($tabright['*'] as $act)
				{
					if($act == '*' || \ACL::get($act))
					{
						\debug::msg($right . ' - ' . $action . ' - ' . '3');
						return true;
					}
				}
			}
		}
		
		if(isset(self::$rights[$right]['*']))
		{
			$tabright = self::$rights[$right]['*'];
			\debug::msg($right . ' - ' . $action . ' - ' . print_r($tabright,1));
			if(($right == 'allow') && !in_array('*',array_keys($tabright)) && !in_array($action,array_keys($tabright)))
			{
				\debug::msg($right . ' - ' . $action . ' - ' . '11');
				return true;
			}

			if(isset($tabright[$action]))
			{
				foreach($tabright[$action] as $act)
				{
					if($act == '*' || \ACL::get($act))
					{
						\debug::msg($right . ' - ' . $action . ' - ' . '12');
						return true;
					}
				}
			}

			if(isset($tabright['*']))
			{
				foreach($tabright['*'] as $act)
				{
					if($act == '*' || \ACL::get($act))
					{
						\debug::msg($right . ' - ' . $action . ' - ' . '13');
						return true;
					}
				}
			}
		}
	}
}
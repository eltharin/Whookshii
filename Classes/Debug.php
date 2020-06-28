<?php
namespace Core\Classes;

class Debug
{

	private static $val;
	private static $timer;
	
	static function print_debug()
	{
		//print_msg

		if ($_SESSION['debug_mode'] === true)
		{
			echo '<div class="debug">';
			
			\DEBUG::print_error();
			echo \DEBUG::print_sql();
			\DEBUG::print_msg();

			echo '<h1>SESSION</h1>' . \DEBUG::print_array(array_filter($_SESSION, function($a){ return !(substr($a,0,12) == '__internal__');}, ARRAY_FILTER_USE_KEY));
			echo '<h1>POST</h1>' . \DEBUG::print_array($_POST);
			echo '<h1>GET</h1>' . \DEBUG::print_array($_GET);


			echo '</div>';
		}

	}
	
	static function get($key)
	{
		if (isset(self::$val[$key]))
		{
			return self::$val[$key];
		}
		else
		{
			return null;
		}
	}
	
	static function start_timer()
	{
		self::$timer = microtime(true);
		return self::$timer;
	}
	
	static function get_time()
	{
		\timer::set(self::$timer);
		return \timer::gettime();
	}
	
	static function sql ($req,$time=0,$error=0,$params=array())
	{
		self::$val['sql'][] = array('query'=>$req,'params'=>$params,'time'=>$time,'error'=>$error);
	}
	
	static function error ($errno, $errstr, $errfile, $errline,$errcontext)
	{
		$taberr = array('errno' => $errno,'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline);
		if(!isset(self::$val['error'])) {self::$val['error'] = array();}
		if(!in_array($taberr,self::$val['error']))
		{
			self::$val['error'][] = $taberr;
		}
	}
	
	static function clean_sql()
	{
		if(isset(self::$val['sql']))
		{
			foreach(self::$val['sql'] as $key => $sql)
			{
				if ($sql['error'] == '')
				{
					unset(self::$val['sql'][$key]);
				}
			}
		}
	}
	
	static function get_sql()
	{
		return self::$val['sql'];
	}
	
	static function print_sql()
	{
		$ret = '';
		if (!empty(self::$val['sql']))
		{
			$ret .= '<h1>SQL</h1>';
			$ret .= '<table class="debugtable"><tr><th>query</th><th>params</th><th>time</th><th>error</th></tr>';
			foreach(self::$val['sql'] as $v)
			{
				$query = $v['query'];
				krsort($v['params']);
				foreach($v['params'] as $k=>$p)
				{
					$query = str_replace(':'.$k,'\'' . $p . '\'',$query);
				}
				$ret .= '<tr><td>' . $query . '</td><td>' . self::print_array($v['params']) . '</td><td>' . $v['time'] . '</td><td>' . $v['error'] . '</td></tr>';
			}
			$ret .= '</table>';
		}
		return $ret;
	}
	
	static function print_error()
	{
		$ret = '';

		if (!empty(self::$val['error']))
		{
			
			$ret .= '<h1>Errors</h1>';
			$ret .= '<table class="debugtable"><tr><th>type</th><th>fichier</th><th>ligne</th><th>erreur</th></tr>';
			foreach(self::$val['error'] as $err)
			{
				switch($err['errno'])
				{
					case E_ERROR : $type='ERROR';break;
					case E_WARNING : $type='WARNING';break;
					case E_PARSE : $type='PARSE';break;
					case E_NOTICE : $type='NOTICE';break;
					case E_CORE_ERROR : $type='CORE_ERROR';break;
					case E_CORE_WARNING : $type='CORE_WARNING';break;
					case E_COMPILE_ERROR : $type='COMPILE_ERROR';break;
					case E_COMPILE_WARNING : $type='COMPILE_WARNING';break;
					case E_USER_ERROR : $type='USER_ERROR';break;
					case E_USER_WARNING : $type='USER_WARNING';break;
					case E_USER_NOTICE : $type='USER_NOTICE';break;
					case E_STRICT : $type='STRICT';break;
					case E_RECOVERABLE_ERROR : $type='RECOVERABLE_ERROR';break;
					case E_DEPRECATED : $type='DEPRECATED';break;
					case E_USER_DEPRECATED : $type='USER_DEPRECATED';break;
					default : $type = $err['errno'];break;
				}
				$ret .= '<tr><td>' . $type . '</td><td>' . $err['errfile'] . '</td><td>' . $err['errline'] . '</td><td>' . $err['errstr'] . '</td></tr>';
			}
			$ret .= '</table>';
		}
		echo $ret;
	}
	
	static function print_array($arr,$ret = '')
	{
		if (is_array($arr))
		{
			ksort($arr);
			if (!empty($arr))
			{
				$ret .= '<table class=\'debugtable\'><tr><th>key</th><th></th><th>val</th></tr>';
				foreach($arr as $k => $v)
				{
					$ret .= '<tr><td>' . $k . '</td><td> => </td><td>' . self::print_array($v) . '</td></tr>';
				}
				$ret .= '</table>';
			}
			else
			{
				$ret .= 'array()';
			}
		}
		elseif(is_object($arr) && $arr instanceof \Core\App\Mvc\Entity)
		{
			$arr = $arr->getAllProperties();
			if (!empty($arr))
			{
				$ret .= '<table class=\'debugtable\'><tr><th>key</th><th></th><th>val</th></tr>';
				foreach($arr as $k => $v)
				{
					$ret .= '<tr><td>' . $k . '</td><td> => </td><td>' . self::print_array($v) . '</td></tr>';
				}
				$ret .= '</table>';
			}
			else
			{
				$ret .= 'array()';
			}
		}
		else
		{
			$ret .= $arr;
		}
		return $ret;
	}
	
	static function msg($message)
	{
		self::$val['msg'][] = $message;
	}
	
	static function print_msg()
	{
		$ret = '';
		if (!empty(self::$val['msg']))
		{
			$ret .= '<h1>Message</h1>';
			$ret .= '<table class="debugtable"><tr><th>Message</th></tr>';
			foreach(self::$val['msg'] as $msg)
			{
				$ret .= '<tr><td>' . (is_array($msg)?self::print_array($msg):$msg) . '</td></tr>';
			}
			$ret .= '</table>';
		}
		echo $ret;
	}
}
?>
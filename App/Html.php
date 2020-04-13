<?php
namespace Core\App;

class HTML
{
	
	public static function print_table($table,$head=null)
	{
		$ret = '<table class="tableau">';
		
		if ($head !== null)
		{
			$ret .= '<tr>';
			foreach($head as $d)
			{
				$ret .= '<th>' . $d . '</th>';
			}
			$ret .= '</tr>' . RN;
		}
		
		foreach($table as $t)
		{
			$ret .= '<tr>';
			foreach($t as $d)
			{
				$ret .= '<td>' . $d . '</td>';
			}
			$ret .= '</tr>' . RN;
		}
		$ret .= '</table>' . RN;
		
		return $ret;
	}

	public static function link($link,$val,$param=array())
	{
		$option = "";
		if(!empty($param))
		{
			if(!is_array($param))
			{				
				$option .= ' target="' . $param . '"';
			}
			else 
			{
				foreach($param as $p =>$v)
				{

					$option .= $p.'="'.$v.'"';			
				}
			}
			
				
			
		}	
		
		return '<a href="' . $link . '" ' . $option . '>' . $val . '</a>';
	}

	public static function button($val,$id,$param=array())
	{
		$option = "";
		if(!empty($param))
		{
			if(is_array($param))
			{
				foreach($param as $p => $v)
				{
					$option .= $p.'="'.$v.'"';
				}
			}
			else
			{
				$option .= ' class = "'.$param.'"';
			}
			
		}
		return '<input type="button" id="' . $id . '" '.$option.' value="' . $val . '">';
	}
	
	public static function image($src,$params=array())
	{
		$ret = '<img src="' . $src . '"';
		$ret .= (isset($params['alt'])?' alt="' . $params['alt'] . '"':'');
		$ret .= (isset($params['title'])?' title="' . $params['title'] . '"':'');
		$ret .= '>';
		return $ret;
	}

	public static function pr($tab)
	{
		echo '<pre>' . print_r($tab,1) . '</pre>';
	}
	public static function print_r($tab)
	{
		self::pr($tab);
	}
	
	public static function var_dump($tab)
	{
		echo '<pre>';
		var_dump($tab);
		echo '</pre>';
	}
	
	public static function redirect($page)
	{
		
		if ($_SESSION['use_mode'] == 'DEBUG')
		{
			echo '<a href="' . $page . '">Retour</a>';
		}
		else
		{
			header('Location: ' . $page);
		}
		
	}
	
	public static function aff_date($date,$format='%d/%m/%Y')
	{
		return strftime($format,strtotime($date));
	}
	
	public static function script($string)
	{
		return '<script language="javascript">' . RN . $string . RN . '</script>'.RN;
	}
}
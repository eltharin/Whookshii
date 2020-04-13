<?php
namespace Core\Classes;

class Form
{
	static $defaultsize = 25;
	static $defaultlength = 50;
	static $default = array();
	private static $iteminline = null;
	
	static function load_css()
	{
	}
	
	static function new_form($params = array(),$default=array())
	{
		if (!is_array($params))
		{
			$params = array('action'=>$params);
		}

		self::set_default($default);

		$params = array_merge(array('method'=>'POST',
									'action'=>'',
									'enctype'=>true,
									'id'=>'form',
									'options'=>'',
									'class'=>'',
									'divclass'=>'',
								), $params);
		
		self::load_css();
		$ret = '<form method="' . $params['method'] . '" action="' . $params['action'] . '" id="' . $params['id'] . '" class="' . $params['class'] . '" ' . $params['options'];
		
		if ($params['enctype']==true)
			$ret .= 'ENCTYPE="multipart/form-data"';
		$ret .= '><div class="' . $params['divclass'] . '">';
		
		return $ret;
	}

	static function itemInLine($nb,array $flexSizes = null)
	{
		static::$iteminline = ['max' => $nb, 'nb' => $nb, 'flexSizes' => $flexSizes??array_fill(0,$nb,1)];
	}
	
	static function set_default($default)
	{
		if (!is_array($default))
		{
			self::$default = self::get_mode($default);
		}
		else
		{
			self::$default = $default;
		}
	}

	static function write($string,$param)
	{
		$string = $param['beforefield'] . $string . $param['afterfield'];
		
		
		if ($param['label'] !== null && $param['cotelabel'] == 'r')
		{
			$string = $string . form::label($param['label'],$param['id'],$param['classlabel']);
		}
		elseif($param['type'] == 'radio' || $param['type'] == 'checkbox')
		{
			$string = $string . form::label('',$param['id'],'');
		}	
				
		if($param['noDivItem'] !== true)
		{
			$string = '<div class="item ' . $param['classDivItem'] . '">' . $string . '</div>';
		}
		
		if ($param['label'] !== null && $param['cotelabel'] != 'r')
		{
			$string = form::label($param['label'] . $param['separator'],$param['id'],$param['classlabel']) . $string;
		}
		
		$string = $param['before'] . $string . $param['after'];
		
		
		if(static::$iteminline !== null)
		{
			if($param['noDivElement'] === false)
			{
				$classSize = strpos($param['divclass'],'grid')!==false?'':'grid' . (12 / array_sum(static::$iteminline['flexSizes']) * static::$iteminline['flexSizes'][count(static::$iteminline['flexSizes']) - static::$iteminline['nb']]);
				$string =  '<div class="form-element ' . ($param['divclass']?:'') . '">' . $string . '</div>';
			}
			
			if($param['noDivRow'] === false && static::$iteminline['max'] == static::$iteminline['nb'])
			{
				$string =  '<div class="form-row">' . $string;
			}
			
			static::$iteminline['nb']--;
			
			if(static::$iteminline['nb'] == 0)
			{
				if($param['noDivRow'] === false)
				{
					$string =  $string . '</div>';
				}
				static::$iteminline = null;
			}
		}
		else
		{
			if($param['noDivElement'] === false)
			{
				$string =  '<div class="form-element ' . ($param['divclass']?:'') . '">' . $string . '</div>';
			}
			if($param['noDivRow'] === false)
			{
				$string =  '<div class="form-row">' . $string . '</div>';
			}
		}
		return $string;
	}
	
	static function submit($value = 'Valider',$withendform=true,$name='')
	{
		$data = ['value'=>'Valider','withendform'=>true,'name'=>'','class'=>''];
		
		if(is_array($value))
		{
			$data = array_merge($data,$value);
		}
		else
		{
			$data = array_merge($data,['value'=>$value,'withendform'=>$withendform,'name'=>$name,'class'=>'']);
		}
		
		$ret = '<input type=submit value="' . $data['value'] . '" ' . ($data['name']==''?'':'name = "' . $data['name'] . '"') .' ' . ($data['class']==''?'':'class = "' . $data['class'] . '"') .'>';
		if ($data['withendform']) {$ret .= self::endform();}
		return $ret;
	}
	
	static function endform()
	{
		return '</div></form>';
	}
	
	static function hidden($name, $value, $id = null)
	{
		if ($id === null)
		{
			$id = $name;
		}
		
		$ret = '<input type=hidden id="' . $id . '" name="' . $name . '" value = "' . $value . '" >';
		return $ret;
	}

	static function label($text,$id,$class='')
	{
		$class .= ' label';
		if ($class != '') {$class = ' class="' . $class . '" ';}
		return '<label for="' . $id . '" ' . $class . '>' . $text . '</label>';
	}
	
	static function input_text($params)
	{

		$params	 = self::get_params('input', $params);
		$options = self::write_params($params);

		$ret = '<input ' . $options . '>';
		return self::write($ret,$params);
	}
	
	static function item_multiradio($tab, $params = array())
	{
		$params	 = self::get_params('multiradio', $params);
		$options = self::write_params($params);

		$ret = '';
		$i=0;
		foreach($tab as $k => $v)
		{
			if(!is_array($v))
			{
				$v = ['label' => $v];
			}

			$ret .= form::item_radio(array('label'=>$v['label'],
											'id'=>$params['name'] . $k,
											'name'=>$params['name'],
											'value'=>$k,
											'classlabel' => 'radio_label ' . $params['classlabel'],
											'options' => $v['options']??'',
											'cotelabel' => 'r',
											'noDivRow' => true,
											'before'=>'',
											'checked' => ($params['value'] == $k?'checked':''),
											'after'=>''));
		}

		$params['classDivItem'] = 'flexitem'.min($params['gotoline'],count($tab));

		return self::write($ret,$params);
	}
	
	static function item_multicheckbox($tab, $params = array())
	{
		$params	 = self::get_params('multicheckbox', $params);
		$options = self::write_params($params);

		$ret = '';
		$i=0;

		if (!is_array($params['value'])) {$params['value'] = array($params['value']);}
			
		foreach($tab as $k => $v)
		{
			$ret .= form::item_checkbox(array('label'=>$v,
													'classlabel' => 'radio_label ' . $params['classlabel'],
													'id'=>$params['name'] . $k,
													'name'=>$params['name'] . '[]',
													'value'=>$k,
													'before'=>'',
													'cotelabel' => 'r',
													'noDivRow' => true,
													'checked' => (in_array($k,$params['value'])?'checked':''),
													'valueoff' => null,
													'after'=>''));

		}
		$params['classDivItem'] = 'flexitem'.min($params['gotoline'],count($tab));

		return self::write($ret,$params);
	}
	
	static function item_select($tab, $params = array())
	{
		$params	 = self::get_params('select', $params);
		$options = self::write_params($params);

		if ($params['optgroup'] === false) {$tab = array($tab);}
		
		$ret = '<select ' . $options . '>';

		if (($params['firstline'] !== null) && (count($tab) >= 1))
		{
			if (is_array($params['firstline']))
			{
				$ret .= "\t" . '<option value="' . $params['firstline'][0] . '" selected>' . $params['firstline'][1] . '</option>';
			}
			else
			{
				$ret .= "\t" . '<option value="" selected>' . $params['firstline'] . '</option>';
			}
		}
		
		foreach ($tab as $cat => $tab2)
		{
			if ($params['optgroup'] === true) {$ret .= "\t" . '<optgroup label="' . $cat . '">';}

			foreach ($tab2 as $key => $val)
			{

				if (!is_array($val))
				{
					$val = array('val'=>$val);
				}
				
				$ret .= "\t" . '<option ';

				if ($params['withkey'])
				{
					$ret .= 'value="' . ($val['key']??$key) . '" ';
				}

				if ((($val['key']??$key) == $params['value']))
				{
					$ret .= " selected ";
				}

				if (isset($val['class']))
				{
					$ret .= ' class="' . $val['class'] . '" ';
				}

				if (isset($val['options']))
				{
					$ret .= ' ' . $val['options'] . ' ';
				}

				if (isset($val['data']))
				{
					foreach($val['data'] as $dataKey => $data)
					{
						$ret .= ' data-' . $dataKey . '="' . $data . '" ';
					}
				}
				
				$ret .= '>' . $val['val'] . '</option>' . "\r\n";
			}
			
			if ($params['optgroup'] === true) {$ret .= "\t" . '</optgroup>';}
			
		}

		if ($params['autre'] === true)
			$ret .= '<option value="autre">Autre...</option>';

		$ret .= '</select>';

		return self::write($ret,$params);
	}

	static function item_textarea($params,$ckeditor=false)
	{
		$params	 = self::get_params('textarea', $params);
		$options = self::write_params($params,'textarea');
		
		$ret = '<textarea ' . $options . '>' . $params['value'] . '</textarea>';
		
		if ($ckeditor == true)
		{
			\Config::get('HTMLTemplate')->addScript('/ckeditor/ckeditor.js');
			\Config::get('HTMLTemplate')->addScript('/ckeditor/samples/js/sample.js');
			\Config::get('HTMLTemplate')->addCss('/ckeditor/samples/css/samples.css');
			$ret .= '<script type="text/javascript">
				$(document).ready(function()
				{
				CKEDITOR.replace( "' . $params['id'] . '",
					{
						enterMode : CKEDITOR.ENTER_BR,
						sharedSpaces :	{top : "topSpace"},
						toolbar :
						[
							["Source","Undo","Redo"],
							["Find","Replace","-","SelectAll","RemoveFormat"],
							["Link", "Unlink"],
							["TextColor","FontSize", "Bold", "Italic","Underline"],
							["NumberedList","BulletedList","-","Blockquote"],

							[ "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock", "Image"]
						]
						
					});
				});
				</script>' . RN;
		}
		
		return self::write($ret,$params);
	}

	static function item_checkbox($params)
	{
		$params	 = self::get_params('checkbox', $params);
		$options = self::write_params($params,'checkbox');
		
		$ret = '';
		
		if ($params['valueoff'] !== null)
		{
			$ret .= self::hidden($params['name'],$params['valueoff'],$params['id'].'val0');
		}
		
		$ret .= '<input type="checkbox" name="' . $params['name'] . '" id="' . $params['id'] . '" class="'.$params['class'].'" value="' . $params['value'] . '"  ' . $params['options'];

		if(isset($params['checked']) && $params['checked'] == true)
		{
			$ret .= ' checked=checked ';
		}

		foreach($params['attr'] as $k => $v)
		{
			$ret .= ' ' . $k . ' ="' . $v . '" ';
		}
		
		$ret .= ' ' . $params['checked'] . '>';
		
		$params['divclass'] .= ' form-multielement ';
		return self::write($ret,$params);
	}

	static function item_radio($params)
	{
		$params	 = self::get_params('radio', $params);
		$options = self::write_params($params,'radio');
		
		$ret = '<input type="radio" name="' . $params['name'] . '" id="' . $params['id'] . '" value="' . $params['value'] . '"  ' . $params['options'] . ' ' . $params['checked'] . '>';
		
		$params['divclass'] .= ' form-multielement ';
		return self::write($ret,$params);
	}
	
	static function item_file($params)
	{
		$params	 = self::get_params('file', $params);
		$options = self::write_params($params);
		
		$ret = '<input type=file ' . $options . '>' . RN;
		return self::write($ret,$params);
	}

	
	static function item_date($params)
	{
		$params	 = self::get_params('input', $params);
		$params	 = self::get_params('date', $params);
		$options = self::write_params($params);

		$ret = '<input ' . $options . '>' . RN;
		$ret .= ' <script>
					$(function() 
					{
						$( "#' . $params['id'] . '" ).datepicker( {dateFormat: "' . $params['format'] . '" '.$params['date_options'].'});
					});			
					</script>';
		

		return self::write($ret,$params);
	}
	
	static function get_params($type, $params=array())
	{
		$params = array_merge(self::get_default_values($type), $params);




		//-- si lid n'est pas saisi on en met un unique
		if ($params['id'] == null)
		{
			if ($params['name'] == null)
			{
				$params['id'] =  $type . '_' . \func\uniqid();
			}
			else
			{
				if (substr($params['name'],-2) == '[]')
				{
					$params['id'] = substr($params['name'],0,-2) . '_' . \uniqid();
				}
				else
				{
					$params['id'] = $params['name'];
				}
			}
		}

		return $params;
	}

	static function write_params($params,$type='')
	{
		$ret = '';

		if ((isset($params['type'])) && ($params['type'] != ''))
			$ret .= ' type="' . $params['type'] . '" ';

		if ((isset($params['name'])) && ($params['name'] != ''))
			$ret .= ' name="' . $params['name'] . '" ';

		if ((isset($params['id'])) && ($params['id'] != ''))
			$ret .= ' id="' . $params['id'] . '" ';

		if ((isset($params['class'])) && ($params['class'] != ''))
			$ret .= ' class="' . $params['class'] . '" ';

		if ((isset($params['value'])) && (!is_array($params['value'])) && ($params['value'] != '') && ($type != 'textarea'))
			$ret .= ' value="' . $params['value'] . '" ';

		if ((isset($params['rows'])) && ($params['rows'] != ''))
			$ret .= ' rows="' . $params['rows'] . '" ';

		if ((isset($params['cols'])) && ($params['cols'] != ''))
			$ret .= ' cols="' . $params['cols'] . '" ';

		//if ((isset($params['options'])) && ($params['options'] != ''))
		//	$ret .= $params['options'];
		
		if ((isset($params['style'])) && ($params['style'] != ''))
			$ret .= ' style="' . $params['style'] . '" ';
		
		if ((isset($params['size'])) && ($params['size'] != ''))
			$ret .= ' SIZE="' . $params['size'] . '" ';
		
		if ((isset($params['maxlength'])) && ($params['maxlength'] != ''))
			$ret .= ' MAXLENGTH="' . $params['maxlength'] . '" ';
		
		if ((isset($params['options'])) && ($params['options'] != ''))
			$ret .= ' ' . $params['options'] . ' ';

		return $ret;
	}

	static function get_default_values($type)
	{
		$params						 	= array();
		$params['name']				 	= null;
		$params['id']				 	= null;
		$params['class']		 		= '';
		$params['text']					= '';
		$params['separator']			= ' : ';
		$params['before']				= '';
		$params['afterfield']			= '';
		$params['beforefield']			= '';
		$params['noDivElement']			= false;
		$params['noDivRow'] 		 	= false;
		$params['noDivItem']	 		= false;
		$params['divclass'] 			= 'grid1';
		$params['classDivItem']			= '';
		$params['after']				= '';
		$params['div']					= false;
		$params['options']				= null;
		$params['value']		 		= null;
		$params['label']				= null;
		$params['cotelabel']			= 'l';
		$params['classlabel']	 		= '';
		$params['attr']	 		 		= array();
		$params['type']		 			= $type;

		switch ($type)
		{
			case 'input' :
				$params['type']		 = 'text';
				$params['size']		 = self::$defaultsize;
				$params['maxlength'] = self::$defaultlength;
				$params['mask']		 = null;
				$params['class']	 = 'input';
				

				break;
			case 'select' :
				$params['optgroup']	 = false;
				$params['withkey']	 = true;
				$params['firstline'] = null;
				$params['autre']	 = null;
				break;
			case 'textarea' :
				$params['rows']		 = 4;
				$params['cols']		 = 50;
				break;
			case 'radio' : 
				$params['checked']  = '';
				$params['cotelabel']	 = 'r';
				break;
			case 'checkbox' : 
				$params['checked']  = '';
				$params['cotelabel']	 = 'r';
				$params['valueoff'] = 0;
				
				$params['value']  = 1;
				break;
			case 'date' : 
				$params['format'] = "dd/mm/yy";
				$params['date_options'] = "";
				break;
			case 'multiradio' : 
			case 'multicheckbox' : 	
				$params['gotoline']  = 10;
				break;
			default:
				break;
		}
		return array_merge($params,self::$default);
	}

	/**
	 * 
	 */
	static function get_mode($mode)
	{
		switch($mode)
		{
			case 'tableinrow' :
				return array('separator' => '</td><td>','before'=>'<tr><td>','after'=>'</td></tr>','classlabel'=>'small');
				break;
			default :
				return array();
				break;
		}
	}
	/**
	 * Fonction permettant l'enregistrement des données du formulaire précédement posté afin de retourner sur celui ci en cas d'erreur
	 * @param type $data les données du formulaire
	 */
	static function set_old_values($data)
	{
		$_SESSION['___dataform']['page'] = substr($_SERVER["HTTP_REFERER"],strpos($_SERVER["HTTP_REFERER"],$_SERVER["HTTP_HOST"])+strlen($_SERVER["HTTP_HOST"]));
		$_SESSION['___dataform']['data'] = $data;	
		
		
	}
	
	/**
	 * Fonction permettant la recupération des valeurs précéement enregiistrées
	 * @param type $var la variable contenant les valeurs actuelle du formulaire (via create_empty ou get)
	 */
	static function get_old_values(&$var)
	{
		if(isset($_SESSION['__dataform']) && ($_SESSION['__dataform']['page'] == $_SERVER["PATH_INFO"]))
		{
			$var = array_merge($var,$_SESSION['__dataform']['data']);
		}
	}
}
?>
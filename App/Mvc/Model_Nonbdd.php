<?php
/**
 * Created by PhpStorm.
 * User: eltharin
 * Date: 19/11/2017
 * Time: 15:15
 */

namespace Core\App\Mvc;


class Model_Nonbdd
{
	//-- tableau contenant les champs de la base
	protected $fields = array();

	//-- tableau contenant les valeurs
	public $values = array();
	public $vars = array();

	//-- tableau contenant les regles de validation
	protected $validate = array();


	/**
	 * constructeur de la classe model
	 * @param type $controller
	 */
	final function __construct($uid=null)
	{
		//- on appelle le constructeur des enfants
		$this->_init($uid);
	}

	function add_field($field,$params)
	{
		$defaut = array('type'=>'varchar',
			'libelle'=>'',
			'size'=>0,
			'null'=>false,
			'default'=>'',
			'key'=>''
		);
		$params = array_merge($defaut,$params);
		$this->fields[$field] = $params;

		if ($this->fields[$field]['size'] > 0)
		{
			//$this->add_rule($field,regex::size($this->fields[$field]['size']),$field . ' too long, max : ' . $this->fields[$field]['size']);
			//echo regex::size($this->fields[$field]['size']);

			$this->add_rule($field,'length',($this->fields[$field]['libelle']!=''?$this->fields[$field]['libelle']:$field) . ' trop long, max : ' . $this->fields[$field]['size'],array($this->fields[$field]['size'],'ine'));
		}
	}


	function add_rule($field,$rule,$mess=null,$param=null)
	{
		if ($mess === null)
		{
			$mess = $field . ' invalide';
		}

		$this->validate[$field][] = array('rule'=>$rule,'message'=>$mess,'param'=>$param);
	}
	/**
	 * Fonction de constructeur pour les enfants
	 */

	function init() {}

	function _init()
	{
		$this->init();
	}

	function set_vars($arg)
	{
		$this->vars = $arg;
	}

	function set_values($arg)
	{
		$this->values = array();

		foreach ($arg as $field => $value)
		{
			if (in_array($field, array_keys($this->fields)))
			{
				if($this->fields[$field]['type'] == 'date' )
				{
					$value = $this->change_date($value);
				}
				elseif($this->fields[$field]['type'] == 'datetime')
				{
					$value = $this->change_datetime($value);
				}
				elseif($this->fields[$field]['type'] == 'json')
				{
					$value = json_encode($value,JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
				}
				elseif($this->fields[$field]['type'] == 'int')
				{
					$value = preg_replace('#[^0-9\-]*#','',$value);
					
					if($this->fields[$field]['null'] === true && $value === '')
					{
						$value = null;
					}
				}
				elseif($this->fields[$field]['type'] == 'float')
				{
					$value = preg_replace('#[^0-9\.\,\-]*#','',$value);
					
					if($this->fields[$field]['null'] === true && $value === '')
					{
						$value = null;
					}
				}
				$this->values[$field] = $value;
			}
			else
			{
				$this->vars[$field] = $value;
			}
		}
	}

	//on force le format d'une date
	function change_date($date)
	{
		if((preg_match("#^(0?\d|[12]\d|3[01])([/\-])(0?\d|1[012])([/\-])(19\d{2}|20\d{2})$#", $date))) //format français
		{
			return strtotime(preg_replace("#^(0?\d|[12]\d|3[01])([/\-])(0?\d|1[012])([/\-])(19\d{2}|20\d{2})$#","$1-$3-$5" , $date));
		}
		else if((preg_match("#^(19\d{2}|20\d{2})([/\-])(0?\d|1[012])([/\-])(0?\d|[12]\d|3[01])$#", $date))) //format américain
		{
			return strtotime(preg_replace("#^(19\d{2}|20\d{2})([/\-])(0?\d|1[012])([/\-])(0?\d|[12]\d|3[01])$#","$5-$3-$1" , $date));
		}
		else
		{
			return $date;
		}
	}

	//Format dateTime
	function change_datetime($date)
	{
		if((preg_match("#^(0?\d|[12]\d|3[01])([/\-])(0?\d|1[012])([/\-])(\d{4})$#", $date))) //format français
		{
			$date = new \DateTime(str_replace("/", "-", $date));

			return date_format($date, 'Y-m-d H:i:s');
		}
		else if((preg_match("#^(19\d{2}|20\d{2})([/\-])(0?\d|1[012])([/\-])(0?\d|[12]\d|3[01])$#", $date))) //format américain
		{
			$date = new \DateTime($date);

			return date_format($date, 'Y-m-d H:i:s');
		}
		else
		{
			return '';
		}
	}

	/**
	 * Fonction de validation des données
	 * @param boolean $addval traiter les variables non définie comme null
	 * @return boolean
	 */
	function validates($addval=true)
	{

		$errors = array();
		/**
		 * Boucle sur chaques champ à valider
		 */
		foreach ($this->validate as $field => $rules)
		{
			/**
			 * Si on veut tester la variable meme si elle n'existe pas
			 */
			if ($addval == true)
			{
				if (!isset($this->values[$field]))
				{
					$this->values[$field] = null;
				}
			}

			/**
			 * pour chaque regle de ce champ
			 */
			foreach ($rules as $rule)
			{
				if (key_exists($field,$this->values))
				{
					if ($this->values[$field] == null)
					{

						if ($rule['rule'] == 'notEmpty')
						{
							$errors[] = array('champ' => $field,
								'message' => $rule['message']);
						}
					}
					else
					{
						if ($rule['rule'] == 'notEmpty')
						{
						}
						elseif (method_exists(\Core\Classes\Check::class,$rule['rule']))
						{
							if(!is_array($rule['param']))
							{
								$rule['param'] = array($rule['param']);
							}

							if (!call_user_func_array(array(\Core\Classes\Check::class,$rule['rule']), array_merge(array($this->values[$field]),$rule['param'])))
							{
								$errors[] = array('champ' => $field,
									'message' => $rule['message']);
							}
						}
						elseif ((!preg_match('/^' . $rule['rule'] . '$/s', $this->values[$field])) && ($this->values[$field] != ''))
						{
							$errors[] = array(	'champ' => $field,
								'message' => $rule['message']);
						}
					}
				}
			}
		}

		/**
		 * Gestion des erreurs
		 */
		if (empty($errors))
		{
			$this->errors = null;
			return true;
		}
		$this->errors = $errors;
		return false;
	}


	public function create_empty($val = array())
	{
		$values = array();
		foreach ($this->fields as $field => $data)
		{
			$values[$field] = $data['default'];
		}

		foreach($val as $k => $v)
		{
			$values[$k] = $v;
		}

		return $values;
	}


	public function create_from_post() {return $this->create_from('post');}
	public function create_from_get() {return $this->create_from('get');}

	public function create_from($type)
	{
		$values = array();
		foreach ($request->$type as $field => $data)
		{
			$values[$field] = $data;
		}

		return $values;
	}

	function LoadModel($name)
	{
		if($model = \Core\App\Loader::Load('Models',$name))
		{
			$model = $model['name'];
			return new $model();
		}
		else
		{
			\HTTP::errorPage('500','Le model ' . $name . ' n\'existe pas.');
		}
	}

	function get_html_errors()
	{
		$errors = '';
		foreach($this->errors as $err)
		{
			$errors .= $err['message'] . BR;
		}
		return $errors;
	}
}
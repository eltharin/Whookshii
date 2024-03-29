<?php
namespace Core\App\Mvc;

use Core\App\Mvc\EntityField;

class Entity implements \JsonSerializable
{

	protected $modified = false;

	protected $properties = [];
	protected $oldProperties = [];

	protected $remplacementValue = '';

	protected $errors = [];

	public function __construct($data = null)
	{
		$this->init();

		if($data !== null)
		{
			foreach($data as $key => $val)
			{
				$this->properties[$key] = $val;
			}

			$this->clearModif();
		}
	}

	public function __debugInfo()
	{
		return $this->properties;
	}

	public function __toString()
	{
		return $this->getRemplacementValue();
	}

	public function getRemplacementValue()
	{
		return $this->remplacementValue;
	}

	public function setRemplacementValue($val)
	{
		$this->remplacementValue = $val;
	}

	public function jsonSerialize(): mixed
	{
		return $this->properties;
	}

	public function getAllProperties()
	{
		return $this->properties;
	}

	public function init() : void {}


	public function __get($key)
	{
		if(array_key_exists($key, $this->properties))
		{
			return $this->properties[$key];
		}
		elseif(method_exists($this, 'get' . ucfirst($key)))
		{
			return call_user_func([$this, 'get' . ucfirst($key)]);
		}
		$d = debug_backtrace();
		trigger_error('Tentative d\'acces a une propriété innexistante : ' . $key . ' dans le fichier ' . $d[0]['file'] . ' à la ligne ' . $d[0]['line'] . RN  ,E_USER_NOTICE);
		return $this->properties[$key] ?? null;
	}

	public function __set($key, $val)
	{
		//--TODO:ajout fonction set
		if(!array_key_exists($key, $this->properties) || $this->properties[$key] !== $val)
		{
			if(!isset($this->oldProperties[$key]))
			{
				$this->oldProperties[$key] = $this->properties[$key] ?? null;
			}
			$this->properties[$key] = $val;
			$this->modified = true;
		}
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->properties);
	}

	public function __unset($key)
	{
		unset($this->properties[$key]);
	}

	public function clearModif() : void
	{
		$this->modified = false;
		$this->oldProperties = [];
	}

	public function getOldValue($key)
	{
		return isset($this->oldProperties[$key]) ? $this->oldProperties[$key] : (isset($this->properties[$key]) ? $this->properties[$key] : null);
	}

	public function isModified()
	{
		return $this->modified;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function addError($errorType, $error)
	{
		$this->errors[$errorType][] = $error;
	}

	public function affect(array $data)
	{
		foreach($data as $k => $v)
		{
			$this->__set($k,$v);
		}
	}

	public function filter(array $filter)
	{
		$clone = clone($this);

		foreach($clone->getAllProperties() as $k => $v)
		{
			if(!in_array($k, $filter))
			{
				unset($clone->$k);
			}
		}
		return $clone;
	}

	/**
	 * @return null or array null if not specified, array for Form::select specifications
	 */
	public function getDataSelect()
	{
		return null;
	}
	/*protected function _setValue($key, $val, $options = [])
	{
		if(isset($this->fields[$key]))
		{
			$this->fields[$key]->setValue($val,$options);
		}
		else
		{
			$this->properties[$key] = $val;
		}
	}

	protected function _getValue($key, ...$args)
	{
		if(isset($this->fields[$key]))
		{
			return $this->fields[$key]->getValue();
		}
		elseif(isset($this->properties[$key]))
		{
			return $this->properties[$key];
		}

		trigger_error($key . ' not found in Object');
		return null;
	}

	protected function _oldValue($key, ...$args)
	{
		if(isset($this->fields[$key]))
		{
			return $this->fields[$key]->oldValue();
		}

		trigger_error($key . ' not found in Object');
		return null;
	}

	public function clearModif() : void
	{
		foreach($this->fields as $field)
		{
			$field->clearModif();
		}
	}

	public function isModified() : bool
	{
		foreach($this->fields as $field)
		{
			if($field->isModified())
			{
				return true;
			}
		}
		return false;
	}

	protected function addField($fieldName, $fieldInfo = []) : void
	{
		$this->fields[$fieldName] = new EntityField($fieldName, $fieldInfo);
	}


*/









	/*


	public function __set($key, $val)
	{
		if(array_key_exists($key, $this->fields))
		{
			$this->fields[$key]->setValue($val);
		}
		if(array_key_exists($key, $this->fieldsNames))
		{
			$this->fields[$this->fieldsNames[$key]]->setValue($val);
		}
		else
		{
			$this->values[$key] = $val;
		}
	}

	public function __get($key)
	{
		if(array_key_exists($key, $this->fields))
		{
			return $this->fields[$key]->getValue();
		}

		return $this->values[$key] ?? null;
	}

	public function getDefaultValues(bool $withNullable = false)
	{
		foreach($this->fields as $fieldName => $field)
		{
			$field->getDefaultValue();
		}
	}

	public function validate() : bool
	{
		$ret  = true;

		foreach($this->fields as $fieldName => $field)
		{
			if(($validate = $field->validate()) === false)
			{
				$this->errors[$fieldName] = $field->getErrors();
				$ret = false;
			}
		}
		return $ret;
	}

	public function getErrors() : ?array
	{

	}*/
}
<?php
namespace Core\App\Mvc;

use Core\App\Mvc\EntityField;

class Entity
{

	protected $modified = false;

	protected $properties = [];
	protected $oldProperties = [];

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

	public function __debugInfo() {
		return $this->properties;
	}

	public function init() : void {}


	public function __get($key)
	{
		return $this->properties[$key];
	}

	public function __set($key, $val)
	{
		$this->properties[$key] = $val;
	}

	public function clearModif() : void
	{
		$this->modified = false;
		$this->oldProperties = $this->properties;
	}

	public function getOldValue($key)
	{
		return $this->oldProperties[$key];
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
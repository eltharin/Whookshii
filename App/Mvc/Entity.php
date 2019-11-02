<?php
namespace Core\App\Mvc;

use Core\App\Mvc\EntityField;

class Entity
{
	protected $fields = [];
	protected $fieldsNames = [];

	protected $values = [];

	public function __construct()
	{
		$this->init();
	}

	public function init() : void {}

	protected function addField($fieldName, $fieldInfo = [])
	{
		$this->fields[$fieldName] = new EntityField($fieldName, $fieldInfo);
		$this->fieldsNames[$this->fields[$fieldName]->getField()] = $fieldName;
	}

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

	}
}
<?php
namespace Core\App\Mvc;

class EntityField
{
	protected $name = null;
	protected $type = null;
	protected $defaultValue = null;
	protected $value = null;

	public function __construct(string $name, array $options)
	{
		$this->name = $name;
		$this->defaultValue = $options['default'] ?? null;
		$this->type = $options['type'] ?? null;
	}

	public function setValue($value)
	{
		$this->value = $value;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function validate() : bool
	{
		return true;
	}

	public function getErrors() : ?array
	{
		return null;
	}

	public function getDefaultValue()
	{
		$this->value = $this->defaultValue;
	}
}
<?php
namespace Core\App\Mvc;

class EntityField
{
	protected $name = null;
	protected $type = null;
	protected $defaultValue = null;
	protected $value = null;
	protected $field = null;

	public function __construct(string $name, array $options)
	{
		$this->name = $name;
		$this->defaultValue = $options['default'] ?? null;
		$this->field = $options['field'] ?? null;
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

	/**
	 * @return mixed|null
	 */
	public function getField(): ?string
	{
		return $this->field ?: $this->name;
	}

	/**
	 * @param mixed|null $field
	 */
	public function setField(string $field): void
	{
		$this->field = $field;
	}
}
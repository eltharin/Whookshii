<?php


namespace Core\App\Mvc;


class Reponse
{
	protected $errors = [];
	protected $data = [];

	public function __get($key)
	{
		return $this->data[$key];
	}

	public function __set($key,$val)
	{
		$this->data[$key] = $val;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getData()
	{
		return $this->data;
	}

	public function hasError()
	{
		return !empty($this->errors);
	}

	public function setError($err)
	{
		$this->errors = $err;
	}

	public function getError()
	{
		return $this->errors;
	}
}
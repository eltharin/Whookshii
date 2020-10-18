<?php


namespace Core\App\Mvc\ServicesQR;


class Reponse
{
	protected $errors = [];
	protected $data = [];

	public function __construct($data = [])
	{
		$this->data = $data;
	}

	public function __get($key)
	{
		return $this->data[$key];
	}

	public function __set($key,$val)
	{
		$this->data[$key] = $val;
	}

	public function __call($method, $arguments)
	{
		if(substr($method,0,3) == 'set')
		{
			$key = lcfirst(substr($method,3));
			$this->data[$key] = $arguments[0];
			return $this;
		}
		elseif(substr($method,0,3) == 'get')
		{
			$key = lcfirst(substr($method,3));
			return $this->data[$key];
		}
		throw new \Exception('La fonction ' . $method . ' n\existe pas.');
	}

	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function hasError()
	{
		return !empty($this->errors);
	}

	public function setError($err, $code = '', $adds = [])
	{
		$this->errors = new QRError($err, $code, $adds);
		return $this;
	}

	public function getError()
	{
		return $this->errors;
	}
}
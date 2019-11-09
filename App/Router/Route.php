<?php
namespace Core\App\Router;

use Core\App\Exception\HttpException;

class Route
{
	private $path;
	private $method;
	private $controller;
	private $action;
	private $name;
	private $callback;
	private $params;


	public function __construct(array $data = [])
	{
		$this->path = $data['path']??'';
		$this->method = $data['method']??'';
		$this->callback = new Callback($data['callback']??'');
		$this->name = $data['name']??'';
		$this->params = $data['params']??[];
	}

	public function setCallback($callback)
	{
		$this->callback = new Callback($callback);
	}
	
	public function addParam($key, $val)
	{
		$this->params[$key] = $val;
	}

	/**
	 * @return string
	 */
	public function getPath() : string
	{
		return $this->path;
	}

	/**
	 * @return array|string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return mixed|string
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}
}
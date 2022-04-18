<?php
namespace Core\App\Router;

use Core\App\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
	private $path;
	private $method;
	private $controller;
	private $action;
	private $name;
	private $callback;
	private $withParam;
	private $params;
	private $properties;


	public function __construct(array $data = [])
	{
		$this->path = $data['path']??'';
		$this->method = $data['method']??'';
		$this->callback = new Callback($data['callback']??'');
		$this->name = $data['name']??'';
		$this->params = $data['params']??[];
		$this->properties = $data['properties']??[];
		$this->withParam = $data['withParam']??false;
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
	/**
	 * @return array
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * @return bool
	 */
	public function getWithParam(): bool
	{
		return $this->withParam;
	}

	public function execute(ServerRequestInterface $request)
	{
		if($this->callback->getCallback() !== null)
		{
			return call_user_func($this->callback->getCallback());
		}

		$controllername = $this->getCallback()->getController();
		$attributes = $this->getParams();

		if(count($attributes) <= 1)
		{
			return call_user_func_array([new $controllername($request), 'Action_' . $this->getCallback()->getAction()],array_values($attributes['_params'] ?? $attributes));
		}
		else
		{
			return call_user_func([new $controllername($request), 'Action_' . $this->getCallback()->getAction()],$attributes);
		}
	}
}
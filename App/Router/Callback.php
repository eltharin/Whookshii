<?php
namespace Core\App\Router;

use Core\App\Exception\HttpException;
use GuzzleHttp\Psr7\Response;


class Callback
{
	private $callable = null;
	private $error = null;
	private $controller = null;
	private $action = null;

	public function __construct($data = '')
	{
		if(is_array($data))
		{
			$this->controller = $data['controller']??'index';
			$this->action = $data['action']??'index';
		}
		elseif(is_callable($data))
		{
			$this->callable = $data;
			return;
		}
		else
		{
			$tab = explode('/',$data);

			if(count($tab) > 1)
			{
				$this->action = array_pop($tab);
			}
			else
			{
				$this->action = 'index';
			}

			$this->controller = implode('/',$tab) ?: 'index';
		}
	}

	public function analyse() : bool
	{
		if(!class_exists($this->controller))
		{
			$controllerclass = \Core\App\Loader::Load('Controllers',ucwords(str_replace(['/','_'],['\\','\\'],$this->controller),'\\'));

			if($controllerclass === null)
			{
				$this->error = new Response(404,[],'Controller ' . $this->controller . ' Not Found');
				return false;
			}
			$this->controller = $controllerclass['name'];
		}

		if(!method_exists($this->controller, 'Action_' . $this->action))
		{
			$this->error = new Response(404,[],'Method ' .$this->action. ' Not Found in ' . $this->controller);
			return false;
		}

		if(!is_a($this->controller ,\Core\App\Mvc\Controller::class, true))
		{
			$this->error = new Response(404,[],'Controller invalide');
			return false;
		}

		return true;
	}

	public function getCallback()
	{
		return $this->callable;
	}
	/**
	 * @return mixed
	 */
	public function getController() : string
	{
		return $this->controller;
	}

	/**
	 * @param mixed $controller
	 */
	public function setController($controller): void
	{
		$this->controller = $controller;
	}

	/**
	 * @return mixed
	 */
	public function getAction() : string
	{
		return $this->action;
	}

	/**
	 * @param mixed $action
	 */
	public function setAction($action): void
	{
		$this->action = $action;
	}

	public function getError()
	{
		return $this->error;
	}

	public function execute()
	{
		if($this->callable !== null)
		{
			return call_user_func_array($this->callable,$params);
		}
		return null;
	}
}
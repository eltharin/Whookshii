<?php
namespace Core\App\Router;

use Core\App\Exception\HttpException;

class Callback
{
	private $controller;
	private $action;
	
	public function __construct($data = '')
	{
		if(is_array($data))
		{
			$this->controller = $data['controller']??'index';
			$this->action = $data['action']??'index';
		}
		else
		{
			$tab = explode('/',$data);

			$this->controller = $tab[0];
			$this->action = $tab[1]??'index';
		}
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


}
<?php

namespace Core\App\Config;

class Routes
{
	private $schema = 'controller/action';
	private $defaultRequest = 'index/index';

	//private $controller = '';
	//private $action = 'index';
	//private $params = [];

	//private $blank_controller = false;
	//private $makeurl = '';

	private $routes = [];
	private $force_route = false;


	public function getSchema()
	{
		return $this->schema;
	}

	public function set_schema($schema)
	{
		$this->schema = $schema;
	}

	public function get_defaultRequest()
	{
		return $this->defaultRequest;
	}

	public function set_defaultRequest($defaultRequest)
	{
		$this->defaultRequest = $defaultRequest;
	}

	public function get_routes()
	{
		return $this->routes;
	}

	public function add_route($route,$params)
	{
		$this->routes[$route] = $params;
	}

	public function get_forceRoute()
	{
		return $this->force_route;
	}

	public function set_forceRoute($force)
	{
		$this->force_route = $force;
	}

/*	public function get_controller()
    {
        return $this->controller;
    }
	
    public function get_action()
    {
        return $this->action;
    }

    public function get_params()
    {
        return $this->params;
    }*/
	
}
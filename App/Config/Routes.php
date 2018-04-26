<?php

namespace Core\App\Config;

class Routes
{
	private $schema = 'controller/action';
	private $defaultRequest = 'index/index';

	private $controller = '';
	private $action = 'index';
	private $params = [];

	private $blank_controller = false;
	private $makeurl = '';

	private $routes = [];
	private $force_route = false;


	public function get_schema()
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

	public function get_controller()
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
    }
	
	public function analyse_routes() : bool
	{
		foreach($this->get_routes() as $route => $params)
		{
			if(preg_match($route,\Core::$request.'/',$matches) === 1)
			{
				foreach ($params as $k => $v)
				{
					$this->$k = $v;
				}
				//@TODO Gestion des matches

				$this->get_infos_from_schema(\Core::$request);
				return true;
			}
		}

		if(!$this->get_forceRoute())
		{
			$this->get_infos_from_schema(\Core::$request);
			return true;
		}

		return false;
	}

	private function get_infos_from_schema($request)
	{
		
		$vars = explode('/', $request);

		if ($this->schema != '')
		{
			$elems = explode('/', trim($this->schema, '/'));
			//-- pour chaque element du schema
			foreach ($elems as $elem)
			{
				//-- si on a assez d'element de query
				if (count($vars) >= 1)
				{
					if($elem != '.')
					{
						//-- on ajoute l'element
						$this->$elem = $vars[0];
					}
					//-- on supprime le premier element de query string
					$vars = array_slice($vars, 1);
				}
			}
		}

		//-- les parametres sont ce qu'il reste de la query string
		$this->params = $vars;
		//-- on supprime les variables
		unset($vars);
		unset($elems);

		return true;
	}
}
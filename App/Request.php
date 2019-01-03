<?php

namespace Core\App;

class Request 
{
	public $schema = null;
	public $headers = [];
	private $subfolder = '';
	private $modeapi = false;
	private $noTemplate = false;

	public function __construct()
	{
		$this->schema = new \stdClass();
		
		if(PHP_SAPI == 'cli')
		{
			$this->sapi = 'CLI';
			$argv = $_SERVER['argv'];
			unset($argv[0]);
			$this->schema->string = implode('/',$argv);
		}
		else
		{
		    $this->headers = $this->getallheaders();

			$this->sapi = 'PHP';

			$this->modeapi = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

			if(!isset($_SERVER['PATH_INFO']))
			{
				$script_name = str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
				if(strtolower($script_name) == '/core/app.php')
				{
					$_SERVER['PATH_INFO'] = $_SERVER['PHP_SELF'];
				}
				elseif(strtolower(substr($script_name,-13)) == '/core/app.php')
				{
					$_SERVER['PATH_INFO'] = substr($_SERVER['PHP_SELF'],strlen($script_name)-13);
				}
			}

			if(strtolower($_SERVER['SCRIPT_NAME']) === '/core/app.php')
			{
				$this->subfolder = '';
			}
			elseif(strtolower(substr($_SERVER['SCRIPT_NAME'],-13)) === '/core/app.php')
			{
				$this->subfolder = substr($_SERVER['SCRIPT_NAME'],0,-13);
			}
			else
			{
				throw new \Exception('Le fichier source ' . $_SERVER['SCRIPT_NAME'] . ' n\'est pas correct.');
			}
			$this->schema->string = trim($_SERVER['PATH_INFO'],'/');
			
			
			if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' && empty($_POST))
			{
				$_POST = json_decode(file_get_contents('php://input'),1);
			}
		}

		$this->noTemplate = $this->get_sapi() || $this->modeapi;
	}

	public function get_request() : string
	{
		return $this->schema->string ?: \Config::$routes->get_defaultRequest();
	}

	public function get_sapi() : bool
	{
		return $this->sapi == 'CLI';
	}
	
	public function get_modeapi() : bool
	{
		return $this->modeapi;
	}

	public function get_subfolder() : string
	{
		return $this->subfolder;
	}

	public function get_noTemplate() : bool
	{
		return $this->noTemplate;
	}

	public function get_headers() : array
    {
        return $this->headers;
    }

    public function get_header($key) : ?string
    {
        return $this->headers[$key]??null;
    }
	
	private function getallheaders()
	{
		if(function_exists('getallheaders'))
		{
			return getallheaders();
		}
		else
		{
			$tab = array_filter($_SERVER,function($a){return substr($a,0,5)=='HTTP_';}, ARRAY_FILTER_USE_KEY);
			return array_combine(array_map(function($a){return ucwords(str_replace('_','-',strtolower(substr($a,5))),'-');},array_keys($tab)),$tab);
		}
	}
	
	//-- Routes
	public function analyseRoutes() : bool
	{
		$request = $this->schema->string ?: \Config::$routes->get_defaultRequest();
		$this->schema->schema = trim(\Config::$routes->getSchema(), '/');
		
		foreach(\Config::$routes->get_routes() as $route => $params)
		{
			if(preg_match($route,$request . '/',$matches) === 1)
			{
				$params = array_merge($matches,$params);
				foreach ($params as $k => $v)
				{
					$this->schema->$k = $v;
					echo $k . ' => ' . $v .BRN;
				}
				//@TODO Gestion des matches

				$this->getInfosFromSchema($request);
				return true;
			}
		}

		if(!\Config::$routes->get_forceRoute())
		{
			$this->getInfosFromSchema($request);
			return true;
		}

		return false;
	}
	
	private function getInfosFromSchema($request)
	{
		$vars = explode('/', $request);
		if ($this->schema->schema != '')
		{
			$elems = explode('/', $this->schema->schema);
			//-- pour chaque element du schema
			foreach ($elems as $elem)
			{
				//-- si on a assez d'element de query
				if (count($vars) >= 1)
				{
					if($elem != '.')
					{
						//-- on ajoute l'element
						$this->schema->$elem = $vars[0];
					}
					//-- on supprime le premier element de query string
					$vars = array_slice($vars, 1);
				}
			}
		}

		//-- les parametres sont ce qu'il reste de la query string
		$this->schema->params = $vars;
		//-- on supprime les variables
		unset($vars);
		unset($elems);

		return true;
	}
}

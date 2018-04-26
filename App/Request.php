<?php

namespace Core\App;

class Request 
{
	private $files = [];
	private $method = '';
	private $data = [];
	private $body = '';
	private $request = '';
	private $defaultRequest = '';
	private $headers = [];
	private $subfolder = '';
	private $modeapi = false;
	private $noTemplate = false;

	public function __toString()
	{
		return $this->get_request();
	}

	public function __construct()
	{
		if(PHP_SAPI == 'cli')
		{
			$this->sapi = 'CLI';
			$argv = $_SERVER['argv'];
			unset($argv[0]);
			$this->request = implode('/',$argv);
		}
		else
		{
		    $this->headers = getallheaders();

			$this->sapi = 'PHP';

			$this->modeapi = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

			if(!isset($_SERVER['PATH_INFO']))
			{
				$script_name = str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
				if($script_name == '/core/app.php')
				{
					$_SERVER['PATH_INFO'] = $_SERVER['PHP_SELF'];
				}
				elseif(substr($script_name,-13) == '/core/app.php')
				{
					$_SERVER['PATH_INFO'] = substr($_SERVER['PHP_SELF'],strlen($script_name)-13);
				}
			}

			if($_SERVER['SCRIPT_NAME'] === '/core/app.php')
			{
				$this->subfolder = '';
			}
			elseif(substr($_SERVER['SCRIPT_NAME'],-13) === '/core/app.php')
			{
				$this->subfolder = substr($_SERVER['SCRIPT_NAME'],0,-13);
			}
			else
			{
				throw new \Exception('Le fichier source ' . $_SERVER['SCRIPT_NAME'] . ' n\'est pas correct.');
			}
			$this->request = trim($_SERVER['PATH_INFO'],'/');
			
			
			if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' && empty($_POST))
			{
				$_POST = json_decode(file_get_contents('php://input'),1);
			}
		}

		$this->noTemplate = $this->get_sapi() || $this->modeapi;
	}

	public function get_request() : string
	{
		return $this->request ?: \Config::$routes->get_defaultRequest();
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
}

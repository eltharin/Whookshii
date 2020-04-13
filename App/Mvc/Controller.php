<?php
namespace Core\App\Mvc;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class Controller
{
	/**
	 * @var \Psr\Http\Message\RequestInterface
	 */
	protected $request;

	protected $vars = [];
	protected $defaultAction = 'index'; //TODO : set default action
	protected $classInfos;


	public function __construct(RequestInterface $request)
	{
		$this->request = $request;

		$this->classInfos = new \StdClass();
		$this->classInfos->namespace = explode('\\',get_class($this));

		if(strtolower($this->classInfos->namespace[0]) == 'plugin')
		{
			$this->classInfos->folderTypePosition = 2;
			$this->url = '/' . $this->classInfos->namespace[1] . '/' . implode('/', array_slice($this->classInfos->namespace,3));
		}
		else
		{
			$this->classInfos->folderTypePosition = 1;
			$this->url = '/' . implode('/', array_slice($this->classInfos->namespace,2));
		}

		$classArray = $this->classInfos->namespace;
		$classArray[$this->classInfos->folderTypePosition] = 'Models';

		
		/*
		 * $modelName = '\\' . implode('\\', $classArray);

		if(class_exists($modelName))
		{
			$modelVarName = lcfirst(implode('',array_map('ucfirst',array_slice($this->classInfos->namespace, -1))));
			$this->$modelVarName = new $modelName();
		}
*/
		$this->_init();
	}

	protected function getViewFolder() : string
	{
		$classArray = $this->classInfos->namespace;
		$classArray[$this->classInfos->folderTypePosition] = 'Views';
		return implode(DS, $classArray) . DS;
	}

	function getLink($link='')
	{
		return $this->url . '/' . $link;
	}
	
	protected function init()	{}

	protected function _init()
	{
		$this->init();
	}

	public function addVars($k,$v=null)
	{
		if(is_array($k))
		{
			$this->vars = array_merge($this->vars,$k);
		}
		else
		{
			$this->vars[$k] = $v;
		}
	}
	protected function render(string $vue,array $vars = [],Controller $controller = null)
	{
		if ($controller !== null)
		{
			// TODO: Revoir ca
			$ctrl = $this->LoadController($controller);
			$ctrl->add_vars($this->vars);
			$ctrl->render($vue);
		}
		else
		{
			$this->vue = $this->getViewFolder() .$vue . '.php';
			$this->vars = array_merge($this->vars, $vars);
			if(file_exists(ROOT . $this->vue))
			{
				unset($vue);
				unset($controller);
				unset($vars);
				extract($this->vars);
				require ROOT . $this->vue;
			}
			else
			{
				echo 'Cette page n\'existe pas : ' . DS . $this->vue;
				//return new Response('404',[],'Cette page n\'existe pas : ' . DS . $this->viewfolder .$vue . '.php');
			}
		}
	}
}
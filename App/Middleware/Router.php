<?php
namespace Core\App\Middleware;

class Router extends MiddlewareAbstract
{
	public function beforeProcess()
	{
		if(!\Core::$request->analyseRoutes())
		{
			\HTTP::error_page('500','Pas de route trouvée');
		}
		
		$controller = \Core::$request->schema->controller;
		//$controller = ucwords(\Config::$routes->get_controller(),'\\');
		
		if($controller == '')
		{
			\HTTP::error_page('500','Pas de controller selectionné');
		}

		if($ctrl = \Core\App\Loader::Load('Controllers',ucwords(str_replace(['/','_'],['\\','\\'],$controller),'\\')))
		{
			$ctrl = $ctrl['name'];
			$ctrl = new $ctrl();
		
			if ($ctrl instanceof \Core\App\Mvc\Controller)
			{
				\Core::$request->schema->controller = $ctrl;
			}
			else
			{
				\HTTP::error_page('500','Le fichier est incorrect');
			}
		}
		else
		{
			\HTTP::error_page('500','Le controller ' . $controller . ' est introuvable');
		}

		return true;
	}


}

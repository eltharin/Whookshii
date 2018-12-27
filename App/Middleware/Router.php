<?php


namespace Core\App\Middleware;


class Router extends MiddlewareInterface
{
	private $schema = 'controller/model';
	private $namespace = '';
	private $controller = '';
	private $action = '';
	private $params = [];

	public function BeforeProcess()
	{
		try
		{
			if(!\Config::$routes->analyse_routes())
			{
				\HTTP::error_page('500','Pas de route trouvée');
			}

			$controller = ucwords(\Config::$routes->get_controller(),'\\');
			$action     = strtolower(\Config::$routes->get_action());
			$params     = \Config::$routes->get_params();
			
			if($controller == '')
			{
				\HTTP::error_page('500','Pas de controller selectionné');
			}

			if($action == '')
			{
				$action = 'index';
			}

			if($ctrl = \Core\App\Loader::Load('Controllers',ucwords(str_replace(['/','_'],['\\','\\'],$controller),'\\')))
			{
				$ctrl = $ctrl['name'];
				$ctrl = new $ctrl();
			
				if ($ctrl instanceof \Core\App\Mvc\Controller)
				{
					session_write_close();
					$ctrl->launch_function($action,$params);
					$s = $_SESSION;
					session_start();
					$_SESSION = $s;
					unset($s);
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
		}
		catch(\Exception $e)
		{
			\Core::$response->add_exception($e);
			$data = $e->getmessage();
			if($_SESSION['debug_mode'])
			{
				echo $e->getFile() . ' - ligne ' . $e->getLine(). BRN;
			}
			echo ($data!=''?$data:'Erreur non spécifiée');
			return null;
		}
	}


}

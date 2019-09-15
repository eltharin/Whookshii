<?php
namespace Core\App\Middleware;

use Core\App\Exception\HttpException;
use Core\App\Exception\RedirectException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;


class Launcher extends MiddlewareAbstract
{
	private $schema = 'controller/model';
	private $namespace = '';
	private $controller = '';
	private $action = '';
	private $params = [];

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{

		if(($callback = $request->getAttribute('__callback')) !== null)
		{
			if($callback === '')
			{
				throw new \Exception('No Callback');
			}

			if(is_string($callback))
			{
				$lastpos = strrpos( $callback, '/');

				$controllername = substr($callback,0,$lastpos);
				$action = substr($callback,$lastpos+1);

				$controllerclass = \Core\App\Loader::Load('Controllers',ucwords(str_replace(['/','_'],['\\','\\'],$controllername),'\\'));

				if($controllerclass === null)
				{
					return new Response(404,[],'Controller ' . $controllername . ' Not Found');
				}

				try
				{
					$controller = new $controllerclass['name']($request);

					if(!method_exists($controller, 'Action_' . $action))
					{
						return new Response(404,[],'Method ' .$action. ' Not Found in ' . $controllername);
					}

					$attributes = $request->getAttribute('__actionParams');

					/*if($controller instanceof \Core\App\Mvc\Controller)
					{
						echo 'new';
						return new Response(200,[],'new');
					}
					elseif($controller instanceof \Core\App\Mvc\Oldcontroller)
					{*/

					ob_start();
					if($controller instanceof \Core\App\Mvc\Controller)
					{
						$actionReturn = call_user_func_array([$controller,'Action_' . $action],$attributes);
						//TODO : Laisser comme ca ou pas
					}
					elseif($controller instanceof \Core\App\Mvc\Oldcontroller)
					{
						$actionReturn = call_user_func_array([$controller,'Action_' . $action],$attributes);
					}
					$content = ob_get_clean();

					if($actionReturn !== null && $actionReturn instanceof Response)
					{
						return $actionReturn;
					}
					return new Response(200,[],$content);
				}
				catch(HTTPException $e)
				{
					return new Response($e->getCode(),[],$e->getMessage() . BRN . print_r(json_decode($e->getPageContent()),true));
				}
				catch(RedirectException $e)
				{
					return new Response(301,['Location'=>$e->getMessage()]);
				}
				/*}
				else
				{
					throw new \Exception('Bad Type Controller received : ' . get_class($controller),500);
					//return new Response('500',[],'Bad Type Controller received');
				}*/
			}

		}

		return new Response(500,[],'Erreur serveur');

	}
	/*public function beforeProcess()
	{
		$controller = \Core::$request->schema->controller;
		$action     = strtolower(\Core::$request->schema->action??'index');
		$params     = \Core::$request->schema->params??[];
		
		if($action == '')
		{
			$action = 'index';
		}		
		
		session_write_close();
		$controller->launch_function($action,$params);
		$s = $_SESSION;
		session_start();
		$_SESSION = $s;
		unset($s);
	}*/
}

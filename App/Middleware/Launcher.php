<?php
namespace Core\App\Middleware;

use Core\App\Exception\HttpException;
use Core\App\Exception\RedirectException;
use Core\App\Exception\RenderResponseException;
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

			$controllername = $callback->getController();//ubstr($callback,0,$lastpos);
			$action = $callback->getAction();//substr($callback,$lastpos+1);

			$controllerclass = \Core\App\Loader::Load('Controllers',ucwords(str_replace(['/','_'],['\\','\\'],$controllername),'\\'));

			if($controllerclass === null)
			{
				return new Response(404,[],'Controller ' . $controllername . ' Not Found');
			}

			try
			{
				try
				{
					$actionReturn = null;
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
				}
				catch(RenderResponseException $e)
				{

				}

				$content = ob_get_clean();

				if($actionReturn !== null && $actionReturn instanceof Response)
				{
					return $actionReturn;
				}
				return \Config::get('Response')->getResponse()->withBody(\GuzzleHttp\Psr7\stream_for($content));
			}
			catch(HTTPException $e)
			{
				return new Response($e->getCode(),[],$e->getMessage());
			}
			catch(RedirectException $e)
			{
				//return new Response(301,['Location'=>$e->getMessage()]);
				return new Response(200,[],'Redirection');
			}
				
				/*}
				else
				{
					throw new \Exception('Bad Type Controller received : ' . get_class($controller),500);
					//return new Response('500',[],'Bad Type Controller received');
				}*/


		}

		return new Response(500,[],'Erreur serveur');

	}
}

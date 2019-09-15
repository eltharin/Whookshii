<?php
namespace Core\App\Middleware;

use Core\App\Router\Route;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router extends MiddlewareAbstract
{
	private $ctrl = null;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if($request->getUri()->getPath() === '/')
		{
			$request = $request->withUri($request->getUri()->withPath(\Config::get('Routes')->getConfig('defaultRoute')));
		}

		$route = $this->match($request);

		if($route === null)
		{
			return new Response('404',[],'Route Not found');
		}

		$request = $request
					->withAttribute('__callback',$route->getCallback())
					->withAttribute('__actionParams', $route->getParams());

		return $handler->handle($request);
	}


	public function match(ServerRequestInterface $request) : ?Route
	{
		$config = \Config::get('Routes')->getConfig();

		foreach($config['routes'] as $route)
		{
			if($this->checkMethod($route, $request) && $this->checkPath($route, $request))
			{
				return $route;
			}
		}

		//-- gestion des routes automatiques
		if($config['automaticsRoutes'])
		{
			$request = explode('/', trim($request->getUri()->getPath(),'/'));
			$controller = array_shift($request);
			$action = array_shift($request);

			return new Route([
						'name' => 'automatic',
						'callback' => $controller.'/'.($action??'index'),
						'params' => $request
						]);
		}

		return null;
	}

	private function checkMethod(Route $route,ServerRequestInterface $request) : bool
	{
		if($route->getMethod() === '*')
		{
			return true;
		}
		if($route->getMethod() == $request->getMethod())
		{
			return true;
		}
		if(is_array($route->getMethod()) && in_array($request->getMethod(), $route->getMethod()))
		{
			return true;
		}

		return false;
	}

	private function checkPath(Route $route,ServerRequestInterface $request) : bool
	{
		$path = '#^' . preg_replace_callback('/\{([a-zA-Z0-9\-\_]+)\s*:\s*([^\}]+)\}/',
							function ($matches){
								return '(?<' . $matches[1]. '>' . str_replace('.','[^/]',$matches[2]). ')';
							},
							$route->getPath()) . '$#';

		if($route->getPath() == $request->getUri()->getPath())
		{
			return true;
		}
		if(preg_match($path,$request->getUri()->getPath(),$matches))
		{
			foreach($matches as $k => $match)
			{
				if(!is_int($k))
				{
					$route->addParam($k, $match);
				}
			}
			return true;
		}
		//echo $route->getPath() . ' -> ' . $request->getUri()->getPath();
		return false;
	}

}

<?php
/**
 * Created by PhpStorm.
 * User: eltharin
 * Date: 12/11/2017
 * Time: 12:28
 */

namespace Core\App;


class Dispatcher
{
	private $middlewares = [];
	private $routeMiddleware = '';
	private $index = 0;

	public function __construct(string $routeMiddleware)
	{
		$this->routeMiddleware = $routeMiddleware;
	}
	
	public function add_middleware(string $middeleware)
	{
		$this->middlewares[] = $middeleware;
	}

	public function handle()
	{
		$middleware = $this->get_nextMiddleware();

		if(is_null($middleware))
		{
			return null;
		}

		return $this->launch_middleware(new $middleware());
	}
	
	private function launch_middleware(Middleware\MiddlewareInterface $middleware)
	{
		if($middleware->BeforeProcess() !== false)
		{
			$this->handle();
		}
		$middleware->AfterProcess();
	}

	private function get_nextMiddleware()
	{
		if(isset($this->middlewares[$this->index]))
		{
			return $this->middlewares[$this->index++];
		}
		elseif($this->index == count($this->middlewares))
		{
			$this->index++;
			return $this->routeMiddleware;
		}
		return null;
	}
}
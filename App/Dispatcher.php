<?php
namespace Core\App;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements RequestHandlerInterface
{
	/**
	 * @var array tableau des middlewares à charger
	 */
	private $middlewares = [];

	/**
	 * @var int index du prochain middleware à executer
	 */
	private $index = 0;

	/**
	 * Initialize le dispatcher, charge un fichier de config ou à défaut la config par défaut
	 * Dispatcher constructor.
	 */
	public function __construct()
	{
		//$this->middlewares = &\Config::get('Middlewares')->getConfig();
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$middleware = $this->getNextMiddleware();

		if(is_null($middleware))
		{
			return new Response();
		}
		$ret = (new $middleware())->process($request, $this);
		return $ret;
	}

	/**
	 * cherche le middleware suivant
	 * @return mixed|null
	 */
	private function getNextMiddleware()
	{
		$middlewares = \Config::get('Middlewares')->getConfig();

		if(isset($middlewares[$this->index]))
		{
			return $middlewares[$this->index++];
		}
		elseif($this->index == count($middlewares))
		{
			$this->index++;
			return \Core\App\Middleware\Launcher::class;
		}
		return null;
	}

}
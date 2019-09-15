<?php
namespace Core\App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
	/**
	 * Fonction executée avant le prochain middleware
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface|null null passe au middleware suivant, une réponse bloque le prochain
	 */
	public function beforeProcess(ServerRequestInterface $request) : ?ResponseInterface {return null;}

	/**
	 * Fonction executée après le prochain middleware
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface      $response
	 * @return ResponseInterface
	 */
	public function afterProcess(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		return $response;
	}

	/**
	 * Fonction du middleware
	 * @param ServerRequestInterface  $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$before = $this->BeforeProcess($request);

		if($before !== null)
		{
			return $before;
		}
		$response = $handler->handle($request);
		return $this->AfterProcess($request,$response);
	}
}
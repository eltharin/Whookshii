<?php

namespace Core\App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorCatcher extends MiddlewareAbstract
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try
		{
			return $handler->handle($request);
		}
		catch(\Throwable $t)
		{
			$errorHandler = \Config::getErrors()->getConfig ('fatalErrorHandler');
			if($errorHandler !== null)
			{
				return 	$errorHandler($t);
			}
			return new Response(500, [], 'Une erreur est survenue.');
		}
	}
}
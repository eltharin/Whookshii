<?php
namespace Core\App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class TraillingSlash extends MiddlewareAbstract
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$uri = $request->getUri()->getPath();
		if($uri !== '/' && substr($uri,-1) === '/')
		{
			return new Response('301',['Location'=>substr($uri,0,-1)]);
		}

		return $handler->handle($request);
	}
}
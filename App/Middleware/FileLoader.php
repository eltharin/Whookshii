<?php
namespace Core\App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FileLoader extends MiddlewareAbstract
{
	public function beforeProcess(ServerRequestInterface $request) : ?ResponseInterface
	{
		$uri = urldecode(ltrim($request->getUri()->getPath(),'/'));

		if(($uri === '') || (strpos($uri,'.') === false))
		{
			return null;
		}

		if(($file = \Core\App\Loader::file($uri)) !== null)
		{
			$response = \HTTP::getResponseWithFile($file);

			return $response;
		}
		elseif(($file = \Core\App\Loader::fileVendor($uri)) !== null)
		{
			return \HTTP::getResponseWithFile($file);
		}
		return null;
	}
}
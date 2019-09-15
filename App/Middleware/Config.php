<?php
namespace Core\App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Config extends MiddlewareAbstract
{
	public function beforeProcess(ServerRequestInterface $request) : ?ResponseInterface
	{
		if(file_exists(SPECS . DS . 'config.php'))
		{
			require_once(SPECS . DS . 'config.php');
		}
		if(file_exists(SPECS . DS . 'config.inc'))
		{
			require_once(SPECS . DS . 'config.inc');
		}

		return null;
	}
}
<?php
namespace Core\App\Middleware;

use Core\App\Exception\HttpException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Config extends MiddlewareAbstract
{
	public function beforeProcess(ServerRequestInterface $request) : ?ResponseInterface
	{
		try
		{
			if(file_exists(SPECS . DS . 'config.php'))
			{
				$r1 = require_once(SPECS . DS . 'config.php');
			}
			if(file_exists(SPECS . DS . 'config.inc'))
			{
				$r2 = require_once(SPECS . DS . 'config.inc');
			}
		}
		catch(HTTPException $e)
		{
			return new Response($e->getCode(),[],$e->getMessage());
		}

		if($r1 != null)
		{
			return $r1;
		}
		if($r2 != null)
		{
			return $r2;
		}
		return null;
	}
}
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

				if($r1 instanceof ResponseInterface)
				{
					return $r1;
				}
			}
			if(file_exists(SPECS . DS . 'config.inc'))
			{
				$r2 = require_once(SPECS . DS . 'config.inc');

				if($r2 instanceof ResponseInterface)
				{
					return $r2;
				}

			}
		}
		catch(HTTPException $e)
		{
			return new Response($e->getCode(),[],$e->getMessage());
		}

		return null;
	}
}
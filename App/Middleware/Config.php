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
				require_once(SPECS . DS . 'config.php');
			}
			if(file_exists(SPECS . DS . 'config.inc'))
			{
				require_once(SPECS . DS . 'config.inc');
			}
		}
		catch(HTTPException $e)
		{
			return new Response($e->getCode(),[],$e->getMessage());
		}

		return null;
	}
}
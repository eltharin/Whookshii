<?php
namespace Core\App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class ShutdownSession extends MiddlewareAbstract
{
	private $enCours = false;
	
	public function  beforeProcess(ServerRequestInterface $request) : ?ResponseInterface
	{
		if(session_status () != \PHP_SESSION_NONE )
		{
			$this->enCours = true;
			session_write_close();
		}
		return null;
	}

	public function afterProcess(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		if($this->enCours)
		{
			$s = $_SESSION;
			session_start();
			$_SESSION = $s;
			unset($s);
		}

		return $response;
	}
}
<?php
namespace Core\App\Middleware;

use Core\App\Exception\HttpException;
use Core\App\Exception\RedirectException;
use Core\App\Exception\RenderResponseException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;


class Launcher extends AbstractMiddleware
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{

		if(($callback = $request->getAttribute('__route')->getCallback()) !== null)
		{
			if($callback === '')
			{
				throw new \Exception('No Callback');
			}

			try
			{
				$actionReturn = null;
				try
				{
					ob_start();
					$actionReturn = $request->getAttribute('__route')->execute($request);
				}
				catch(RenderResponseException $e)
				{

				}

				$content = ob_get_clean();

				if($actionReturn !== null && $actionReturn instanceof Response)
				{
					return $actionReturn;
				}
				return \Config::get('Response')->getResponse()->withBody(\GuzzleHttp\Psr7\stream_for($content));
			}
			catch(HTTPException $e)
			{
				$content = ob_get_clean();
				return new Response($e->getCode()?:500,[],$content . $e->getMessage());
			}
			catch(RedirectException $e)
			{
				//return new Response(301,['Location'=>$e->getMessage()]);
				return new Response(200,[],'Redirection');
			}
		}

		return new Response(500,[],'Erreur serveur');

	}
}

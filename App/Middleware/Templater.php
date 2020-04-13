<?php
namespace Core\App\Middleware;

use Core\App\Exception\HttpException;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function GuzzleHttp\Psr7\stream_for;

class Templater extends MiddlewareAbstract
{
	public function afterProcess(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		if(\Config::get('HTMLTemplate') === null || \Config::get('HTMLTemplate')->getTemplate() === null || \Config::get('HTMLTemplate')->getNoTemplate() === true || \Config::get('Vars')->getConfig('modeAjax') || $request->getAttribute('SAPI')=='CLI')
		{
			return $response;
		}

		$buffer = $this->render($response->getBody(), \Config::get('HTMLTemplate')->getTemplate());
		return $response->withBody(stream_for($buffer));
	}
	
	private function render(string $content, ?String $file)
	{
		$template = \Core\App\Loader::SearchFile($file ,'.php','Templates',true);

		if($template === null)
		{
			throw new HttpException('Le template ' . $file . ' est inconnu',404);
		}

		ob_start();
		require $template['file'];
		$content = ob_get_clean();

		return $content;
	}
}
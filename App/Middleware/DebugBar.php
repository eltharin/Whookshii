<?php
namespace Core\App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class DebugBar extends MiddlewareAbstract
{

	public function  beforeProcess(ServerRequestInterface $request) : ?ResponseInterface
	{
		\Config::createElement('DebugBar');
		return null;
	}

	public function afterProcess(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		$debugbar = new \DebugBar\StandardDebugBar();

		$debugbar["messages"]->addMessage("hello world!");

		//$this->debugbar->addCollector(new \DebugBar\DataCollector\RequestDataCollector());
		$debugbar->addCollector(new \DebugBar\DataCollector\MessagesCollector('SQL'));
		$debugbar['SQL']->addMessage(\DEBUG::get_sql());

		$debugbarRenderer = $debugbar->getJavascriptRenderer();

		$body = $response->getBody();
		$body = str_replace('</head>',$debugbarRenderer->renderHead() . '</head>',$body);
		$body = str_replace('</body>',$debugbarRenderer->render() . '</body>',$body);
		return $response->withbody(\GuzzleHttp\Psr7\stream_for($body));
	}
}
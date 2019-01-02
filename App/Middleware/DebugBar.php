<?php

namespace Core\App\Middleware;


class DebugBar extends MiddlewareAbstract
{

	public function BeforeProcess()
	{
		$this->debugbar = new \DebugBar\StandardDebugBar();

		$this->debugbar["messages"]->addMessage("hello world!");

	}

	public function AfterProcess()
	{
		foreach(\Core::$response->get_exceptions() as $e)
		{
			$this->debugbar['exceptions']->addException($e);
		}

		//$this->debugbar->addCollector(new \DebugBar\DataCollector\RequestDataCollector());
		$this->debugbar->addCollector(new \DebugBar\DataCollector\MessagesCollector('SQL'));
		$this->debugbar['SQL']->addMessage(\DEBUG::get_sql());

		$debugbarRenderer = $this->debugbar->getJavascriptRenderer();

		$body =\Core::$response->get_body();
		$body = str_replace('</head>',$debugbarRenderer->renderHead() . '</head>',$body);
		$body = str_replace('</body>',$debugbarRenderer->render() . '</body>',$body);
		\Core::$response->set_body($body);
	}
}
<?php
namespace Core\App\MiddlewareInterface;

class Whoops extends MiddlewareAbstract
{
	public function beforeProcess()
	{
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	}
}
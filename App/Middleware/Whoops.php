<?php
/**
 * Created by PhpStorm.
 * User: eltharin
 * Date: 12/11/2017
 * Time: 16:40
 */

namespace Core\App\MiddlewareInterface;


class Whoops
{
	public function BeforeProcess()
	{
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	}
}
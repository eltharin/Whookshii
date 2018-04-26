<?php


namespace Core\App\Middleware;


class Templater extends MiddlewareInterface
{
	public function BeforeProcess()
	{
		ob_start();
	}

	public function AfterProcess()
	{
		$buffer = ob_get_clean();
		\Core::$response->set_body(\Core::$response->get_body().$buffer);

		if ($cls = \Config::get_classTemplate())
		{
			$cls->render(\Config::get_template());
		}
	}
}
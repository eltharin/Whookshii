<?php


namespace Core\App\Middleware;


class FileLoader extends MiddlewareInterface
{
	public function BeforeProcess()
	{
		$request = \Core::$request->get_request();
		if(($request === '') || (strpos($request,'.') === false))
		{
			return true;
		}

		if(($file = \Core\App\Loader::file($request)) !== null)
		{
			\HTTP::show_file($file);
			return false;
		}
		elseif(($file = \Core\App\Loader::fileVendor($request)) !== null)
		{
			\HTTP::show_file($file);
			return false;
		}
	}

}
<?php
namespace Core\App\Middleware;

class Config extends MiddlewareInterface
{
	public function BeforeProcess()
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
}
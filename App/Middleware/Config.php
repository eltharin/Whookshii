<?php
namespace Core\App\Middleware;

class Config extends MiddlewareInterface
{
	public function BeforeProcess()
	{
		if(file_exists(SPECS . DS . 'Config.php'))
		{
			require_once(SPECS . DS . 'Config.php');
		}
		if(file_exists(SPECS . DS . 'Config.inc'))
		{
			require_once(SPECS . DS . 'Config.inc');
		}
	}
}
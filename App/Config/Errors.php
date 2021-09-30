<?php

namespace Core\App\Config;

class Errors extends AbstractConfigElement
{
	protected const AUTOFILECONFIG = 'auto.errors';

	public function __construct()
	{
		//parent::__construct();
		$this->LoadConfig();

		if($this->getConfig('errorHandler') !== null)
		{
			set_error_handler($this->getConfig('errorHandler'), $this->getConfig('catchErrorsTypes') ?? E_ALL);
		}
		else
		{
			set_error_handler(function (int $errno,	string $errstr,string $errfile = null, int $errline = null, array $errcontext = null) {\DEBUG::error ($errno,	$errstr,$errfile, $errline , $errcontext);}, E_ALL);
		}
	}

}
<?php
namespace Core\App\Config;

class Providers extends ConfigElementAbstract
{
	protected const AUTOFILECONFIG = 'auto.providers';

	public function getDefault()
	{
		if(count($this->config) == 1)
		{
			return reset($this->config);
		}
		return null;
	}

}
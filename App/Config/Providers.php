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
		return $this->config['default'] ?? null;
	}

	public function add($key, $value, $params=[])
	{
		parent::add($key, $value, $params);

		if(isset($params['default']) && $params['default'] === true)
		{
			$this->config['default'] = $this->config[$key];
		}

		return $this->config[$key];
	}
}
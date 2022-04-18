<?php
namespace Core\App\Config;

class Providers extends AbstractConfigElement
{
	protected const AUTOFILECONFIG = 'auto.providers';

	public function getDefault()
	{
		if(isset($this->config['default']))
		{
			return $this->getProvider($this->config['default']);
		}
		elseif(count($this->config['providers']) == 1)
		{
			return reset($this->config['providers']);
		}
		else
		{
			return $this->getProvider('db.default');
		}
		return null;
	}

	public function getProvider($key)
	{
		return $this->config['providers'][$key] ?? null;
	}

	public function add($key, $value, $params=[])
	{
		if(isset($this->config['providers'][$key]))
		{
			throw new \Exception('La clé ' . $key . 'existe déjà');
		}
		$this->config['providers'][$key] = $value;
		return $this->config['providers'][$key];
	}
}
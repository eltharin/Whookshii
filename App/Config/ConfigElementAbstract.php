<?php
namespace Core\App\Config;

class ConfigElementAbstract
{
	protected const AUTOFILECONFIG = null;

	protected $config = [];

	public function loadConfig(string $file = null)
	{
		$file = ($file??static::AUTOFILECONFIG);

		if($file !== null)
		{
			if(($config = $this->LoadConfigFile($file)) !== null)
			{
				$this->config = $config;
			}
		}
	}

	public function addConfig(string $file = null)
	{
		$file = ($file??static::AUTOFILECONFIG);

		if($file !== null)
		{
			$this->config = array_merge($this->config,require($file) ?? []);
		}
	}

	/**
	 * Retourne un élément de la config, si aucun élément n'est spécifié, l'ensemble de la configuration est retourné
	 * @param string $key
	 * @return array|String
	 */
	public function getConfig(string $key = '')
	{
		if($key === '')
		{
			return $this->config;
		}
		return $this->config[$key]??null;
	}

	/**
	 * Charge un fichier de configuration
	 * @param string $fileConfig
	 */
	public function LoadConfigFile($fileConfig)
	{
		if(file_exists(CONFIG . $fileConfig . '.php'))
		{
			return require(CONFIG . $fileConfig . '.php');
		}
		return null;
	}

	public function add($key, $value, $params=[])
	{
		if(isset($this->config[$key]))
		{
			throw new \Exception('La clé ' . $key . 'existe déjà');
		}
		$this->config[$key] = $value;
		return $this->config[$key];
	}

	public function setConfig($key, $value)
	{
		$this->config[$key] = $value;
	}
}
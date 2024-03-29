<?php
namespace Core\App\Config;

use Core\App\Router\Route;

class Routes extends AbstractConfigElement
{
	const AUTOFILECONFIG = 'auto.routes';

	protected $config = [
		'routes' => [],
		'automaticsRoutes' => true,
		'defaultRoute' => 'index/index',
	];

	public function loadConfig(string $file = null)
	{
		$fileconfigcontent = $this->LoadConfigFile($file ?? static::AUTOFILECONFIG);

		if($fileconfigcontent !== null)
		{
			foreach($fileconfigcontent as $name => $config)
			{
				if($name == 'routes')
				{
					foreach($config as $routeName => $route)
					{
						$this->addRoute($route[0], $route[1], $route[2], $routeName, $route[3] ?? false, $route[4] ?? []);
					}
				}
				else
				{
					$this->config[$name] = $config;
				}
			}
		}
	}

	/**
	 * Creation d'une route
	 * @param string   $path
	 * @param array|string    $method
	 * @param callable|string $callback
	 * @param string   $name
	 */
	public function addRoute(string $path, $method, $callback, string $name, bool $withParam, array $properties = [])
	{
		$this->config['routes'][$name] = new Route(['path' => $path,'method' => $method,'callback' => $callback,'name' => $name,'withParam' => $withParam, 'properties' => $properties]);
	}

	public function setDefaultRoute($route)
	{
		$this->setConfig('defaultRoute',$route);
	}
}
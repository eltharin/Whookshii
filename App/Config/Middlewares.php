<?php
namespace Core\App\Config;

class Middlewares extends ConfigElementAbstract
{
	protected const AUTOFILECONFIG = 'auto.middlewares';

	protected $config = [
									\Core\App\Middleware\Subfolder::class,
									\Core\App\Middleware\FileLoader::class,
									//\Core\App\Middleware\Config::class,
									\Core\App\Middleware\Router::class,

									\Core\App\Middleware\Launcher::class
							];
}
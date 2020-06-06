<?php
namespace Core\App\Config;

class Middlewares extends ConfigElementAbstract
{
	protected const AUTOFILECONFIG = 'auto.middlewares';

	protected $config = [
								\Core\App\Middleware\TraillingSlash::class,
								\Core\App\Middleware\Subfolder::class,
								\Core\App\Middleware\FileLoader::class,
								\Core\App\Middleware\Config::class,
								\Core\App\Middleware\Router::class,

								\Core\App\Middleware\Templater::class,

								\Core\App\Middleware\ShutdownSession::class,

							];

	public function addNext($newMid)
	{
		$this->config[] = $newMid;
	}
}
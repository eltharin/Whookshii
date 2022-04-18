<?php
namespace Core\App\Config;

class Middlewares extends AbstractConfigElement
{
	protected const AUTOFILECONFIG = 'auto.middlewares';

	protected $config = [
								\Core\App\Middleware\TraillingSlash::class,
								\Core\App\Middleware\Subfolder::class,
								\Core\App\Middleware\FileLoader::class,
								\Core\App\Middleware\Router::class,
								\Core\App\Middleware\Config::class,

								\Core\App\Middleware\ErrorCatcher::class,
								\Core\App\Middleware\Templater::class,
								\Core\App\Middleware\ShutdownSession::class,

							];

	public function addNext($newMid)
	{
		$this->config[] = $newMid;
	}

	public function addBefore($middlewareBefore, $newMid)
	{
		$key = array_search($middlewareBefore, $this->config);

		if($key === false)
		{
			$this->addNext($newMid);
		}
		else
		{
			$this->config = array_merge(array_slice($this->config,0,$key),[$newMid],array_slice($this->config,$key));
		}
	}

	public function addAfter($middlewareAfter, $newMid)
	{
		$key = array_search($middlewareAfter, $this->config);

		if($key === false)
		{
			$this->addNext($newMid);
		}
		else
		{
			$this->config = array_merge(array_slice($this->config,0,$key+1),[$newMid],array_slice($this->config,$key+1));
		}
	}

	public function remove($middleware)
	{
		$key = array_search($middleware, $this->config);

		if($key !== false)
		{
			$this->config = array_merge(array_slice($this->config,0,$key),array_slice($this->config,$key+1));
		}
	}
}
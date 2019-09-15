<?php

return [
	\Core\App\Middleware\TraillingSlash::class,
	//\Middlewares\Whoops::class,
	//\Core\App\Middleware\Subfolder::class,
	\Core\App\Middleware\FileLoader::class,

	\Core\App\Middleware\Config::class,
	//\Core\App\Middleware\Templater::class,
	\Core\App\Middleware\Router::class,

	\Core\App\Middleware\Launcher::class
];

/*
 * 		self::$dispatcher = new \Core\App\Dispatcher(\Core\App\Middleware\Launcher::class);

		self::$dispatcher->add_middleware(\Core\App\Middleware\Subfolder::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\FileLoader::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\Config::class);
		//self::$dispatcher->add_middleware(\Core\App\Middleware\Templater::class);
		self::$dispatcher->add_middleware(\Core\App\Middleware\Router::class);
 */
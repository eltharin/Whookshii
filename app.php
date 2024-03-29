<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$request = \Core\App\ServerRequest::fromGlobals();

if(PHP_SAPI == 'cli')
{
	$request = $request->withAttribute('SAPI','CLI');
	$argv = $_SERVER['argv'];
	unset($argv[0]);
	$request = $request->withUri($request->getUri()->withPath('/'.implode('/',$argv)));
}
else
{
	$request = $request->withAttribute('SAPI','WEB');
}

$app = new Core\Core();
$response = $app->run($request);

\HTTP\Response\send($response);

<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

if($request->getMethod() != 'GET' && $request->getMethod() != 'POST')
{
	if($request->getHeaderLine('Content-Type') == 'application/json')
	{
		$request = $request->withParsedBody(json_decode($request->getBody(),true));
	}
	else
	{
		$request = $request->withParsedBody($request->getBody());
	}
}


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

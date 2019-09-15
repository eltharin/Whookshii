<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$app = new Core\Core();
$response = $app->run($request);

\HTTP\Response\send($response);

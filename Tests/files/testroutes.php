<?php

return [
	'routes' => [
		'blog' 				=> ['/blog','*',function(){return '<h1>Bienvenue sur mon blog</h1>';}],
		'post.show' 		=> ['/blog/{slug:[a-z0-9\-]+}-{id:\d+}','*',function(){return 'Bienvene sur l\'article article-de-test';}],
		'post.controller'   => ['/toto','*','toto/tutu'],
	],
	'automaticsRoutes'	=> true,
];
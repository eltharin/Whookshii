<?php
namespace Core\App\Middleware;

abstract class MiddlewareAbstract
{
	//-- return null for stop processing
	public function beforeProcess() {}
	
	public function afterProcess() {}
}
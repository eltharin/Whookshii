<?php
namespace Core\Tests;

use Core\Core;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Core\App\Middleware\Router;

class RouterTest extends TestCase
{
	public function setUp() : void
	{
		$core = new Core();
	}

	public function testGoodMethod()
	{
		\Config::get('Routes')->setConfig('automaticsRoutes',false);
		\Config::get('Routes')->addRoute('/test','*',function(){return 'hello';},'test1', false);
		\Config::get('Routes')->addRoute('/test2','GET',function(){return 'bonjour';},'test2', false);
		\Config::get('Routes')->addRoute('/test3',['POST','PUT'],function(){return 'salut';},'test3', false);
		\Config::get('Routes')->addRoute('/test4',['POST','GET'],'test/actiondetest','test4', false);
		\Config::get('Routes')->addRoute('/test5',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test5', false);
		\Config::get('Routes')->addRoute('/test6/{param1:.*}-{param2:\d*}','GET','test/actiondetestavecparams','test6', false);
		\Config::get('Routes')->addRoute('/testprop','GET','test/actiondetestavecproperties','testprop', false, ['toto' => 'tutu', 'titi' => 'toto']);

		$router = new Router();

		$request = new ServerRequest('GET','/test');
		$route = $router->match($request);
		$this->assertEquals('test1',$route->getName());
		$this->assertEquals('hello',$route->execute($request));

		$request = new ServerRequest('GET','/test2');
		$route = $router->match($request);
		$this->assertEquals('test2',$route->getName());
		$this->assertEquals('bonjour',$route->execute($request));

		$request = new ServerRequest('POST','/test3');
		$route = $router->match($request);
		$this->assertEquals('test3',$route->getName());
		$this->assertEquals('salut',$route->execute($request));


		$request = new ServerRequest('GET','/test4');
		$route = $router->match($request);
		$this->assertEquals('test4',$route->getName());

		$request = new ServerRequest('GET','/test5');
		$route = $router->match($request);
		$this->assertEquals('test5',$route->getName());

		$request = new ServerRequest('GET','/test6/bonjour-18');
		$route = $router->match($request);
		$this->assertEquals('test6',$route->getName());

		$request = new ServerRequest('GET','/test6/salut-46');
		$route = $router->match($request);
		$this->assertEquals('test6',$route->getName());


		$request = new ServerRequest('GET','/testprop');
		$route = $router->match($request);
		$route->getCallback()->analyse();
		$this->assertEquals('testprop',$route->getName());

	}

	public function testGetMethodIfNotExist()
	{
		\Config::get('Routes')->setConfig('automaticsRoutes',false);
		\Config::get('Routes')->addRoute('/testqsdqsd','*',function(){return 'hello';},'test1', false);
		\Config::get('Routes')->addRoute('/test','POST',function(){return 'salut';},'test2', false);
		\Config::get('Routes')->addRoute('/test',['POST','PUT'],function(){return 'bonjour';},'test3', false);

		$router = new Router();
		$request = new ServerRequest('GET','/test');

		$route = $router->match($request);
		$this->assertEquals(null,$route);
	}

	public function testGetMethodWithParams()
	{
		\Config::get('Routes')->setConfig('automaticsRoutes',false);
		$request = new ServerRequest('GET','/test/mon-slug-8');

		\Config::get('Routes')->addRoute('/test','*',function(){return 'qsdsqd';},'posts', false);
		\Config::get('Routes')->addRoute('/test/{slug:[a-z0-9\-]+}-{id:\d+}','*',function(){return 'hello';},'post.show', false);

		$router = new Router();

		$route = $router->match($request);
		$this->assertEquals('post.show', $route->getName());
		$this->assertEquals('hello',$route->execute($request));
		$this->assertEquals(['slug' => 'mon-slug','id' => '8'],$route->getParams());

		$route = $router->match(new ServerRequest('GET','/test/mon_test-189'));
		$this->assertEquals(null,$route);
	}

	public function testAutomaticRoute()
	{
		\Config::get('Routes')->setConfig('automaticsRoutes',true);

		$router = new Router();

		$request = new ServerRequest('GET','/test/actionautomatiquedetest/toto');
		$route = $router->match($request);

		$this->assertNotNull($route);
		$this->assertEquals('automatic',$route->getName());
		$this->assertEquals(['_params' => ['0' => 'toto']],$route->getParams());
		//$this->assertEquals('les routes automatiques fonctionnent et le param est toto',$route->getCallback()->exec($route->getParams()));

		$request = new ServerRequest('GET','/test/actionautomatiquedetest/tutu');
		$route = $router->match($request);
		$this->assertNotNull($route);
		$this->assertEquals('automatic',$route->getName());
		$this->assertEquals(['_params' => ['0' => 'tutu']],$route->getParams());
		//$this->assertEquals('les routes automatiques fonctionnent et le param est tutu',$route->getCallback()->exec($route->getParams()));
	}


	public function testComplexeRule()
	{
		\Config::get('Routes')->addRoute('/admin/{controller:.*}/{action:.*}','*',['controller' => 'admin/{controller}', 'action' => '{action}'],'admin', true);

		$router = new Router();

		$request = new ServerRequest('GET','/admin/machin/truc/toto/tutu');
		$route = $router->match($request);

		$this->assertNotNull($route);
		$this->assertEquals('admin',$route->getName());
		$this->assertEquals(['_params' => ['0' => 'toto','1' => 'tutu']],$route->getParams());
		//$this->assertEquals('les routes automatiques fonctionnent et le param est toto',$route->getCallback()->exec($route->getParams()));

	}
}
<?php
namespace Tests\Core;

use Core\Controllers\Test;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
	private $app;

	protected function setUp(): void
	{
		$this->app = new \Core\Core();
		@mkdir(CORE . DS . 'Files');
		@mkdir(CORE . DS . 'Files' . DS . 'css');
		copy(__DIR__ . DS . 'files' . DS . 'testfile.css', CORE . DS . 'Files' . DS . 'css' . DS . 'testfile.css');

		$tmpMiddlewaresConfigFile = 'testmiddlewares';
		$tmpRoutesConfigFile = 'testroutes';

		@mkdir(CORE . DS . 'Controllers');
		@mkdir(CORE . DS . 'Controllers' . DS . 'TestNamespace');

		copy(__DIR__ . DS . 'files' . DS . 'TestController.php', CORE . DS . 'Controllers' . DS . 'Test.php');
		copy(__DIR__ . DS . 'files' . DS . 'TestNamespacedController.php', CORE . DS . 'Controllers' . DS . 'TestNamespace' . DS . 'Test.php');

	}

	public function tearDown(): void
	{
		unlink(CORE . DS . 'Files' . DS . 'css' . DS . 'testfile.css');

		@rmdir(CORE . DS . 'Files' . DS . 'css');
		@rmdir(CORE . DS . 'Files');


		unlink(CORE . DS . 'Controllers' . DS . 'Test.php');
		unlink(CORE . DS . 'Controllers' . DS . 'TestNamespace' . DS . 'Test.php');

		@rmdir(CORE . DS . 'Controllers' . DS . 'TestNamespace');
		@rmdir(CORE . DS . 'Controllers');
	}

	function testDemoTraillingSlash()
	{
		$request = new ServerRequest('GET' , '/test/');
		$response = $this->app->run($request);

		$this->assertContains('/test',$response->getHeader('Location'));
		$this->assertEquals(301,$response->getStatusCode());
	}

	function testFileLoader()
	{
		$request = new ServerRequest('GET' , '/css/testfile.css');
		$response = $this->app->run($request);

		$this->assertEquals(200,$response->getStatusCode());
		$this->assertContains('text/css',$response->getHeader('Content-type'));
		$this->assertStringContainsString('/*css file is working*/',(string)$response->getBody());
	}

	function testErreur404()
	{
		$request = new ServerRequest('GET' , '/urlquinexistepas');
		$response = $this->app->run($request);
		$this->assertEquals(404,$response->getStatusCode());
		$this->assertStringContainsString('Controller urlquinexistepas Not Found',(string)$response->getBody());


		$request = new ServerRequest('GET' , '/test/methodequinexistepas');
		$response = $this->app->run($request);
		$this->assertEquals(404,$response->getStatusCode());
		$this->assertStringContainsString('Method methodequinexistepas Not Found in',(string)$response->getBody());
	}


	function testGoodLaunch1()
	{
		\Config::get('Routes')->addRoute('/test1',['POST','GET'],['controller' => Test::class, 'action' => 'actiondetest'],'test1', false);
		\Config::get('Routes')->addRoute('/test2',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test2', false);
		\Config::get('Routes')->addRoute('/test3/{param1:.*}-{param2:\d*}','GET','test/actiondetestavecparams','test3', false);

		$request = new ServerRequest('GET','/test1');
		$response = $this->app->run($request);
		$this->assertEquals(200,$response->getStatusCode());
		$this->assertStringContainsString('action de test fonctionne',(string)$response->getBody());


	}
	function testGoodLaunch2()
	{
		\Config::get('Routes')->addRoute('/test1',['POST','GET'],['controller' => Test::class, 'action' => 'actiondetest'],'test1', false);
		\Config::get('Routes')->addRoute('/test2',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test2', false);
		\Config::get('Routes')->addRoute('/test3/{param1:.*}-{param2:\d*}','GET','test/actiondetestavecparams','test3', false);


		$request = new ServerRequest('GET','/test2');
		$response = $this->app->run($request);
		$this->assertEquals(200,$response->getStatusCode());
		$this->assertStringContainsString('action de test avec namespace fonctionne.',(string)$response->getBody());


	}
	function testGoodLaunch3()
	{
		\Config::get('Routes')->addRoute('/test1',['POST','GET'],['controller' => Test::class, 'action' => 'actiondetest'],'test1', false);
		\Config::get('Routes')->addRoute('/test2',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test2', false);
		\Config::get('Routes')->addRoute('/test3/{param1:.*}-{param2:\d*}','GET','test/actiondetestavecparams','test3', false);

		$request = new ServerRequest('GET','/test3/bonjour-18');
		$response = $this->app->run($request);
		$this->assertEquals(200,$response->getStatusCode());
		$this->assertStringContainsString('Les params sont bonjour et 18.',(string)$response->getBody());

	}
	function testGoodLaunch4()
	{
		\Config::get('Routes')->addRoute('/test1',['POST','GET'],['controller' => Test::class, 'action' => 'actiondetest'],'test1', false);
		\Config::get('Routes')->addRoute('/test2',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test2', false);
		\Config::get('Routes')->addRoute('/test',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test4', true);
		\Config::get('Routes')->addRoute('/test3/{param1:.*}-{param2:\d*}','GET','test/actiondetestavecparams','test3', false);


		$request = new ServerRequest('GET','/test3/salut-46');
		$response = $this->app->run($request);
		$this->assertEquals(200,$response->getStatusCode());
		$this->assertStringContainsString('Les params sont salut et 46.',(string)$response->getBody());

	}


	function testGoodLaunchwithprop()
	{
		\Config::get('Routes')->addRoute('/test1',['POST','GET'],['controller' => Test::class, 'action' => 'actiondetest'],'test1', false);
		\Config::get('Routes')->addRoute('/test2',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test2', false);
		\Config::get('Routes')->addRoute('/test',['POST','GET'],'testnamespace/test/actiondetestavecnamespace','test4', true);
		\Config::get('Routes')->addRoute('/testprop','GET','test/actiondetestavecproperties','testprop', false, ['toto' => 'tutu', 'titi' => 'toto']);

		$request = new ServerRequest('GET','/testprop');
		$response = $this->app->run($request);

		$this->assertEquals(200,$response->getStatusCode());
		$this->assertEquals('La propriété toto est tutu.'.BRN.'La propriété titi est toto.'.BRN,(string)$response->getBody());
	}
}
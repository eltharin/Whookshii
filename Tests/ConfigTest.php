<?php
namespace Tests\Core;

use Core\Core;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
	public function testLoadConfig()
	{
		$core = new Core();

		$tmpMiddlewaresConfigFile = 'testmiddlewares';
		$tmpRoutesConfigFile = 'testroutes';

		copy(__DIR__ . DS . 'files' . DS . 'testmiddlewares.php', CONFIG . $tmpMiddlewaresConfigFile . '.php');
		copy(__DIR__ . DS . 'files' . DS . 'testroutes.php', CONFIG . $tmpRoutesConfigFile . '.php');

		\Config::get('Middlewares')->LoadConfig($tmpMiddlewaresConfigFile);
		\Config::get('Routes')->LoadConfig($tmpRoutesConfigFile);

		$this->assertInstanceOf(\Core\App\Config\Middlewares::class,\Config::get('Middlewares'));
		$this->assertIsArray(\Config::get('Middlewares')->getConfig());

		$this->assertInstanceOf(\Core\App\Config\Routes::class,\Config::get('Routes'));
		$routesConfig = \Config::get('Routes')->getConfig()['routes'];
		$this->assertIsArray($routesConfig);
		$this->assertArrayHasKey('blog',$routesConfig);

		$this->assertNull(\Config::get('ConfigDoesntExists'));
		//getConfig($configName)

		unlink(CONFIG . $tmpMiddlewaresConfigFile . '.php');
		unlink(CONFIG . $tmpRoutesConfigFile . '.php');
	}
}
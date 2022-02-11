<?php
namespace Core;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\App\Exception\HttpException;

class Core
{
	private $config;

	public function __construct()
	{
		setlocale(LC_TIME, "fra_FRA");
		error_reporting(-1);
		ini_set('display_errors', true);

		define('DS',DIRECTORY_SEPARATOR);
		define('CORE', __DIR__ . DS);
		define('APP',CORE . 'App' . DS);

		if(!defined('BASE_URL')) {define('BASE_URL','');}
		//define('BASE_URL',self::$request->get_subfolder());

		class_alias(\Core\Classes\Debug::class,'Debug');
		class_alias(\Core\App\Config::class,'Config');
		class_alias(\Core\App\Http::class,'HTTP');
		class_alias(\Core\App\Html::class,'HTML');
		class_alias(\Core\App\Auth::class,'Auth');
		class_alias(\Core\App\ACL::class,'ACL');

		require_once APP . 'Require.php';

		if(file_exists (CONFIG . 'env.php'))
		{
			$_ENV = array_merge($_ENV, include(CONFIG . 'env.php'));
		}

		if(session_status () == \PHP_SESSION_NONE )
		{
			session_start();
		}

		\Auth::init();
		\Config::init();

		
		if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' && empty($_POST))
		{
			$_POST = json_decode(file_get_contents('php://input'),1);
		}
	}

	public function run(\Psr\Http\Message\ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
	{
		\Config::get('Vars')->setConfig('modeAjax',(isset($request->getServerParams()['HTTP_X_REQUESTED_WITH']) && strtolower($request->getServerParams()['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));

		$response = (new \Core\App\Dispatcher())
				->handle($request);


		return $response;
	}
}
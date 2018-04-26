<?php
session_start();
//-- start with errors
error_reporting(-1);
ini_set('display_errors', true);


setlocale(LC_TIME, "fra_FRA");
//class CoreException extends Exception{};

define('DS',DIRECTORY_SEPARATOR);
define('CORE', dirname(__FILE__) . DS);
define('APP',CORE . 'App' . DS);

require_once APP . 'require.php';

spl_autoload_register('core\App\Loader::ClassAutoload');
spl_autoload_register('core\App\Loader::fullNamed');

if(file_exists(ROOT . 'vendor' . DS . 'autoload.php'))
{
	require ROOT . 'vendor' . DS . 'autoload.php';
}

Core::launch_middleware();


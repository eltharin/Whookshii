<?php
namespace Core\Controllers;


class file extends \Core\App\Mvc\Controller
{
	function Action_show(...$file)
	{
		\Config::set_template(null);
		$file = ROOT . 'plugin' . DS . 'intranet' . DS . 'files' . DS . implode(DS,$file);

		file_put_contents('D:\\WEB\\erreurfile.log',$file.RN,FILE_APPEND);

		if(file_exists($file))
		{
			\HTTP::show_file($file);
		}
		else
		{
			\HTTP::error_page(404);
		}
	}
}
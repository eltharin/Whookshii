<?php


namespace Core\App\Middleware;


class Subfolder extends MiddlewareAbstract
{
	public function aAfterProcess()
	{
		$content = $response->get_body();

	//	$content = preg_replace(array('# href=([\"\']{1})\^\/#'), ' href=${1}/' . BASE_URL  . '/', $content);
		$content = preg_replace('#([\s.]{1})(href|src|action)[\s]*=[\s]*([\"\']{1})/#', '${1}${2}=${3}' . BASE_URL . '/', $content);
		$content = preg_replace('#([\s.]{1})(href|src|action)[\s]*=[\s]*\\\\([\"\']{1})\\\/#', '${1}${2}=\\\${3}' . str_replace('/','\\/',BASE_URL) . '\\/', $content);
		//$content = preg_replace('#href=\\\"\\\/#', 'href=\\"\\' . str_replace('\\','\\\\',BASE_URL) . '\\/', $content);
		//$content = preg_replace(array('# src[\s]*=[\s]*([\"\']{1})/#'), ' src=${1}' . BASE_URL . '/', $content);
		//$content = preg_replace(array('# action[\s]*=[\s]*([\"\']{1})/#'), ' action=${1}' . BASE_URL . '/', $content);
		
		$content = preg_replace(array('#(url|image)[\s]*:[\s]*([\"\']{1})/#'), ' ${1} : ${2}' . BASE_URL . '/', $content);
		

		$content = preg_replace(array('#:[\s]*url[\s]*\(([\"\']{1})/#'), ' : url(${1}' . BASE_URL . '/', $content);
		$content = preg_replace(array('#\.load[\s]*\(([\"\']{1})/#'), '.load(${1}' . BASE_URL . '/', $content);
		$content = preg_replace(array('#\.(post|getJSON|get)[\s]*\(([\"\']{1})/#'), '.${1}(${2}' . BASE_URL . '/', $content);
		$content = preg_replace(array('#url[\s]*\(/#'), 'url(' . BASE_URL . '/', $content);
		$content = preg_replace(array('#url[\s]*\(([\"\']{1})/#'), 'url(${1}' . BASE_URL . '/', $content);

		$response->set_body($content);
	}
}
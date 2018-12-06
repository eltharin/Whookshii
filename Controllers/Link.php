<?php
namespace Core\Controllers;


class Link extends \Core\Mvc\Controller
{
	function Action_toto($params)
	{
		echo 'lancement de l\'appli core' . BRN;
		var_dump($params);
	}
}
<?php
namespace Core\Controllers;

use Core\App\Mvc\Controller;

class Test extends Controller
{
	public function Action_actiondetest()
	{
		echo 'action de test fonctionne.';
	}

	public function Action_actiondetestavecparams($params)
	{
		echo 'Les params sont ' . $params['param1'] . ' et ' . $params['param2'] . '.';
	}

	public function Action_actionautomatiquedetest($param)
	{
		var_dump($param);
		echo 'les routes automatiques fonctionnent et le param est ' . $param;
	}
}
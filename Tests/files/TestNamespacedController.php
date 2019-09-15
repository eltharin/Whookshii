<?php
namespace Core\Controllers\TestNamespace;

use Core\App\Mvc\Controller;

class Test extends Controller
{
	public function Action_actiondetestavecnamespace()
	{
		echo 'action de test avec namespace fonctionne.';
	}
}
<?php


namespace Core\App\Middleware;


class Templater extends MiddlewareAbstract
{
	public function BeforeProcess()
	{
		ob_start();
	}

	public function AfterProcess()
	{
		$buffer = ob_get_clean();
		\Core::$response->set_body(\Core::$response->get_body().$buffer);

		$this->render(\Core::$config->HTMLtemplate->get_template());
	}
	
	private function render(?String $file)
	{
		if(\Core::$response->getWithTemplate() && \Core::$config->HTMLtemplate->get_template() != '')
		{
			$template = \Core\App\Loader::SearchFile($file ,'.php','Templates',true);

			if($template === null)
			{
				\HTTP::error_page(404,'Le template ' . $file . ' est inconnu');
				return false;
			}

			ob_start();
			require $template['file'];
			$content = ob_get_contents();
			ob_end_clean();

			\Core::$response->set_body($content);
		}
	}
}
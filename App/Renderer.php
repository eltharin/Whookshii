<?php


namespace Core\App;


class Renderer //implements \Core\Interfaces\TemplateInterface
{
	private $title;
	private $css = [];
	private $script = [];


	public function render(?String $file)
	{
		if(!\Core::$request->get_noTemplate() && \Config::get_template() != '')
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
<?php

namespace Core\App\Config;

class HTMLTemplate extends ConfigElementAbstract
{
	protected $noTemplate = false;
	protected $css = [];
	protected $scripts = [];
	protected $menu = [];
	protected $template_file = null;
	protected $globaltitle = '';
	protected $title = '';
	protected $message = [];

	public function __construct()
	{
		$this->actualmessage = $_SESSION['__message'];
		$_SESSION['__message'] = [];
		$this->message = &$_SESSION['__message'];
	}

	/**
	 * @param bool $noTemplate
	 */
	public function setNoTemplate(bool $noTemplate): void
	{
		$this->noTemplate = $noTemplate;
	}

	public function getNoTemplate()
	{
		return $this->noTemplate;
	}

	public function getTemplate()
	{
		return $this->template_file;
	}

	public function setTemplate($template_file)
	{
		$this->template_file = $template_file;
	}
	
	public function getCss()
	{
		$ret = '';
		foreach (array_keys($this->css) as $val)
		{
			$ret .= "\t" . '<link rel=\'stylesheet\'  href=\'' . $val . '\' type=\'text/css\'>' . "\r\n";
		}
		return $ret;
	}

	public function addCss($css)
	{
		if(\Core::$request->get_modeapi() == '')
		{
			$this->css[$css] = $css;
		}
		else
		{
			echo '<script id="addcss">if($($($("head").get(0)).children("link[type=\'text/css\']")).filter("link[href=\'/' . $css . '\']").length == 0){$("head").append($(\'<link rel="stylesheet" type="text/css" href="' . $css . '" media="screen" >\'));$(\'#addcss\').remove();}</script>';
		}
	}

	public function getScript()
	{
		$ret = '';
		foreach (array_keys($this->scripts) as $val)
		{
			$ret .= "\t" . '<script type=\'text/javascript\' src=\'' . $val . '\'></script>' . RN;
		}
		return $ret;
	}

	public function addScript($script)
	{
		if(\Core::$request->get_modeapi() == '')
		{
			$this->scripts[$script] = $script;
		}
		else
		{
			echo '<script id="addscript">if($($($("head").get(0)).children("script[type=\'text/javascript\']")).filter("script[src=\'' . $script . '\']").length == 0){$("head").append($(\'<script type="text/javascript" src="' . $script . '"><\/script>\'));$(\'#addscript\').remove();}</script>';
		}
	}

	public function setGlobaltitle($title)
	{
		$this->globaltitle = $title;
	}

	public function getGlobaltitle($title)
	{
		$this->globaltitle = $title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function getTitle()
	{
		if ($this->globaltitle != '')
			return $this->globaltitle . ' - ' . $this->title;
		else
			return $this->title;
	}
	
	public function hasMenu()
	{
		return !empty($this->menu);
	}
	
	public function addMenu(array $arr)
	{
		$this->menu[] = $arr;
	}
	
	public function set_menu(array $arr)
	{
		$this->menu = $arr;
	}
	
	public function getMenu()
	{
		$ret = '';
		return $ret;
	}

	public function addMessage(string $message, array $options) : void
	{
		$options = array_merge(['type' => 'info','position' => 'top'], $options);

		$this->message[$options['position']][] = '<div class="' . $options['type'] . '">' . $message . '</div>';
	}

	public function getMessages(string $position) : string
	{
		$messages = implode('',$this->actualmessage[$position]??[]);
		unset($this->actualmessage[$position]);
		return $messages;
	}
}
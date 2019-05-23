<?php

namespace Core\App\Config;

class HTMLTemplate extends ConfigAbstract
{
	protected $css = [];
	protected $scripts = [];
	protected $menu = [];
	protected $template_file = null;
	protected $globaltitle = '';
	protected $title = '';
	
	public function get_template()
	{
		return $this->template_file;
	}

	public function set_template($template_file)
	{
		$this->template_file = $template_file;
	}
	
	public function get_css()
	{
		$ret = '';
		foreach (array_keys($this->css) as $val)
		{
			$ret .= "\t" . '<link rel=\'stylesheet\'  href=\'' . $val . '\' type=\'text/css\'>' . "\r\n";
		}
		return $ret;
	}

	public function add_css($css)
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

	public function get_script()
	{
		$ret = '';
		foreach (array_keys($this->scripts) as $val)
		{
			$ret .= "\t" . '<script type=\'text/javascript\' src=\'' . $val . '\'></script>' . RN;
		}
		return $ret;
	}

	public function add_script($script)
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

	public function set_globaltitle($title)
	{
		$this->globaltitle = $title;
	}
	
	public function set_title($title)
	{
		$this->title = $title;
	}

	public function get_title()
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
}
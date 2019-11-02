<?php
namespace Core\Classes;

class pdf extends \setasign\Fpdi\TcpdfFpdi
{
	private $FunctionHeader = null;
	private $FunctionFooter = null;
	private $vars = array();
	
	function setHeaderByFunction($fct)
	{
		$this->FunctionHeader = $fct;
	}
	
	function setFooterByFunction($fct)
	{
		$this->FunctionFooter = $fct;
	}
	
	function Header()
	{
		
		if($this->FunctionHeader !== null)
		{
			call_user_func($this->FunctionHeader,$this);
		}
		else
		{
			parent::Header();
		}
	}	
	
	function Footer()
	{
		
		if($this->FunctionFooter !== null)
		{
			call_user_func($this->FunctionFooter,$this);
		}
		else
		{
			parent::Footer();
		}
	}
	
	function get_num_pagegroup()
	{
		return $this->pagegroups[$this->currpagegroup];
	}
	
	public function Output($name='doc.pdf', $dest='I')
	{
		if($dest !== 'S')
		{
			\Config::get('HTMLTemplate')->setNoTemplate(true);
			\Config::get('Response')->addHeader('Content-type','application/pdf');
		}
		return parent::Output($name,$dest);
	}
	
	public function set_vars($k,$v)
	{
		$this->vars[$k] = $v;
	}
	
	public function get_vars($k)
	{
		if (isset($this->vars[$k]))
		{
			return $this->vars[$k];
		}
		return null;
	}
}
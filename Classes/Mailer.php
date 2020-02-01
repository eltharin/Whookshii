<?php
namespace Core\Classes;

class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
	public function __construct($exceptions = false)
	{
		parent::__construct($exceptions);
		
		$this->IsHTML(true);
		$this->SetLanguage('fr');
	}
	
	protected function addAnAddress($kind, $address, $name = '')
	{
		$address = explode(';',$address);
		foreach($address as $adr)
		{
			parent::addAnAddress($kind, $adr, $name);
		}
	}
}

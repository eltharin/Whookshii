<?php
namespace Core\Classes\OAuth;

use Core\App\Exception\Stop;

abstract class OAuth
{
	protected $configFile = '';
	protected $config = [];
	protected $auth = [];

	final public function __construct()
	{
		if($this->configFile === '' || !is_file($this->configFile))
		{
			throw new Stop('Config File not found in ' . (new \ReflectionClass($this))->getFileName());
		}
		$this->config = include($this->configFile);
	}

	abstract protected function getFieldId($data);
	abstract protected function getFieldFamilyName($data);
	abstract protected function getFieldGivenName($data);
	abstract protected function getFieldEmail($data);
	abstract protected function getFieldPicture($data);

	abstract public function getAutorizationAdress();
	abstract public function haveToConnect();
	abstract public function hasRefuseConnect();
	abstract public function getAuthenticateRequest();
	abstract public function getUserinfoRequest();

	public function redirectToLoginAdress()
	{
		\Core::$response->add_header('Location: ' . $this->getAutorizationAdress());
		echo '<a href="' . $this->getAutorizationAdress() . '" >Aller sur la page de connexion</a>';
		\Core::doBreak();
	}
	public function redirectToDisableLoginAdress()
	{
		\Core::$response->add_header('Location: ' . $this->config['refuseLoginURL']);
		echo '<a href="' . $this->config['refuseLoginURL'] . '" >Aller sur la page de connexion</a>';
		\Core::doBreak();
	}

	public function getInfosUser()
	{
		$this->authenticate();
		if(!empty($this->auth))
		{
			$data = $this->userinfo();
			return [
				'id' 	 => $this->getFieldId($data),
				'nom' 	 => $this->getFieldFamilyName($data),
				'prenom' => $this->getFieldGivenName($data),
				'email'  => $this->getFieldEmail($data),
				'photo'  => $this->getFieldPicture($data),
			];
		}
		return null;
	}

	public function connectAndGetInfos()
	{
		if($this->hasRefuseConnect())
		{
			$this->redirectToDisableLoginAdress();
			\Core::doBreak();
			return null;
		}

		if($this->haveToConnect())
		{
			if($info = $this->getInfosUser())
			{
				return $info;
			}
		}
		$this->redirectToLoginAdress();
		\Core::doBreak();
		return null;
	}

	public function authenticate()
	{
		$val = $this->getAuthenticateRequest();

		if($val->code == 200)
		{
			$this->auth = $val->data;
		}
		else
		{
			$this->redirectToLoginAdress();
			\HTTP::error_page('500','impossible de se connecter');
		}
	}

	public function userinfo()
	{
		if(($data = $this->getUserinfoRequest()) != null)
		{
			return $data;
		}

		$this->redirectToLoginAdress();
		\HTTP::error_page('500','impossible de récupérer les informations du compte.');
		return null;
	}
}
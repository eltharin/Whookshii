<?php
namespace Core\Classes\OAuth;

use Core\App\Exception\Stop;
use GuzzleHttp\Psr7\Response;
use Core\App\Exception\HttpException;

abstract class OAuth
{
	protected $configFile = '';
	protected $config = [];
	protected $auth = [];

	final public function __construct()
	{
		if($this->configFile === '' || !is_file($this->configFile))
		{
			\HTTP::errorPage(500, 'Config File not found in ' . (new \ReflectionClass($this))->getFileName());
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
		return new Response(301,['Location' => $this->getAutorizationAdress()],'<a href="' . $this->getAutorizationAdress() . '" >Aller sur la page de connexion</a>');
	}

	public function redirectToDisableLoginAdress()
	{
		return new Response(301,['Location' => $this->config['refuseLoginURL']],'<a href="' . $this->config['refuseLoginURL'] . '" >Aller sur la page de connexion</a>');
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
			return $this->redirectToDisableLoginAdress();
		}

		if($this->haveToConnect())
		{
			if($info = $this->getInfosUser())
			{
				return $info;
			}
		}

		return $this->redirectToLoginAdress();
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
			throw new HTTPException('impossible de se connecter',500);
		}
	}

	public function userinfo()
	{
		if(($data = $this->getUserinfoRequest()) != null)
		{
			return $data;
		}

		$this->redirectToLoginAdress();
		throw new HTTPException('impossible de récupérer les informations du compte.',500);
	}
}
<?php
namespace Core\Classes\OAuth;

class Google extends OAuth
{
	protected $configFile = CONFIG . 'googleOAuth.json';

	protected function getFieldId($data) {return $data->sub;}
	protected function getFieldFamilyName($data) {return $data->family_name;}
	protected function getFieldGivenName($data) {return $data->given_name;}
	protected function getFieldEmail($data) {return $data->email;}
	protected function getFieldPicture($data) {return $data->picture;}

	public function getAutorizationAdress()
	{
		return 'https://accounts.google.com/o/oauth2/v2/auth?'.
 				'scope=email profile&'.
  				'access_type=online&'.
  				'redirect_uri=' . urlencode($this->config['redirectURL']) . '&'.
  				'prompt=consent&'.
				'response_type=code&'.
  				'client_id=' . $this->config['client_id'];
	}

	public function haveToConnect()
	{
		return isset($_GET['code']);
	}

	public function hasRefuseConnect()
	{
		return false;
	}

	public function getAuthenticateRequest()
	{
		$req = new \HTTPRequest('https://www.googleapis.com/oauth2/v4/token');
		$req->set_form_data([
			'code' => $_GET['code'],
			'client_id' => $this->config['client_id'],
			'client_secret' => $this->config['secret'],
			'redirect_uri' => $this->config['redirectURL'],
			'grant_type' => 'authorization_code'
		]);
		return $req->post();
	}

	public function getUserinfoRequest()
	{
		$req = new \HTTPRequest('https://openidconnect.googleapis.com/v1/userinfo');
		$req->add_header('Authorization: Bearer ' . $this->auth->access_token);
		$val = $req->get();
		//\HTML::print_r($val);
		if($val->code == 200)
		{
			if($val->data->email_verified != 1)
			{
				\HTTP::error_page('500','impossible de récupérer les informations du compte.');
				return null;
			}

			return $val->data;
		}
		return null;
	}
}
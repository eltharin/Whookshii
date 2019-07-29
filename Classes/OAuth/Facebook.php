<?php
namespace Core\Classes\OAuth;

class Facebook extends OAuth
{
	protected $configFile = CONFIG . 'facebookOAuth.json';

	protected function getFieldId($data) {return $data->id;}
	protected function getFieldFamilyName($data) {return $data->last_name;}
	protected function getFieldGivenName($data) {return $data->first_name;}
	protected function getFieldEmail($data) {return $data->email;}
	protected function getFieldPicture($data) {return $data->picture->data->url;}


	public function getAutorizationAdress()
	{
		return 'https://www.facebook.com/v3.3/dialog/oauth?'.
 				'scope=email&'.
  				'redirect_uri=' . urlencode($this->config['redirectURL']) . '&'.
  				//'prompt=consent&'.
				'state=coucou&'.
  				'client_id=' . $this->config['client_id'];
	}

	public function haveToConnect()
	{
		return isset($_GET['code']);
	}

	public function hasRefuseConnect()
	{
		return isset($_GET['error']) && $_GET['error'] == 'access_denied' && isset($_GET['error_reason']) && $_GET['error_reason'] == 'user_denied';
	}

	public function getAuthenticateRequest()
	{
		$req = new \HTTPRequest('https://graph.facebook.com/v3.3/oauth/access_token');
		$req->set_form_data([
			'code' => $_GET['code'],
			'client_id' => $this->config['client_id'],
			'client_secret' => $this->config['secret'],
			'redirect_uri' => $this->config['redirectURL']
		]);

		return $req->post();
	}

	public function getUserinfoRequest()
	{
		$req = new \HTTPRequest('https://graph.facebook.com/v3.3/me');
		$req->add_header('Authorization: Bearer ' . $this->auth->access_token);
		$req->set_form_data([
			'fields' => 'id,first_name,last_name,middle_name,name_format,picture,short_name,name,email'
		]);
		$val = $req->post();
		if($val->code == 200)
		{
			return json_decode($val->data)??$val->data;
		}
		return null;
	}
}
<?php
namespace Core\App\Config;

class Response extends AbstractConfigElement
{
	private $response;
	
	public function __construct()
	{
		$this->response = new \GuzzleHttp\Psr7\Response(200,[],'');
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	public function addHeader($header,$value)
	{
		$this->response = $this->response->withHeader($header,$value);
	}
	
	public function setCode($code)
	{
		$this->response = $this->response->withStatus($code);
	}
	
	public function setBody($body)
	{
		$this->response = $this->response->withBody(\GuzzleHttp\Psr7\stream_for($body));
	}
}
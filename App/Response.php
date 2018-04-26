<?php

namespace Core\App;

class Response
{
	private $body = '';
	private $message = '';
	private $content_type = '';
	private $protocol = 'HTTP/1.1';
	private $code = 200;
	private $headers = [];
	private $exceptions = [];

	public function start_cache()
	{
		//ob_start();
	}

	public function stop_cache()
	{

	}

	public function render()
	{
		header($this->protocol . " ".$this->code." " . ($this->message!=''?$this->message:\HTTP::$http_status_codes[$this->code]));
		
		foreach($this->headers as $header)
		{
			header($header);
		}
		echo $this->body;
	}

	public function get_content_type()
	{
		return $this->content_type;
	}

	public function set_content_type(String $content_type)
	{
		$this->content_type = $content_type;
	}
	
	public function get_body()
	{
		return $this->body;
	}

	public function set_body(String $body,String $contenttype='')
	{
		if($contenttype !== null)
		{
			$this->set_content_type($contenttype);
		}
		$this->body = $body;
	}

	public function set_body_json($data)
	{
		$this->set_body(($data),'application/json');
	}
	
	public function get_code()
	{
		return $this->code;
	}

	public function set_code(Int $code)
	{
		$this->code = $code;
	}

	public function get_message()
	{
		return $this->message;
	}

	public function set_message(String $message)
	{
		$this->message = $message;
	}

	public function get_headers()
	{
		return $this->headers;
	}

	public function add_header(String $header)
	{
		$this->headers[] = $header;
	}

	public function get_exceptions()
	{
		return $this->exceptions;
	}

	public function add_exception(\Exception $exception)
	{
		$this->exceptions[] = $exception;
	}
	
	public function clear_cache()
	{
		ob_end_clean();
		ob_start();
	}

}

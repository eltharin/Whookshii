<?php
namespace Core\App\Mvc\ServicesQR;


class QRError
{
	private $code = '';
	private $message = '';
	private $adds = [];

	public function __construct($err, $code, $adds = [])
	{
		$this->message = $err;
		$this->code = $code;
		$this->adds = $adds;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getAdds($key = null)
	{
		if($key !== null)
		{
			return $this->adds[$key];
		}
		return $this->adds;
	}
}
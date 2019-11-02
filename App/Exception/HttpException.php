<?php
namespace Core\App\Exception;

use Throwable;

class HttpException extends \Exception
{
	private $pageContent = '';

	public function __construct($message = "", $code = 0, Throwable $previous = null,string $pageContent = '')
	{
		$this->pageContent = $pageContent;
		parent::__construct($message, $code, $previous);
	}

	public function getPageContent() : string
	{
		return $this->pageContent;
	}
}
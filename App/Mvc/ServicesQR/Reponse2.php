<?php

namespace Core\App\Mvc\ServicesQR;

class Reponse2
{
	protected $messages = [];
	protected $warnings = [];
	protected $errors   = [];

	public function __construct($data = null)
	{
		if($data !== null)
		{
			$this->messages = $data['messages'];
			$this->warnings = $data['warnings'];
			$this->errors   = $data['errors'];
		}
	}

	public function hasErrors()
	{
		return !empty($this->errors);
	}

	public function addMessage($message)
	{
		$this->messages[] = $message;
	}

	public function addWarning($warning)
	{
		$this->warnings[] = $warning;
	}

	public function addError($error)
	{
		$this->errors[] = $error;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getWarnings()
	{
		return $this->warnings;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function fusionne(Reponse2 $rep)
	{
		$this->messages = array_merge($this->messages, $rep->getMessages());
		$this->warnings = array_merge($this->warnings, $rep->getWarnings());
		$this->errors = array_merge($this->errors, $rep->getErrors());
	}
}
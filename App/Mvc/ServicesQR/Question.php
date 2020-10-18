<?php


namespace Core\App\Mvc\ServicesQR;


class Question
{
	public function __construct($data = [])
	{
		foreach($data as $k => $v)
		{
			$this->$k = $v;
		}
	}
}
<?php


namespace Core\App\Mvc;


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
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

	public function checkParams(array $params)
	{
		$paramsNeeded = [];

		foreach($params as $p)
		{
			if(!property_exists($this, $p))
			{
				$paramsNeeded[] = $p;
			}
		}

		return $paramsNeeded;
	}
}
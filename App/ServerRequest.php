<?php

namespace Core\App;

class ServerRequest extends \GuzzleHttp\Psr7\ServerRequest
{
	public static function fromGlobals()
	{
		$request = parent::fromGlobals();

		$bodyContent = $request->getBody()->getContents();
		$request->getBody()->rewind();
		if($bodyContent != '')
		{
			$body = self::decodeBody($bodyContent, $request->getHeaderLine('Content-Type'));
			$request = $request->withQueryParams(array_merge($request->getQueryParams(),$body));
			if($request->getMethod() == 'POST')
			{
				$_POST = array_merge($body,$_POST);
			}
		}

		return $request;
	}

	protected static function decodeBody($body, $contentType)
	{
		if($contentType == 'application/json')
		{
			return json_decode($body,true);
		}

		return [];
	}
}
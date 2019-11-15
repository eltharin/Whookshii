<?php

namespace Core\App;


use Core\App\Exception\HttpException;
use Core\App\Exception\RenderResponseException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Http
 * @package Core\App
 */
class Http
{
	private static $doNotRedirect = false;
	/**
	 * @var array status HTTP par défaut
	 */
	public static $httpStatusCodes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Internal Server Error", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");


	public static function redirect($page=null)
	{
		if ($page === null)
		{
			if ((isset($_SESSION['_page_to_redirect'])) && ($_SESSION['_page_to_redirect'] != ''))
			{
				$page = $_SESSION['_page_to_redirect'];
				unset($_SESSION['_page_to_redirect']);
			}
			elseif ((isset($_SERVER['HTTP_REFERER'])) && ($_SERVER['HTTP_REFERER'] != ''))
			{
				$page = $_SERVER['HTTP_REFERER'];
			}
			else
			{
				$page = '/';
			}
		}
		
		if(!\Config::get('Vars')->getConfig('modeAjax'))
		{
			if (($_SESSION['debug_mode'] === true) || (self::$doNotRedirect == true))
			{
				echo BRN;
				echo \HTML::link($page,'redirection');
			}
			else
			{
				\Config::get('Response')->addHeader('Status','301 Moved Permanently', true, 301);

				if(substr($page,0,4) == 'http')
				{
					\Config::get('Response')->addHeader('Location', $page);
				}
				else
				{
					\Config::get('Response')->addHeader('Location', BASE_URL . $page);
				}
				\Config::get('Response')->setCode(301);
			}
			throw new RenderResponseException();	
		}	
	}
	
	public static function setReferer($var=null)
	{
		if ($var !== null)
		{
			$_SESSION['_page_to_redirect'] = $var;
		}
		else
		{
			if ((isset($_SERVER['HTTP_REFERER'])) && ($_SERVER['HTTP_REFERER'] != ''))
			{
				$_SESSION['_page_to_redirect'] = $_SERVER['HTTP_REFERER'];
			}
			else
			{
				$_SESSION['_page_to_redirect'] = $_SERVER["PATH_INFO"];
			}
		}
	}
	
	public static function stopRedirect()
	{
		self::$doNotRedirect = true;
	}
	
	public static function errorPage($errno, $message = null)
	{
		$val = array_slice(debug_backtrace(),0,2);
		unset($val[1]['object']);
		throw new HttpException($message,$errno,null,json_encode($val));
	}

	public static function showFile(string $file)
	{
		
	}
	
	/**
	 * Affiche un fichier local
	 * @param string $file chemin du fichier à afficher
	 * @return ResponseInterface
	 */
	public static function getResponseWithFile(string $file) : ResponseInterface
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		$type = self::getMimeType($ext);

		$text = file_get_contents($file);
		if (substr($text, 0, 2) == chr(0xFF) . chr(0xFE))
		{
			$text = iconv('UTF-16LE', 'UTF-8', $text);
		}

		return (new Response(200,[],$text))
					->withHeader('Content-type',$type)
					->withHeader('Cache-Control','public, max-age=31536000')
					->withHeader('Expires','Thu, 19 Nov 2018 08:52:00 GMT')
					->withHeader('Pragma','public');
	}

	/**
	 * Retourne le mime type en fonction de l'extension passée
	 * @param string $param Extension
	 * @return string mime-type
	 */
	public static function getMimeType(string $param) : string
	{
		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'text/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		if (array_key_exists($param,$mime_types))
		{
			return $mime_types[$param];
		}
		return "application/octet-stream";
	}
	
	public static function forceDownload($filename)
	{
		if(file_exists($filename))
		{
			$result = new \finfo();
			$type = $result->file($filename, FILEINFO_MIME_TYPE);
			self::downloadHeader(basename($filename),$type,filesize($filename));
			readfile($filename);
		}
	}

	public static function downloadHeader($name,$type='application/octet-stream',$size=0)
	{
		\Config::get('HTMLTemplate')->setNoTemplate(true);
		ob_clean();
		//-- on met les header
		\Config::get('Response')->addHeader('Content-disposition','attachment; filename="' . $name . '"');
		\Config::get('Response')->addHeader('Content-type','application/force-download');
		\Config::get('Response')->addHeader('Content-Transfer-Encoding',$type . "\n");
		
		if ($size > 0)
		{
			\Config::get('Response')->addHeader('Content-Length',$size);
		}	
	}
	
	public static function getUrl()
	{
		if (!isset($_SERVER["REQUEST_SCHEME"]))
		{
			$_SERVER["REQUEST_SCHEME"] = 'https';
		}
		return $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"] . BASE_URL . '/';
	}
}

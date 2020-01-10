<?php
namespace Core\App\Exception;

use Throwable;

class HttpException extends \Exception
{
	private $pageContent = '';

	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		$content = ob_get_clean();

		$message = '<h2>Erreur ' . $code . ' - ' . $message . '</h2>' . BRN . $content;;

		if($_SESSION['debug_mode']??false)
		{
			foreach($this->getTrace() as $trace)
			{
				if(mb_substr($trace['file']??'',-32) == 'Core\\App\\Middleware\\Launcher.php')
				{
					break;
				}

				$message .= '<div style="margin-bottom:10px;border:1px solid black;">' .
								(isset($trace['file'])?'Fichier : ' . mb_substr($trace['file'],strlen(ROOT)) . BRN:'') .
								(isset($trace['line'])?'Ligne : ' .  $trace['line'] . BRN:'') .
								(isset($trace['function'])?'Fonction : ' .  $trace['function'] . BRN:'') .
								(isset($trace['class'])?'Class : ' .  $trace['class'] . BRN:'') .
								((isset($trace['args']) && count($trace['args'])>0)?'args : ' . $this->array2HtmlList($trace['args']):'') .
							'</div>';
			}
		}
		parent::__construct($message, $code, $previous);
	}

	public function errorMessage()
	{
		return 'erreur';
	}

	public function array2HtmlList($array) : string
	{
		$ret = '<ul>';

		foreach($array as $item)
		{
			$ret .= '<li>';
			$ret .= (is_array($item) ? '<ul>' . $this->array2HtmlList($item) . '</ul>' : (is_string($item) ? $item : print_r($item,true)));
			$ret .= '</li>';
		}

		$ret .= '</ul>';
		return $ret;
	}
}
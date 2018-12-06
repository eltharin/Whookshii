<?php
namespace Core\Classes;

trait tracer
{
	function traceMail($title,$msg)
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0];
		$origine = 'File : ' . $backtrace['file'] . '<br>Line : ' . $backtrace['line'] . BR . $msg;
		$mail = new \Plugin\Intranet\classes\mailer();
		$mail->setFrom('Core@intranet.fr');
		$mail->addAddress('dev@cogep.fr');
		$mail->Subject = $title;
		$mail->msgHTML($origine);
		$mail->send();
	}
}
?>

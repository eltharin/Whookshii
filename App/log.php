<?php
namespace Core\App;


class log
{
	public static function save($str)
	{
		file_put_contents(TEMP.'log.log',$str.RN,FILE_APPEND);
	}
}
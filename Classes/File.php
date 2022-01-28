<?php
namespace Core\Classes;

class File
{

	static function glob_rec($folder)
	{
		return glob($folder . "/{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/,*/*/*/*/*/*/,*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/*/*/}{*.*,*}", GLOB_BRACE | GLOB_NOSORT | GLOB_MARK);
	}

	static function rrmdir($folder)
	{
		$handle = opendir($folder);
		while (false !== ($entry = readdir($handle)))
		{
			if ($entry != '.' && $entry != '..')
			{
				if (is_dir($folder . '/' . $entry))
				{
					self::rrmdir($folder . '/' . $entry);
				}
				elseif (is_file($folder . '/' . $entry))
				{
					unlink($folder . '/' . $entry);
				}
			}
		}
		rmdir($folder);
		closedir($handle);
	}

	static function createFolder($path)
	{
		if(file_exists($path))
		{
			return true;
		}

		if(!file_exists(dirname($path)))
		{
			self::createFolder(dirname($path));
		}

		echo 'create ' . $path . BRN;
		mkdir($path);
	}
}


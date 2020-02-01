<?php
namespace core\classes;

class file
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

}
?>


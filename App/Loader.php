<?php

namespace Core\App;

class Loader
{
	private static $classes_loaded = [];
	/*
	public static function fullNamed($str)
	{
		$tabStr = explode('\\',trim($str,'\\'));
		
		if(in_array(strtolower($tabStr[0]),['core','specs','plugin']))
		{
			
			if(file_exists(ROOT . $str.'.php'))
			{
				require ROOT.$str.'.php';
				return true;
			}
		}
		return false;
	}


	public static function ClassAutoload(String $str)
	{
		$cls = self::Load('Classes',$str,true);
	}

*/
	/**
	 * charge un fichier externe présent dans Specs/Files ou Core/Files
	 * @param string $str
	 * @return string|null
	 */
	public static function file(string $str) : ?string
	{
		if($file = self::SearchFile($str,'','Files',true))
		{
			return $file['file'];
		}
		return null;
	}

	/**
	 * charge un fichier externe présent dans Specs/Files ou Core/Files
	 * @param string $str
	 * @return string|null
	 */
	public static function fileVendor(string $str) : ?string
	{
		if(substr($str,0,7) == 'vendor/')
		{
			if(file_exists(ROOT . $str))
			{
				return ROOT . $str;
			}
		}
		return null;
	}

	/**
	 * Cherche le fichier spécifié dans les différents dossiers de l'application
	 * @param String $str
	 * @param String $ext
	 * @param        $subFolder
	 * @param bool   $with_plugin
	 * @return array|null
	 */
	public static function SearchFile(String $str,String $ext,$subFolder,bool $with_plugin = false)
	{
    	$str = str_replace('\\','/',$str);
    
		foreach(['Specs','Core'] as $folder)
		{
			if(file_exists(ROOT . $folder . DS . $subFolder . DS .$str . $ext))
			{
				return ['file' => ROOT . $folder . DS . $subFolder . DS .$str . $ext,
						'name' => str_replace('/','\\',$folder . '\\' . $subFolder . '\\' .$str),
						'finalname' => $str,
					];
			}
		}

		
		if($with_plugin)
		{
			$str = explode('/',$str);
			$namespace = array_shift($str);
			
			if(file_exists(ROOT . 'plugin' . DS . $namespace . DS . $subFolder . DS . implode(DS,$str).$ext))
			{
         		return ['file' => ROOT . 'plugin' . DS . $namespace . DS . $subFolder . DS . implode(DS,$str).$ext,
						'name' => 'plugin\\' . $namespace . '\\' . $subFolder . '\\' . implode(DS,$str),
						'finalname' => $namespace . '\\' . implode(DS,$str),
				];
			}
        	elseif(file_exists(ROOT . 'Plugin' . DS . ucfirst($namespace) . DS . $subFolder . DS . implode(DS,$str).$ext))
			{
         		return ['file' => ROOT . 'Plugin' . DS . ucfirst($namespace) . DS . $subFolder . DS . implode(DS,$str).$ext,
						'name' => 'plugin\\' . ucfirst($namespace) . '\\' . $subFolder . '\\' . implode(DS,$str),
						'finalname' => $namespace . '\\' . implode(DS,$str),
				];
			}
		}
		return null;
	}


	public static function Load(string $cat, string $classe,$alias = false)
	{
		$classe = str_replace('/','\\',$classe);

		if(isset(self::$classes_loaded[$cat][$classe]))
		{
			return self::$classes_loaded[$cat][$classe];
		}

		if($file = self::SearchFile($classe,'.php',$cat,true))
		{
			require_once $file['file'];
			if($alias)
			{
				if(!class_exists($classe) && !trait_exists($classe) && !interface_exists($classe))
				{
					class_alias(str_replace('/','\\',$file['name']),$classe);
				}
			}
			$file['f2'] = str_replace('\\','/',$classe);
			self::$classes_loaded[$cat][$classe] = $file;
			return $file;
		}
	}

	/*
	public static function load_model(string $model)
	{
		return self::Load('Models',$model);
	}*/

}
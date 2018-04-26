<?php
namespace core\classes;

class error
{
	var $type_erreur = array(	'e1' => 'ERROR',
								'f1' => 'FATAL_ERROR',
								'c1' => 'ERREUR_PAS_GRAVE',
							);
	function __construct()
	{
		foreach ($this->type_erreur as $k => $v)
		{
			define($v,$k);
		}
	}
	
	function get_type($type)
	{
		return $this->type_erreur[$type];
	}
	
	function is_fatal($type)
	{
		return (substr($type,0,1) == 'f');
	}
    
    static function set($type,$message)
    {
        
    }

}
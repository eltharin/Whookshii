<?php
namespace core\classes;

class ftp
{
	private $conn_id;
	private $connected;
	
	private $srv;
	private $user;
	private $pass;
	private $folder;
	private $option;
	
	function __construct($srv,$user,$pass,$folder='.',$option='-dF')
	{
		$this->srv = $srv;
		$this->user = $user;
		$this->pass = $pass;
		$this->folder = $folder;
		$this->option = $option;
	
		$this->connect();
	}
	
	function connect()
	{
		$this->conn_id = ftp_connect($this->srv);
		
		$login_result = @ftp_login($this->conn_id, $this->user, $this->pass);
		ftp_chdir($this->conn_id, $this->folder);
		
		if ($login_result)
		{
			$this->connected = 1;
		}
		else
		{
			$this->connected = 0;
		}
		
	}
	
	function download($source,$dest,$mode=FTP_ASCII)
	{
		set_time_limit(-1);
		return ftp_get($this->conn_id, $dest, $source, $mode);
 
	}
	
	function list_folder($folder='.',$opt = null)
	{
		if($opt === null)
		{
			$opt = $this->option;
		}
		
		if($opt != '')
		{
			$opt = $opt . ' ';
		}
		
		return ftp_nlist($this->conn_id, $opt . $folder);
	}
	
	function get_all_files($folder='.')
	{
		set_time_limit(-1);
		$retour = array();
		
		$collection = $this->list_folder($folder);
		foreach($collection as $item)
		{
			//if (ftp_size($this->conn_id, $item) == -1)
			if (substr($item,-1) == '/')
			{
				$retour = array_merge($retour,$this->get_all_files($item));
			}
			else
			{
				$retour[] = $item;
			}
		}
		return $retour;
	}
	
	function upload($source,$dest)
	{
		//ftp_chdir($this->conn_id, dirname($dest));
		
		if(substr($source,-1) == '\\')
		{
			ftp_chdir($this->conn_id, dirname($dest));
			@ftp_mkdir($this->conn_id,basename($dest));
			//echo 'création du dossier ' . $dest . BRN;
		}
		else
		{			
			ftp_put($this->conn_id, $dest , $source, FTP_BINARY);
			//echo 'création du fichier ' . $source . BRN;
		}
	}
	
	function uploadfolder($source,$dest)
	{
		ftp_chdir($this->conn_id, dirname($dest));
		
		if(!is_dir('ftp://'.$this->user.':'.$this->pass.'@'.$this->srv.'/'.basename($dest)))
		{
			ftp_mkdir($this->conn_id,basename($dest));
		}
		
		foreach (glob($source . "/{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/,*/*/*/*/*/*/,*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/*/*/}{*.*,*}",GLOB_BRACE|GLOB_NOSORT|GLOB_MARK) as $filename)
		{			
			$this->upload($filename,$dest.'/'.str_replace($source,'',$filename));
		}
	}
	
	function disconnect()
	{
		ftp_close($this->conn_id);
	}
	
	function delete($ource)
	{
		ftp_delete($this->conn_id, $ource);
	}

	function rename($oldname,$newname)
	{
		ftp_rename ( $this->conn_id , $oldname , $newname );
	}
	
	function mkdir($dir)
	{
		ftp_mkdir ($this->conn_id ,$dir );
	}

	function rmdir($dir)
	{
		ftp_rmdir ($this->conn_id ,$dir );
	}
}

?>


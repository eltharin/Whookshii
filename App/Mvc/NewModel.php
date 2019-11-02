<?php
namespace Core\App\Mvc;

use \PDO;

class NewModel
{
	protected $db = null;
	protected $provider = '';

	protected $entity = '';
	protected $uid = '';
	protected $table = '';

	public function __construct()
	{
		$this->uid = $this->table;

		if($this->provider == '')
		{
			$this->db = \Config::get('Providers')->getDefault();
		}
		else
		{
			$this->db = \Config::get('Providers')->getConfig($this->provider);
		}

		/*if($this->entity != '')
		{
			$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		}
		else
		{
			$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}*/
	}

	public function getField($field)
	{
		return $this->uid . '.' . $field;
	}

	public function getTable()
	{
		return $this->table . ' as ' . $this->uid;
	}

	public function getAll($select='*')
	{
		$req = $this->db->select($this->getField($select))->from($this->getTable());
        //$this->add_request_for_select($req);
        //$this->_add_filter($req);

		echo $this->entity;
		if($this->entity != '')
		{
			return $req->get(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE,$this->entity);
		}
        return $req->get();
	}
}
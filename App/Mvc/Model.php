<?php
namespace Core\App\Mvc;

use Core\Classes\form;

class Model extends Model_Nonbdd
{
	public $primaryKey;
	public $db = null;
	public $table = null;
	public $uid = '';
	public $libelle = '';
	public $relations = [];
	public $allowInsert = 1;
	public $allowUpdate = 1;
	public $allowDelete = 1;
	protected $forecReplaceInto = false;
	public $valuesNotPrepared = [];

	final function __init_model($uid=null)
	{
		if ($this->db === null)
		{
			$this->db = \Config::get('Providers')->getDefault();
        }
        //-- si on a pas renseigné de table
        if ($this->table === null)
        {
            $this->table =  explode('\\',get_class($this));
            $this->table =  strtolower(end($this->table));
        }

        //-- si on a pas renseigné de cle ou libelle, on les cherche
        if ($this->primaryKey == null)
        {
            foreach ($this->fields as $field => $data)
            {
                //-- la cle primaire dans mysql
                if ($data['key'] == 'PRI')
                {
                    $this->primaryKey = $field;
                }
                //-- si le champ est un libelle
                elseif ((substr($field, -7, 7) == 'LIBELLE') || ($data['key'] == 'LIBELLE'))
                {
                    if ($this->libelle == null)
                    {
                        $this->libelle = $field;
                    }
                }
            }
        }
        $this->set_uid($uid??$this->uid?:str_replace('.','_',$this->table));
    }

    function set_uid($uid)
    {
        $this->uid = str_replace('\/','_',$uid);
    }

	function get_db()
	{
		return $this->db;
	}

	function set_db($db)
	{
		$this->db = $db;
	}

	function _init()
	{
		parent::_init();
		$this->__init_model();
	}


    function add_relation($param)
    {
        $param = array_merge(array('name' => '',
            'uid' => md5(mt_rand()),
            'table' => '',
            'Rel_id' => '',
            'Rel_FK' => '',
            'add_cond'=>'',
            'alias'=>'',
            'blockfollow' => false,
            'follow' => false,
            'notfollow' => array(),
            'champs' => '*',
            'inner'=>true) , $param);
        if ($param['name'] == '')
        {
            $param['name'] = $param['table'];
        }

        if ($param['alias'] == '')
        {
            $param['alias'] = preg_replace('#[^[:alnum:]-]#','',$param['table']);
        }

		if ($param['uid'] == '')
        {
            $param['uid'] = preg_replace('#[^[:alnum:]-]#','',$param['table']);
        }

        $this->relations[strtolower($param['alias'])][] = $param;

    }

    public function create_empty($val = array())
	{
		return $this->createEmpty($val);
	}

    public function createEmpty($val = array())
    {
        $values = parent::create_empty($val);

		if($this->primaryKey)
		{
			$values[$this->primaryKey] = null;
		}

        return $values;
    }

    /**
     * retourne les elements de la table
     * @param type $arg
     * @return array les lignes
     */

    function get_requete($select = '*')
    {
        $req = $this->db->select($this->get_champ($select))->from($this->get_table());
        $this->add_request_for_select($req);
        $this->_add_filter($req);
        return $req;
    }

    function get_requete_with_rel($select = '*')
    {
        $req = $this->get_requete($this->get_champ($select));
        $req = $this->get_relations($req,array(),false,$select==='*');
        return $req;
    }

    function add_request_for_select($requester)	{}

    function _add_filter($requester) {}

    function add_request($requester) {}

    function add_request_for_update($requester)	{}

    function add_request_for_delete($requester)	{}

    function find($where = null,$params=array(), $order=null, $mode = \PDO::FETCH_ASSOC)
    {
        $req = $this->get_requete();
        $req = $this->get_relations($req);

        if ($where !== null)
        {
            $req->where($where,$params);
        }

        if($order !== null)
        {
            $req->order($order);
        }

        return $req->get($mode);
    }

    function findFirst($where = null,$params=array(),$mode = \PDO::FETCH_ASSOC)
    {
        $req = $this->get_requete();
        $req = $this->get_relations($req);

        if ($where !== null)
        {
            $req->where($where,$params);
        }

        return $req->findFirst($mode);
    }

    function get($id,$mode = \PDO::FETCH_ASSOC)
    {
        $req = $this->get_requete();
        $req = $this->get_relations($req);
        $this->add_request($req);
        $this->_add_filter($req);
        if(!is_array($id))
        {
            $req->where($this->get_champ($this->primaryKey) . '=:id',array('id'=>$id));
        }
        else
        {
            foreach($id as $k=>$v)
            {
                $req->where($k . '=:'.$k,array($k=>$v));
            }
        }

        return $req->findFirst($mode);
    }

    function get_relations($requester,$notfollow=array(),$blockfollow=false)
    {
        if(!$blockfollow)
        {
            foreach ($this->relations as $alias => $tabrel)
			{
				foreach($tabrel as $num => $rel)
				{
					$table = $rel['table'];
					if (!in_array($rel['name'],$notfollow))
					{
						$model = $this->LoadModel($table);
						$model->set_uid($this->uid . '_' . $alias . ($num!=0?$num:''));

						$fct = ($rel['inner'] == true?'ijoin':'ljoin');
						$requester->$fct($model->get_table(), $this->get_champ($rel['Rel_FK']) . ' = ' . $model->get_champ($rel['Rel_id'] . ($rel['add_cond'] != ''?' AND ' . $rel['add_cond']:'')));

						foreach(explode(',',$rel['champs']) as $champ)
						{
							$requester->select($model->get_champ(ltrim($champ)));
						}

						if ($rel['follow'] === true)
						{
							$requester = $model->get_relations($requester,$rel['notfollow'],$rel['blockfollow']);
						}
					}
				}
			}
        }
        return $requester;
    }


    function getPK()
    {
        return $this->values[$this->primaryKey];
    }

    function save($values=null,$params=[])
    {
        if (($values !== null) && (is_array($values)))
        {
            $this->set_values($values);
        }

        foreach (array_keys($this->values) as $key)
        {
            if (!in_array($key, array_keys($this->fields)))
            {
                unset($this->values[$key]);
            }
        }

		if($this->forecReplaceInto == true)
		{
			if(!$this->allowInsert || !$this->allowUpdate)
            {
                \HTTP::errorPage('500','Action incorrecte');
            }
            if (!$this->validates(false))
			{
				\form::set_old_values(array_merge($this->vars,$this->values));
				return false;
			}
			return $this->DBReplace();
		}
		elseif (!empty($this->values[$this->primaryKey]))
		{
            if(!$this->allowUpdate)
            {
                \HTTP::errorPage('500','Vous ne pouvez pas mettre à jour cette entrée');
            }
            if (!$this->validates(false))
            {
                form::set_old_values(array_merge($this->vars,$this->values));
                return false;
            }
            return $this->DBUpdate($params);
        }
        else
        {
            if(!$this->allowInsert)
            {
                \HTTP::errorPage('500','Vous ne pouvez pas ajouter cette entrée');
            }
            if (!$this->validates(true))
            {
                form::set_old_values(array_merge($this->vars,$this->values));
                return false;
            }
            return $this->DBInsert($params);
        }

    }

    function delete($arg=null,$csrf=0)
    {
        if(!$this->allowDelete)
        {
            \HTTP::errorPage('500','Vous ne pouvez pas supprimer cette entrée');
        }

        if($csrf == 1)
        {

        }

        if ($arg === null)
        {
            return false;
        }

        if (is_array($arg))
        {
            $this->set_values($arg);
        }
        else
        {
            $this->set_values(array($this->primaryKey => $arg));
        }
        return $this->DBDelete();
    }

    /**
     * fonction permettant de retourner un champ pour ce model
     * @param string/array $champ champ(s) a retourner
     * @return string liste des champs
     */

    function get_champ($champ)
    {
        if($champ == '*')
        {
            return $this->uid . '.*';
        }

        if (is_array($champ))
        {
            foreach ($champ as $k => $v)
            {
                if(isset($this->fields[$k]) || $k == '*')
                {
                    $ret[] = $this->uid . '.' . $k . ' as ' . $v;
                }
                else
                {
                    $ret[] = $k . ' as ' . $v;
                }
            }
        }
        else
        {
            $champ = array_map('trim',explode(',',$champ));

            foreach ($champ as $k => $v)
            {
				if(isset($this->fields[$v]) || $v == '*' || isset($this->fields[(explode(' ',$v))[0]]))
                {
                    $ret[] = $this->uid . '.' . $v;
                }
                else
                {
                    $ret[] = $v;
                }
            }
        }
        return implode(', ', $ret);
    }

    /**
     * retourne le nom de la table du model avec un identifiant
     * @return string nom de la table
     */

    function get_table($withid = true)
    {
        if ($withid)
            return $this->table . ' as ' . $this->uid;
        else
            return $this->table;
    }

    protected function _BeforeAll()	{ return $this->BeforeAll();}
    protected function BeforeAll()	{ return true;}

    protected function _AfterAll()	{ $this->AfterAll();}
    protected function AfterAll()	{}

    protected function _BeforeInsert()	{ return $this->BeforeInsert();}
    protected function BeforeInsert()	{ return true;}


    protected function DBInsert($params=array())
    {
        if(!is_array($params)){$params = array($params);}

		//$values = $this->values;
		if(!in_array('forcekey',$params))
		{
			unset($this->values[$this->primaryKey]);
		}
		if ($this->_BeforeAll() && $this->_BeforeInsert())
		{
			$this->values[$this->primaryKey] = $this->db->insert($this->table, $this->values,$params,$this->valuesNotPrepared);
			if ($this->values[$this->primaryKey] !== null)
			{
				//$this->values[$this->primaryKey] = $this->values[$this->primaryKey];
				$this->_AfterAll();
				$this->_AfterInsert();
				return $this->values[$this->primaryKey] ;
			}
			else
			{
				$this->_AfterBadInsert();
				return false;
			}
		}
		else
		{
			return false;
		}
	}

    protected function _AfterInsert()	{ $this->AfterInsert();}
    protected function AfterInsert()	{}

    protected function _AfterBadInsert()	{ $this->AfterBadInsert();}
    protected function AfterBadInsert()	{}

    protected function _BeforeUpdate()	{ return $this->BeforeUpdate();}
    protected function BeforeUpdate()	{ return true;}


    protected function DBUpdate()
    {
        if ($this->_BeforeAll() && $this->_BeforeUpdate())
        {
            $val = $this->values;
            unset($val[$this->primaryKey]);
            $param = array($this->primaryKey => $this->values[$this->primaryKey]);

            $req = $this->db->update($this->table);
            $req->set($val);
            $req->where('',$param);
            $this->_add_filter($req);
            $this->add_request_for_update($req);
            $ret = $req->exec();

            $this->_AfterAll();
            $this->_AfterUpdate();
            return $ret;
        }
        return null;
    }

    protected function _AfterUpdate()	{$this->AfterUpdate();}
    protected function AfterUpdate()	{}

	protected function _BeforeReplace()	{ return $this->BeforeReplace();}
	protected function BeforeReplace()	{ return true;}


	protected function DBReplace()
	{
		if ($this->_BeforeAll() && $this->_BeforeReplace())
		{
			$ret = $this->values[$this->primaryKey] = $this->db->replace($this->table, $this->values);
			return $ret;
		}
		return null;
	}

	protected function _AfterReplace()	{$this->AfterReplace();}
	protected function AfterReplace()	{}

	protected function _BeforeDelete()	{ return $this->BeforeDelete();}
	protected function BeforeDelete()	{ return true;}

    function DBDelete()
    {
        if ($this->_BeforeDelete())
        {
            $req = $this->db->delete()->from($this->table);
            $this->add_request($req);
            $this->add_request_for_delete($req);

            foreach($this->values as $k => $v)
            { $req->where($k . ' = :' . $k,array($k=>$v)); }

            $ret = $req->exec();
            $this->_AfterDelete();
            return $ret;
        }
        return null;
    }

    protected function _AfterDelete()	{ $this->AfterDelete();}
    protected function AfterDelete()	{}

    /**
     * retourne un tableau id/libelle (FETCH::KEYPAIR)
     * @param string $where est la condition de la requête
     *
     */

    function get_select()
    {
        list($where, $params, $order) = func_get_args() + array('0'=>null,'1'=>array(),'2'=>null);

        $req = $this->get_requete_with_rel()->select($this->get_champ($this->primaryKey), true)->select($this->libelle);

        if($where !== null)
        {
            $req->where($where,$params);
        }

        if($order !== null)
        {
            $req->order($order);
        }else{
            $req->order($this->libelle);
        }

        return $req->get($mode= \PDO::FETCH_KEY_PAIR);
    }


    function get_select_data()
    {
        list($where, $params, $order) = func_get_args() + array('0'=>null,'1'=>array(),'2'=>null);

        $req = $this->get_requete_with_rel()->select($this->get_champ($this->primaryKey), true)->select($this->libelle);

        if($where !== null)
        {
            $req->where($where,$params);

        }

        if($order !== null)
        {
            $req->order($order);
        }else{
            $req->order($this->libelle);
        }

        return $req->get();
    }
}
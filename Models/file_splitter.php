<?php
namespace Core\Models;

class file_splitter extends \Core\App\Mvc\Model
{
    protected $tablePart;
    protected $filePart;

    function _init()
    {
        $this->filePart = new \stdClass();
        parent::_init();
    }

    function _AfterInsert()
    {
        $this->save_parts();
        parent::_AfterInsert();
    }

    function _AfterUpdate()
    {
        if($this->vars['file_id'] != 0)
        {
            $this->delete_parts($this->vars['file_id']);
        }
        $this->save_parts();
        parent::_AfterInsert();
    }

    function _BeforeDelete()
    {
        $this->delete_parts($this->values[$this->primaryKey]);
        parent::_BeforeDelete();
    }

    function save_parts()
    {
        foreach(str_split($this->vars['file'], $this->filePart->size) as $ordre => $part)
        {
            $this->db->query('INSERT INTO ' . $this->tablePart . ' ('.  $this->filePart->prefix . $this->filePart->id_ref .  ', ' .
                                                                        $this->filePart->prefix . $this->filePart->ordre . ', ' .
                                                                        $this->filePart->prefix . $this->filePart->data . ') 
                                                            VALUES (' . $this->getPK() . ', ' .
                                                                        $ordre . ', \'' .
                                                                        $part . '\')');
        }
    }

    function delete_parts($id)
    {
        $this->db->query('DELETE FROM ' . $this->tablePart . ' WHERE ' . $this->filePart->prefix . $this->filePart->id_ref . ' = ' . $id);
    }

    function restore_file($id)
    {
        $fileParts = $this->db->select('*')
                              ->from($this->table)
                              ->ljoin($this->tablePart, $this->primaryKey . ' = ' . $this->filePart->prefix . $this->filePart->id_ref)
                              ->where($this->primaryKey . ' = :id', ['id' => $id])
                              ->order($this->filePart->prefix . $this->filePart->ordre)
                              ->get();
        $file = "";
        foreach($fileParts as $filePart)
        {
            $file .= base64_decode($filePart[$this->filePart->prefix . $this->filePart->data]);
        }

        return $file;
    }
}
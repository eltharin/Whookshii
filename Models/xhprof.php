<?php
namespace Core\Models;

class xhprof extends \Core\App\Mvc\Model
{
    function init()
    {
        $this->db = new \DB\mysql('localhost','root','iaddfo','xhprof');
        $this->table = "xhprof";
        $this->add_field('XHP_XHPROF',          ['key' => 'PRY', 'type' => 'int']);
        $this->add_field('XHP_ACTION_CALLED',   ['type' => 'var']);
        $this->add_field('XHP_SITE',            ['type' => 'var']);
        $this->add_field('XHP_FILE',            ['type' => 'var']);
        $this->add_field('XHP_METHOD',          ['type' => 'var']);
        $this->add_field('XHP_CALL_NUMBER',     ['type' => 'var']);
        $this->add_field('XHP_CALL_WALL_TIME',  ['type' => 'var']);
    }
}
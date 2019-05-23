<?php
namespace Core\Classes;

class UploadedFile
{
    private $name     = '';
    private $type     = '';
    private $tmp_name = '';
    private $error    = '';
    private $size     = '';

    public function __construct($name,$type=null,$tmp_name=null,$error=null,$size=null)
    {
        if(is_array($name))
        {
            $this->name = $name['name'];
            $this->type = $name['type'];
            $this->tmp_name = $name['tmp_name'];
            $this->error = $name['error'];
            $this->size = $name['size'];
        }
        else
        {
            $this->name = $name;
            $this->type = $type;
            $this->tmp_name = $tmp_name;
            $this->error = $error;
            $this->size = $size;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->tmp_name;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    public function getContent()
    {
        return file_get_content($this->tmp_name);
    }

    public function move($path)
    {
        return move_uploaded_file($this->tmp_name,$path);
    }

    public function getExt()
    {
        return pathinfo($this->name)['extension'];
    }
}
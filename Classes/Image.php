<?php
namespace Core\Classes;

class Image
{
	private $image;
	private $type;

	private $typesFunctions = [
								IMAGETYPE_JPEG => ['create'=>'imagecreatefromjpeg','save'=>'imagejpeg'],
								IMAGETYPE_GIF  => ['create'=>'imagecreatefromgif' ,'save'=>'imagegif'],
								IMAGETYPE_PNG  => ['create'=>'imagecreatefrompng' ,'save'=>'imagepng'],
								];

	public function __construct($filename = null)
	{
		if($filename !== null)
		{
			$this->load($filename);
		}
	}

	public function load($filename)
	{
		if(file_exists($filename))
		{
			$info = getimagesize($filename);
			$this->type = $info[2];
			if(isset($this->typesFunctions[$this->type]))
			{
				$this->image = $this->typesFunctions[$this->type]['create']($filename);
				return $this;
			}
		}
		return null;
	}

	public function getWidth()
	{
		return imagesx($this->image);
	}

   	public function getHeight()
	{
		return imagesy($this->image);
	}

	public function output($type=IMAGETYPE_JPEG, $filename=null, $compression=null)
	{
		$this->typesFunctions[$type]['save']($this->image, $filename, $compression);
	}

	function resize($width,$height)
	{
      $new = imagecreatetruecolor($width, $height);
      imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $clone = clone $this;
      $clone->image = $new;
      return $clone;
   }

	public function scale($scale)
	{
		return $this->resize($this->getWidth() * $scale, $this->getHeight() * $scale);
	}

   	function resizeWithMaxSizes($maxWidth,$maxHeight)
	{
		$ratio = min($maxWidth / $this->getWidth(),$maxHeight / $this->getHeight());
    	return $this->scale($ratio);
	}
}
/*

class SimpleImage {



   function resizeToHeight($height) {

      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }

   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }

   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }

   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }

}*/
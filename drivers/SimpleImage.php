<?php
class SimpleImage {
   
	public $image;
	public $image_type;

	public function __construct($filename = null){
		if (!empty($filename)) {
			$this->load($filename);
		}
	}
    // load
	public function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];

		if ($this->image_type == IMAGETYPE_JPEG) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif ($this->image_type == IMAGETYPE_GIF) {
			$this->image = imagecreatefromgif($filename);
		} elseif ($this->image_type == IMAGETYPE_PNG) {
			$this->image = imagecreatefrompng($filename);
		} else {
			throw new Exception("The file you're trying to open is not supported");
		}
	}
    // save
	public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null) {
		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->image,$filename,$compression);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->image,$filename);         
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->image,$filename);
		}

		if ($permissions != null) {
			chmod($filename,$permissions);
		}
	}
    // output
	public function output($image_type=IMAGETYPE_JPEG, $quality = 80) {
		if ($image_type == IMAGETYPE_JPEG) {
			header("Content-type: image/jpeg");
			imagejpeg($this->image, null, $quality);
		} elseif ($image_type == IMAGETYPE_GIF) {
			header("Content-type: image/gif");
			imagegif($this->image);         
		} elseif ($image_type == IMAGETYPE_PNG) {
			header("Content-type: image/png");
			imagepng($this->image);
		}
	}
    // get width
	public function getWidth() {
		return imagesx($this->image);
	}
    // get height
	public function getHeight() {
		return imagesy($this->image);
	}
    // resize to height
	public function resizeToHeight($height) {
        $ratio = $height / $this->getHeight();
        $width = round($this->getWidth() * $ratio);
        $this->resize($width,$height);
	}
    // resize to width
	public function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = round($this->getHeight() * $ratio);
        $this->resize($width,$height);
	}
    // resize
	public function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		
        imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));
		
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}
    // scale
	public function scale($scale) {
		$width = round($this->getWidth() * $scale/100);
		$height = round($this->getHeight() * $scale/100);
		$this->resize($width,$height);
	}
    // max area
	public function maxarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() > $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() > $height) {
			$this->resizeToheight($height);
		}
	}
	// min area
	public function minarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() < $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() < $height) {
			$this->resizeToheight($height);
		}
	}
    // max area fill
    public function maxareafill($width, $height, $red = 0, $green = 0, $blue = 0) {
	    $this->maxarea($width, $height);
	    $new_image = imagecreatetruecolor($width, $height);
	    $color_fill = imagecolorallocate($new_image, $red, $green, $blue);
	    imagefill($new_image, 0, 0, $color_fill);
	    imagecopyresampled($new_image, $this->image, floor(($width - $this->getWidth()) / 2), floor(($height - $this->getHeight()) / 2), 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
	    $this->image = $new_image;
	}
    // cut
    public function cut($x, $y, $width, $height) {
    	$new_image = imagecreatetruecolor($width, $height);	

		imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));

		imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);

		$this->image = $new_image;
	}
    // cut from center
	public function cutFromCenter($width, $height) {
        
        if($width > $this->getWidth()) {
            $this->resizeToWidth($width);
        }
        if($height > $this->getHeight()) {
            $this->resizeToHeight($height);
        }
        
		$x = ($this->getWidth() / 2) - ($width / 2);
		$y = ($this->getHeight() / 2) - ($height / 2);
		
		return $this->cut($x, $y, $width, $height);
	}
    // crop
    public function crop($width, $height) {

        $ratio_source = $this->getWidth() / $this->getHeight();
        $ratio_dest = $width / $height;

        if ($ratio_dest < $ratio_source) {
            $this->resizeToHeight($height);
        } else {
            $this->resizeToWidth($width);
        }

        $x = ($this->getWidth() / 2) - ($width / 2);
        $y = ($this->getHeight() / 2) - ($height / 2);

        return $this->cut($x, $y, $width, $height);
    }
    // watermark
    public function watermark($right, $bottom, $watermark_url) {
        $new_image = imagecreatetruecolor($this->getWidth(), $this->getHeight());	
        
        $watermark = imagecreatefrompng($watermark_url);
        
        $x = imagesx($watermark);
        $y = imagesy($watermark);
        
        imagecopy($this->image, $watermark, imagesx($this->image) - $x - $right, imagesy($this->image) - $y - $bottom, 0, 0, imagesx($watermark), imagesy($watermark));
    }
}
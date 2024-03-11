<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Image class
*/
class Image {
	private $file;
	private $image;
	private $width;
	private $height;
	private $bits;
	private $mime;

	/**
	 * Constructor
	 *
	 * @param	string	$file
	 *
 	*/
	public function __construct($file) {
		if (!extension_loaded('imagick')) {
			exit('Error: PHP Imagick is not installed!');
		}

		if (file_exists($file)) {
			$this->file = $file;
			$this->image = new Imagick();
			$this->image->readImage($file);

			$this->width = $this->image->getImageWidth();
			$this->height = $this->image->getImageHeight();
			$this->bits = $this->image->getImageLength();
			$this->mime = $this->image->getFormat();
		} else {
			exit('Error: Could not load image ' . $file . '!');
		}
	}
	
	/**
     * 
	 * 
	 * @return	string
     */
	public function getFile() {
		return $this->file;
	}

	/**
     * 
	 * 
	 * @return	array
     */
	public function getImage() {
		return $this->image;
	}
	
	/**
     * 
	 * 
	 * @return	string
     */
	public function getWidth() {
		return $this->width;
	}
	
	/**
     * 
	 * 
	 * @return	string
     */
	public function getHeight() {
		return $this->height;
	}
	
	/**
     * 
	 * 
	 * @return	string
     */
	public function getBits() {
		return $this->bits;
	}
	
	/**
     * 
	 * 
	 * @return	string
     */
	public function getMime() {
		return $this->mime;
	}
	
	/**
     * 
     *
     * @param	string	$file
	 * @param	int		$quality
     */
	public function save($file, $quality = 90) {
		$this->image->setCompressionQuality($quality);

		$this->image->setImageFormat($this->mime);

		$this->image->writeImage($file);
	}
	
	/**
     * 
     *
     * @param	int	$width
	 * @param	int	$height
	 * @param	string	$default
     */
	public function resize($width = 0, $height = 0, $default = '') {
		if (!$this->width || !$this->height) {
			return;
		}

		switch ($default) {
			case 'w':
				$height = $width;
				break;
			case 'h':
				$width = $height;
				break;
		}

		// Image Magick Filter Comparison
		// https://legacy.imagemagick.org/Usage/filter
		// https://urmaul.com/blog/imagick-filters-comparison
		$this->image->resizeImage($width, $height, Imagick::FILTER_CATROM, 1, true);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();

		if ($width == $height && $this->width != $this->height) {
			$image = new Imagick();

			if ($this->mime == 'image/png') {
				$background_color = 'transparent';
			} else {
				$background_color = 'white';
			}
			
			$image->newImage($width, $height, new ImagickPixel($background_color));
			
			$x = (int)(($width - $this->width) / 2);
			$y = (int)(($height - $this->height) / 2);

			$image->compositeImage($this->image, Imagick::COMPOSITE_OVER, $x, $y);
			

			if($default == 'merge' && $x != null){
				$crop = $image;
				
				$image_left = clone $image;
				$image_left->cropImage($x, $height, $x, $y);
				$image_left->blurImage(0,9);
				$image_left->setImageType(Imagick::IMGTYPE_GRAYSCALE);
				
				$image_right = clone $image;
				$image_right->cropImage($x + 1, $height, $width - ($x * 2), $y);
				$image_right->blurImage(0,9);
				$image_right->setImageType(Imagick::IMGTYPE_GRAYSCALE);
				
				$image->compositeImage($image_left, Imagick::COMPOSITE_OVER, 0, 0);
				$image->compositeImage($image_right, Imagick::COMPOSITE_OVER, $width - $x - 1, 0);
				
			}	
			$this->image = $image;

			$this->width = $this->image->getImageWidth();
			$this->height = $this->image->getImageHeight();
		}
	}
	
	/**
     * 
     *
     * @param	string	$watermark
	 * @param	string	$position
     */
	public function watermark($watermark, $position = 'bottomright') {
		$watermark = new Imagick($watermark);

		switch ($position) {
			case 'overlay':
				for ($width = 0; $width < $this->width; $width += $watermark->getImageWidth()) {
					for ($height = 0; $height < $this->height; $height += $watermark->getImageHeight()) {
						$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $width, $height);
					}
				}
				break;
			case 'topleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, 0);
				break;
			case 'topcenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), 0);
				break;
			case 'topright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), 0);
				break;
			case 'middleleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'middlecenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'middleright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'bottomleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, $this->height - $watermark->getImageHeight());
				break;
			case 'bottomcenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), $this->height - $watermark->getImageHeight());
				break;
			case 'bottomright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), $this->height - $watermark->getImageHeight());
				break;
		}
	}
	
	/**
     * 
     *
     * @param	int		$top_x
	 * @param	int		$top_y
	 * @param	int		$bottom_x
	 * @param	int		$bottom_y
     */
	public function crop($top_x, $top_y, $bottom_x, $bottom_y) {
		$this->image->cropImage($bottom_x - $top_x, $bottom_y - $top_y, $top_x, $top_y);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();
	}
	
	/**
     * 
     *
     * @param	int		$degree
	 * @param	string	$color
     */
	public function rotate($degree, $color = 'FFFFFF') {
		$this->image->rotateImage($color, $degree);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();
	}
	
	/**
     * 
     *
     */
	private function filter() {
        $args = func_get_args();

        call_user_func_array('imagefilter', $args);
	}
	
	/**
     * 
     *
     * @param	string	$text
	 * @param	int		$x
	 * @param	int		$y 
	 * @param	int		$size
	 * @param	string	$color
     */
	private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000') {
		$rgb = $this->html2rgb($color);

		imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
	}
	
	/**
     * 
     *
     * @param	object	$merge
	 * @param	object	$x
	 * @param	object	$y
	 * @param	object	$opacity
     */
	private function merge($merge, $x = 0, $y = 0, $opacity = 100) {
		imagecopymerge($this->image, $merge->getImage(), $x, $y, 0, 0, $merge->getWidth(), $merge->getHeight(), $opacity);
	}
	
	/**
     * 
     *
     * @param	string	$color
	 * 
	 * @return	array
     */
	private function html2rgb($color) {
		if ($color[0] == '#') {
			$color = substr($color, 1);
		}

		if (strlen($color) == 6) {
			list($r, $g, $b) = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
		} elseif (strlen($color) == 3) {
			list($r, $g, $b) = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
		} else {
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return [$r, $g, $b];
	}
}

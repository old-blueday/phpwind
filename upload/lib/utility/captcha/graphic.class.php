<?php
/**
 * 
 * @author pw team, Nov 12, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 
 * @package default
 */
!function_exists('readover') && exit('Forbidden');
class PW_Graphic {

	var $width;
	var $height;
	var $codes;
	var $num;
	var $lang = 'en';
	var $fontSize = 12;
	var $imageType = 'png';
	var $backGround = '#FFFFFF';
	var $disturbImg = false;//干扰图型
	var $disturbCode = false;//干扰文字
	var $fontRandomSize = false;
	var $fontRandomFamily = false;
	var $fontRandomAngle = false;
	var $fontRandomPosition = true;
	var $fontRandomColor = false;

	function PW_Graphic($w,$h) {
		$w = intval($w);
		$h = intval($h);
		($w <= 0 || $w>1000) && $w = 150;
		($h <= 0 || $h>800) && $h = 60;
		$this->width	= $w;
		$this->height	= $h;
	}
	
	function setCodes($code){
		$this->codes = $code;
		$this->num = $this->getNum($code);
	}
	/**
	 * 打印背景
	 */
	function background() {
		$im  = imagecreatetruecolor($this->width,$this->height);
		$bgs = array();

		if ($this->backGround == 'image' && function_exists('imagecreatefromjpeg') && function_exists('imagecopymerge')) {
			if ($fp = @opendir($GLOBALS['imgdir'].'/ck/bg/')) {
				while ($flie = @readdir($fp)) {
					if (preg_match('/\.jpg$/i',$flie)) {
						$bgs[] = $GLOBALS['imgdir'].'/ck/bg/'.$flie;
					}
				}
				@closedir($fp);
			}
		}
		if ($this->backGround == 'image' && $bgs) {
			$imbg = imagecreatefromjpeg($bgs[array_rand($bgs)]);
			imagecopymerge($im,$imbg,0,0,mt_rand(0,200-$this->width),mt_rand(0,80-$this->height), $this->width,$this->height,100);
			imagedestroy($imbg);
		} elseif (preg_match('/^#([a-f0-9]{6})$/i' , $this->backGround ,$m)) {
			//specified color
			$m = $m[1];
			for ($i = 0; $i < $this->width; $i++)
			imageline($im,$i,0,$i,$this->height,imagecolorallocate($im,hexdec($m[0].$m[1]),hexdec($m[2].$m[3]),hexdec($m[4].$m[5])));
		} else {
			//random lines
			$c = array();
			for ($i = 0; $i < 3; $i++) {
				$c[$i]		= mt_rand(200, 255);
				$step[$i]	= (mt_rand(100, 150) - $c[$i]) / $this->width;
			}
			for ($i = 0; $i < $this->width; $i++) {
				imageline($im,$i,0,$i,$this->height,imagecolorallocate($im,$c[0],$c[1],$c[2]));
				$c[0] += $step[0];
				$c[1] += $step[1];
				$c[2] += $step[2];
			}
		}
		return $im;
	}

	/**
	 * 
	 * @param $im
	 */
	function getColor(&$im) {
		if ($this->fontRandomColor) {
			$color = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
		} else {
			static $color = null;
			if (!isset($color)) {
				$c_index = imagecolorat($im, 1, 1);
				$c = imagecolorsforindex($im, $c_index);
				$color = imagecolorallocate($im,255-$c['red'],255-$c['green'],255-$c['blue']);
			}
		}
		return $color;
	}
	
	function getNum($code = null){
		$code == null && $code = $this->codes;
		if (is_string($code)) {
			return strlen($code);
		} elseif(is_array($code)) {
			return count($code);
		}else{
			return $code;
		}
	}
	
	/**
	 * 
	 * @param $im
	 */
	function ttffont(&$im) {
		$codefont = $GLOBALS['imgdir'] . '/fonts/' . $this->lang . '/';
		$dirs = opendir($codefont);
		$ttf = array();
		while ($file = readdir($dirs)) {
			if ($file != '.' && $file != '..' && preg_match('/\.ttf$/i',$file)) {
				$ttf[] = $file;
			}
		}
		@closedir($dirs);
		if (empty($ttf)) return;

		$width	= $this->width / $this->num;
		for ($i = 0; $i < $this->num; $i++) {
			$dsize	= $this->fontRandomSize ? mt_rand($this->fontSize*0.8,$this->fontSize*1.2) : $this->fontSize;
			$angle	= $this->fontRandomAngle ? mt_rand(-30, 30) : 0;
			$color	= $this->getColor($im);
			$font	= $codefont.$ttf[array_rand($ttf)];
			$box	= $this->N_imagettfbbox($dsize,0,$font,$this->codes[$i]);
			$length = $width * $i;
			$height = $box[1] - $box[5];
			$margin = ($this->height - $height)/2;
			($margin < 0 || $margin > $this->height) && $margin = 0;
			$x = $this->fontRandomPosition ? mt_rand($length,$length + $width - (max($box[2], $box[4]) - min($box[0], $box[6]))) : $length;
			$y = $this->fontRandomPosition ? mt_rand(max($box[1],$box[3])-min($box[5],$box[7]),$this->height) : $this->height-$margin;
			imagettftext($im,$dsize,$angle,$x,$y,$color,$font,$this->codes[$i]);
		}
	}

	function N_imagettfbbox($size,$angle,$fontfile,$text) {
		if (function_exists('imagecreatetruecolor')) {
			$im = imagecreatetruecolor(1,1);
		} else {
			$im = imagecreate(1,1);
		}
		$bbox = imagettftext($im,$size,$angle,0,0,imagecolorallocate($im,0,0,0),$fontfile,$text);
		imagedestroy($im);
		return $bbox;
	}

	/**
	 * 图片文字
	 * @param $im
	 */
	function imgfont(&$im) {
		$img = array();
		if (function_exists('imagecreatefromgif')) {
			$imgfont = $GLOBALS['imgdir'].'/ck/gif/';
			$dirs = opendir($imgfont);
			while ($file = readdir($dirs)) {
				if ($file != '.' && $file != '..' && file_exists($imgfont.$file.'/2.gif')) {
					$img[] = $file;
				}
			}
			@closedir($dirs);
		}
		$width	= $this->width / $this->num;

		for ($i = 0; $i < $this->num; $i++) {
			$filepath = $img ? $imgfont.$img[array_rand($img)].'/'.strtolower($this->codes[$i]).'.gif' : '';
			$len  = $i * $width;
			if ($filepath && file_exists($filepath)) {
				$src_im = imagecreatefromgif($filepath);
				list($srcW,$srcH) = getimagesize($filepath);
				$dstW = $this->height/2;
				$dstH = $dstW * $srcH / $srcW;
				$x = mt_rand($len,$len + $width - $dstW);
				$y = mt_rand(0,$this->height - $dstH);
				if ($this->fontRandomColor) {
					imagecolorset($src_im,0,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
				}
				if ($this->fontRandomSize) {
					$rate  = mt_rand(80,120)/100;
					$dstW *= $rate;
					$dstH *= $rate;
				}
				imagecopyresized($im, $src_im, $x, $y, 0, 0, $dstW, $dstH, $srcW, $srcH);
			} else {
				$color = $this->getColor($im);
				$x = mt_rand($len,$len + $width - 10);
				$y = mt_rand(10,$this->height - 10);
				imagechar($im,5,$x,$y,$code[$i],$color);
			}
		}
	}

	function _disturbcode(&$im) {
		$x = $this->width / $this->num;
		$y = $this->height / 10;
		$color	= $this->getColor($im);
		for ($i = 0; $i <= 3; $i++) {
			imagechar($im,5,$x*$i+mt_rand(0,$x-10),mt_rand($y,$this->height-10-$y),$this->codes[$i],$color);
		}
	}

	function _disturbimg(&$im) {
		$nums = $this->height / 10;
		for ($i=0; $i <= $nums; $i++) {
			$color	= $this->getColor($im);
			$x = mt_rand(0,$this->width);
			$y = mt_rand(0,$this->height);
			if (mt_rand(0,1)) {
				imagearc($im,$x,$y,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,360),mt_rand(0, 360),$color);
			} else {
				imageline($im,$x,$y,mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
			}
		}
	}

	function ckgif() {
		L::loadClass('gif', 'utility', false);
		$trueframe = mt_rand(1, 9);
		$im = $this->background();
		imagepng($im);
		imagedestroy($im);
		$bg = ob_get_contents();
		ob_clean();
		for ($i = 0; $i <= 9; $i++) {
			$im = imagecreatefromstring($bg);
			$this->disturbImg && $this->_disturbimg($im);
			$x[$i] = $y[$i] = 0;
			if ($i == $trueframe) {
				if($this->codes)$this->fontRandomFamily ? $this->ttffont($im) : $this->imgfont($im);
				$d[$i] = mt_rand(250, 400);
			} else {
				$this->_disturbcode($im);
				$d[$i] = mt_rand(5, 15);
			}
			imagegif($im);
			imagedestroy($im);
			$frame[$i] = ob_get_contents();
			ob_clean();
		}
		$anim = new GIFEncoder($frame, $d, 0, 0, 0, 0, 0, 'bin');
		header('Content-type: image/gif');
		echo $anim->getAnimation();
	}

	function ckpng() {
		header('Content-type: image/png');
		$im = $this->background();
		$this->disturbImg && $this->_disturbimg($im);
		if($this->codes)$this->fontRandomFamily ? $this->ttffont($im) : $this->imgfont($im);
		@imagepng($im);
		imagedestroy($im);
	}

	function display() {
		if ($this->imageType == 'gif' && function_exists('imagegif')) {
			$this->ckgif();
		} else {
			$this->ckpng();
		}
	}
}
?>
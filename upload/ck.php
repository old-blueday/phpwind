<?php
define('CK',1);
require_once('global.php');
if (GetServer('HTTP_IF_MODIFIED_SINCE') || GetServer('HTTP_IF_NONE_MATCH') || empty($_COOKIE) && !$pwServer['HTTP_USER_AGENT']) {
	sendHeader('304');exit;
}

if ($_GET['admin']) {
	$db_ckpath	 = '/';
	$db_ckdomain = '';
}

header('Pragma:no-cache');
header('Cache-control:no-cache');

class CkCode {

	var $width;
	var $height;
	var $num;
	var $style;
	var $gdtype;

	function CkCode() {
		list($w,$h,$n) = explode("\t",$GLOBALS['db_gdsize']);
		(!is_numeric($w) || $w < 50 || $w > 200) && $w = 150;
		(!is_numeric($h) || $h < 20 || $h > 80)  && $h = 60;
		(!is_numeric($n) || $n < 1) && $n = 4;
		$this->width	= $w;
		$this->height	= $h;
		$this->num		= (int)$n;
		$this->style	= $GLOBALS['db_gdstyle'];
		$this->gdtype	= $GLOBALS['db_gdtype'];
		$this->gdtype	== 3 && $this->gdtype = mt_rand(0,2);
	}

	function background() {
		$im  = imagecreatetruecolor($this->width,$this->height);
		$bgs = array();

		if (($this->style & 8) && function_exists('imagecreatefromjpeg') && function_exists('imagecopymerge')) {
			if ($fp = @opendir($GLOBALS['imgdir'].'/ck/bg/')) {
				while ($flie = @readdir($fp)) {
					if (preg_match('/\.jpg$/i',$flie)) {
						$bgs[] = $GLOBALS['imgdir'].'/ck/bg/'.$flie;
					}
				}
				@closedir($fp);
			}
		}
		if ($bgs) {
			$imbg = imagecreatefromjpeg($bgs[array_rand($bgs)]);
			imagecopymerge($im,$imbg,0,0,mt_rand(0,200-$this->width),mt_rand(0,80-$this->height), $this->width,$this->height,100);
			imagedestroy($imbg);
		} else {
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

	function getColor(&$im) {
		if ($this->style & 16) {
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

	function getCode($type=null,$set=true) {
		empty($type) && $type = $this->gdtype;
		$code = '';
		switch ($type) {
			case 2:
				global $db_charset,$lang;
				require_once GetLang('ck');
				$step = strtoupper($db_charset) == 'UTF-8' ? 3 : 2;
				$len  = (strlen($lang['ck'])/$step) - 1;
				for ($i = 0; $i < $this->num; $i++) {
					$code .= substr($lang['ck'],mt_rand(0,$len)*$step,$step);
				}
				$set && $this->cookie($code);
				if (strtoupper($db_charset) <> 'UTF-8') {
					$code = $this->convert($code,'UTF-8',$db_charset);
				}
				$code = explode(',',wordwrap($code,3,',',1));
				break;
			case 1:
				$list = 'BCEFGHJKMPQRTVWXY2346789';
				$len  = strlen($list) - 1;
				for ($i = 0; $i < $this->num; $i++) {
					$code .= $list[mt_rand(0,$len)];
				}
				$set && $this->cookie($code);
				break;
			default:
				$code = num_rand($this->num);
				$set && $this->cookie($code);
		}
		return $code;
	}

	function ttffont(&$im) {
		global $db_gdtype;
		$codefont = $GLOBALS['imgdir'].($this->gdtype == 2 ? '/fonts/ch/' : '/fonts/en/');
		$dirs = opendir($codefont);
		$ttf = array();
		while ($file = readdir($dirs)) {
			if ($file != '.' && $file != '..' && preg_match('/\.ttf$/i',$file)) {
				$ttf[] = $file;
			}
		}
		@closedir($dirs);
		if (empty($ttf)) return;

		$size	= $this->height / ($this->gdtype == 2 ? 2.4 : 2);
		$code	= $this->getCode();
		$width	= $this->width / $this->num;

		for ($i = 0; $i < $this->num; $i++) {
			$dsize	= ($this->style & 2) ? mt_rand($size*0.8,$size*1.2) : $size;
			$angle	= ($this->style & 4) ? mt_rand(-30, 30) : 0;
			$color	= $this->getColor($im);
			$font	= $codefont.$ttf[array_rand($ttf)];
			$box	= $this->N_imagettfbbox($dsize,0,$font,$code[$i]);
			$length = $width * $i;
			$x = mt_rand($length,$length + $width - (max($box[2], $box[4]) - min($box[0], $box[6])));
			$y = mt_rand(max($box[1],$box[3])-min($box[5],$box[7]),$this->height);
			imagettftext($im,$dsize,$angle,$x,$y,$color,$font,$code[$i]);
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
		$code	= $this->getCode();
		$width	= $this->width / $this->num;

		for ($i = 0; $i < $this->num; $i++) {
			$filepath = $img ? $imgfont.$img[array_rand($img)].'/'.strtolower($code[$i]).'.gif' : '';
			$len  = $i * $width;
			if ($filepath && file_exists($filepath)) {
				$src_im = imagecreatefromgif($filepath);
				list($srcW,$srcH) = getimagesize($filepath);
				$dstW = $this->height/2;
				$dstH = $dstW * $srcH / $srcW;
				$x = mt_rand($len,$len + $width - $dstW);
				$y = mt_rand(0,$this->height - $dstH);
				if ($this->style & 16) {
					imagecolorset($src_im,0,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
				}
				if ($this->style & 2) {
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

	function disturbcode(&$im) {
		$code = $this->getCode(1,false);
		$x = $this->width / $this->num;
		$y = $this->height / 10;
		$color	= $this->getColor($im);
		for ($i = 0; $i <= 3; $i++) {
			imagechar($im,5,$x*$i+mt_rand(0,$x-10),mt_rand($y,$this->height-10-$y),$code[$i],$color);
		}
	}

	function disturbimg(&$im) {
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
			($this->style & 32) && $this->disturbimg($im);
			$x[$i] = $y[$i] = 0;
			if ($i == $trueframe) {
				($this->style & 1 || $this->gdtype == 2) ? $this->ttffont($im) : $this->imgfont($im);
				$d[$i] = mt_rand(250, 400);
			} else {
				$this->disturbcode($im);
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
		($this->style & 32) && $this->disturbimg($im);
		($this->style & 1 || $this->gdtype == 2) ? $this->ttffont($im) : $this->imgfont($im);
		imagepng($im);
		imagedestroy($im);
	}

	function out() {
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagecolorallocate') || !function_exists('imagepng') || !function_exists('imagettftext')) {
			header("ContentType: image/bmp");
			$code = $this->getCode(4);
			echo $this->Codebmp($code,$this->num);
		} elseif (empty($_GET['nowtime'])) {
			$im = $this->background();
			imagepng($im);
			imagedestroy($im);
		} elseif (($this->style & 64) && function_exists('imagegif')) {
			$this->ckgif();
		} else {
			$this->ckpng();
		}
	}

	function cookie($code) {
		global $timestamp;
		Cookie('cknum',StrCode($timestamp."\t\t".md5($code.$timestamp)));
	}

	function Codebmp($nmsg,$num) {
		$color = array(
			0 => chr(0).chr(0).chr(0),
			1 => chr(255).chr(255).chr(255),
		);
		$numbers = array(
			0 => '1110000111110111101111011110111101001011110100101111010010111101001011110111101111011110111110000111',
			1 => '1111011111110001111111110111111111011111111101111111110111111111011111111101111111110111111100000111',
			2 => '1110000111110111101111011110111111111011111111011111111011111111011111111011111111011110111100000011',
			3 => '1110000111110111101111011110111111110111111100111111111101111111111011110111101111011110111110000111',
			4 => '1111101111111110111111110011111110101111110110111111011011111100000011111110111111111011111111000011',
			5 => '1100000011110111111111011111111101000111110011101111111110111111111011110111101111011110111110000111',
			6 => '1111000111111011101111011111111101111111110100011111001110111101111011110111101111011110111110000111',
			7 => '1100000011110111011111011101111111101111111110111111110111111111011111111101111111110111111111011111',
			8 => '1110000111110111101111011110111101111011111000011111101101111101111011110111101111011110111110000111',
			9 => '1110001111110111011111011110111101111011110111001111100010111111111011111111101111011101111110001111'
		);
		$code  = '';
		$code .= chr(66).chr(77).chr(230).chr(4).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(54).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(10).chr(0).chr(0).chr(0).chr(1).chr(0);
		$code .= chr(24).chr(0).chr(0).chr(0).chr(0).chr(0).chr(176).chr(4).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);

		for ($i=9;$i>=0;$i--){
			for ($j=0;$j<$num;$j++){
				for ($k=1;$k<=10;$k++){
					if (mt_rand(0,7)<1) {
						$code .= $color[mt_rand(0,1)];
					} else {
						$code .= $color[substr($numbers[$nmsg[$j]],$i*10+$k,1)];
					}
				}
			}
		}
		return $code;
	}
	function convert($str,$to_encoding,$from_encoding) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($str,$to_encoding,$from_encoding);
		} else {
			L::loadClass('Chinese', 'utility/lang', false);
			$chs = new Chinese($from_encoding,$to_encoding);
			return $chs->Convert($str);
		}
	}
}
$ck = new CkCode();
$ck->out();
exit;
?>
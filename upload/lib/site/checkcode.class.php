<?php
!defined('P_W') && exit('Forbidden');
/**
 * 验证码
 *
 */

class PW_CheckCode {
	
	var $width;
	var $height;
	var $num;
	var $style;
	var $gdtype;
	var $gdcontent;

	function PW_CheckCode() {
		list($w, $h, $n) = explode("\t", $GLOBALS['db_gdsize']);
		(!is_numeric($w) || $w < 50 || $w > 200) && $w = 150;
		(!is_numeric($h) || $h < 20 || $h > 80)  && $h = 60;
		(!is_numeric($n) || $n < 1) && $n = 4;
		$this->width	= $w;
		$this->height	= $h;
		$this->num		= (int)$n;
		$this->style	= $GLOBALS['db_gdstyle'];//识别难度
		$this->gdtype	= $GLOBALS['db_gdtype'];//类型: 1-图片,2-Flash,3-语音
		$this->gdcontent = $GLOBALS['db_gdcontent'];//内容: 1-数字,2-英文,3-中文
		$this->cloudgdcode = $GLOBALS['db_cloudgdcode'] ? $GLOBALS['db_cloudgdcode'] : 0;
		if(isset($this->gdcontent[3]) && ($this->gdtype == 2 || $this->gdtype == 3 || !function_exists('imagettftext') || !$this->checkChFonts()))
			unset($this->gdcontent[3]);
		if(is_array($this->gdcontent) && count($this->gdcontent)>0){
			$this->gdcontent = array_rand($this->gdcontent);
		}else{
			$this->gdcontent = 2;
		}
	}
	
	function getCode($type = null, $set = true) {
		empty($type) && $type = $this->gdcontent;
		$code = '';
		switch ($type) {
			case 3:
				global $db_charset, $lang;
				require_once GetLang('ck');
				$step = strtoupper($db_charset) == 'UTF-8' ? 3 : 2;
				$len  = (strlen($lang['ck']) / $step) - 1;
				for ($i = 0; $i < $this->num; $i++) {
					$code .= substr($lang['ck'], mt_rand(0, $len)*$step, $step);
				}
				$set && $this->cookie($code);
				if (strtoupper($db_charset) <> 'UTF-8') {
					$code = $this->convert($code, 'UTF-8', $db_charset);
				}
				$code = explode(',', wordwrap($code, 3, ',', 1));
				break;
			case 2:
				$list = 'BCEFGHJKMPQRTVWXY2346789';
				$len  = strlen($list) - 1;
				for ($i = 0; $i < $this->num; $i++) {
					$code .= $list[mt_rand(0, $len)];
				}
				$set && $this->cookie($code);
				break;
			default:
				$list = '2346789';
				$this->gdtype == 3 && $list .= '15';
				$len = strlen($list) - 1;
				mt_srand((double) microtime() * 1000000);
				for ($i = 0; $i < $this->num; $i++) {
					$code .= $list{mt_rand(0, $len)};
				}
				$set && $this->cookie($code);
		}
		return $code;
	}
	
	function out() {
		switch ($this->gdtype){
			case 2:
				$this->outputFlash();
				//Flash
				break;
			case 3:
				//语音
				$this->outputAudio();
				break;
			default:
				//图片
				$this->outputImage();
		}
	}
	
	function outputAudio() {
		$code = $this->getCode();
		L::loadClass('audio', 'utility/captcha', false);
		$audio = new PW_Audio();
		$audio->setAudioPath(D_P . 'images/ck/audio/');
		$audio->setCode($code);
		$audio->outputAudio();
	}
	
	function outputImage(){
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagecolorallocate') || !function_exists('imagepng')) {
			header("ContentType: image/bmp");
			$code = $this->getCode(1);
			echo $this->Codebmp($code, $this->num);exit;
		}
		$code = $this->getCode();
		L::loadClass('graphic', 'utility/captcha', false);
		$graphic = new PW_Graphic($this->width, $this->height);
		$graphic->backGround = $this->style & 8 ? 'image' : 'random';
		
		if (!empty($_GET['nowtime'])) {
			$graphic->setCodes($code);
			$this->gdcontent == 3 && $graphic->lang = 'ch';
			$graphic->fontSize = $this->height / ($this->gdcontent == 3 ? 2.4 : 2);
			if (($this->style & 64) && function_exists('imagegif')) {
				$graphic->imageType = 'gif';
			}
			$this->style & 16 && $graphic->fontRandomColor = true;
			$this->style & 4  && $graphic->fontRandomAngle = true;
			$this->style & 2  && $graphic->fontRandomSize = true;
			($this->style & 1 || $this->gdcontent == 3) && function_exists('imagettftext') && $graphic->fontRandomFamily = true;
			$this->style & 32 && $graphic->disturbImg = true;
		}
		$graphic->display();
	}
	
	function outputFlash(){
		L::loadClass('flash', 'utility/captcha', false);
		$flash = new PW_Flash($this->width, $this->height);
		$flash->codes = $this->getCode();
		$flash->display();
	}
	
	function cookie($code) {
		global $timestamp;
		Cookie('cknum', StrCode($timestamp . "\t\t" . md5($code . $timestamp . getHashSegment())));
	}

	function Codebmp($nmsg,$num) {
		$color = array(
			0 => chr(0) . chr(0) . chr(0),
			1 => chr(255) . chr(255) . chr(255),
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

		for ($i=9; $i>=0; $i--){
			for ($j=0; j<$num; $j++){
				for ($k=1; $k<=10; $k++){
					if (mt_rand(0, 7)<1) {
						$code .= $color[mt_rand(0, 1)];
					} else {
						$code .= $color[substr($numbers[$nmsg[$j]], $i*10+$k, 1)];
					}
				}
			}
		}
		return $code;
	}
	
	function convert($str,$to_encoding,$from_encoding) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($str, $to_encoding, $from_encoding);
		} else {
			L::loadClass('Chinese', 'utility/lang', false);
			$chs = new Chinese($from_encoding, $to_encoding);
			return $chs->Convert($str);
		}
	}
	
	function checkChFonts(){
		$codefont = $GLOBALS['imgdir'] . '/fonts/ch/';
		if (file_exists($codefont)) {
			$dirs = opendir($codefont);
			while ($file = readdir($dirs)) {
				if ($file != '.' && $file != '..' && preg_match('/\.ttf$/i', $file)) {
					@closedir($dirs);
					return true;
				}
			}
			@closedir($dirs);
		}
		return false;
	}
	
	function getCheckCodeTemplate($isCheck = false) {
		global $onlineip;
		static $captchaUrl = null;
		static $jsLoaded = null;
		if (is_null($captchaUrl) && $this->cloudgdcode) {
			$cloudService = L::loadClass('cloudcaptcha', 'utility/captcha');
			$captcha['sessionid'] = $cloudService->generateSessionid($onlineip);
			$captcha['url']	= $cloudService->getCaptchaUrl();
			$result = $isCheck ? $cloudService->request($captcha['url'] . '&sessionid=' . $captcha['sessionid']) : true;
			if ($result) {
				$captchaUrl = 'var cloudcaptchaurl = "' . $captcha['url'] . '&sessionid=' . $captcha['sessionid'] . '";';
				Cookie('cloudcksessionid', $captcha['sessionid']);
			} else {
				$this->cloudgdcode = 0;
				Cookie('cloudckfailed', 1, $GLOBALS['timestamp'] + 180);
			}
		}
		$jsString = $jsLoaded ? '' : '<script type="text/javascript" src="js/pw_authcode.js"></script>';
		is_null($jsLoaded) && $jsLoaded = true;
		$captchaUrl = $captchaUrl ? $captchaUrl : '';
		return $jsString . 
				'<script type="text/javascript">
					var flashWidth = "' . $this->width . '";
					var flashHeight = "' . $this->height . '";
					var gdtype = ' . $this->gdtype . ';
					var cloudgdcode = ' . $this->cloudgdcode . ';' . $captchaUrl . '
				</script>';
	}
}
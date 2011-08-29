<?php
!function_exists('readover') && exit('Forbidden');

function ImgWaterMark($source, $w_pos = 0, $w_img = '', $w_text = '', $w_font = 12, $w_color = '#FF0000', $w_pct, $w_quality, $dstsrc = null) {
	global $imgdir, $db_waterfonts, $db_watermark;
	$sourcedb = $waterdb = array();
	if (!($sourcedb = GetImgInfo($source))) {
		return false;
	}
	if ($db_watermark == 1 && GetImgInfo("$imgdir/water/$w_img")) {
		$waterdb = GetImgInfo("$imgdir/water/$w_img");
	} elseif ($db_watermark == 2 && $w_text) {
		empty($db_waterfonts) && $db_waterfonts = 'en/PilsenPlakat';
		empty($w_font) && $w_font = 12;
		$fontsfile = "$imgdir/fonts/$db_waterfonts.ttf";
		$temp = imagettfbbox($w_font, 0, $fontsfile, $w_text); //取得使用 TrueType 字体的文本的范围
		$waterdb['width'] = $temp[2] - $temp[6];
		$waterdb['height'] = $temp[3] - $temp[7];
		unset($temp);
	} else {
		return false;
	}
	if ($w_pos == 0) {
		$wX = rand(0, ($sourcedb['width'] - $waterdb['width']));
		$wY = $db_watermark == 1 ? rand(0, ($sourcedb['height'] - $waterdb['height'])) : rand($waterdb['height'], $sourcedb['height']);
	} elseif ($w_pos == 1) {
		$wX = 5;
		$wY = $db_watermark == 1 ? 5 : $waterdb['height'];
	} elseif ($w_pos == 2) {
		$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
		$wY = $db_watermark == 1 ? 5 : $waterdb['height'];
	} elseif ($w_pos == 3) {
		$wX = $sourcedb['width'] - $waterdb['width'] - 5;
		$wY = $db_watermark == 1 ? 5 : $waterdb['height'];
	} elseif ($w_pos == 4) {
		$wX = 5;
		$wY = $db_watermark == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
	} elseif ($w_pos == 5) {
		$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
		$wY = $db_watermark == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
	} elseif ($w_pos == 6) {
		$wX = $sourcedb['width'] - $waterdb['width'] - 5;
		$wY = $db_watermark == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
	} else {
		$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
		$wY = $db_watermark == 1 ? ($sourcedb['height'] - $waterdb['height']) / 2 : ($sourcedb['height'] + $waterdb['height']) / 2;
	}
	imagealphablending($sourcedb['source'], true);
	if ($db_watermark == 1) {
		if ($waterdb['type'] == 'png') {
			$tmp = imagecreatetruecolor($sourcedb['width'], $sourcedb['height']);
			imagecopy($tmp, $sourcedb['source'], 0, 0, 0, 0, $sourcedb['width'], $sourcedb['height']);
			imagecopy($tmp, $waterdb['source'], $wX, $wY, 0, 0, $waterdb['width'], $waterdb['height']);
			$sourcedb['source'] = $tmp;
			//imagecopy($sourcedb['source'], $waterdb['source'], $wX, $wY, 0, 0, $waterdb['width'], $waterdb['height']);
		} else {
			imagecopymerge($sourcedb['source'], $waterdb['source'], $wX, $wY, 0, 0, $waterdb['width'], $waterdb['height'], $w_pct);
		}
	} else {
		if (strlen($w_color) != 7) return false;
		$R = hexdec(substr($w_color, 1, 2));
		$G = hexdec(substr($w_color, 3, 2));
		$B = hexdec(substr($w_color, 5));
		//imagestring($sourcedb['source'],$w_font,$wX,$wY,$w_text,imagecolorallocate($sourcedb['source'],$R,$G,$B));
		if (strpos($db_waterfonts, 'ch/') !== false && strtoupper($GLOBALS['db_charset']) != 'UTF-8') {
			$w_text = pwConvert($w_text, 'UTF-8', $GLOBALS['db_charset']);
		}
		imagettftext($sourcedb['source'], $w_font, 0, $wX, $wY, imagecolorallocate($sourcedb['source'], $R, $G, $B), $fontsfile, $w_text);
	}
	//	P_unlink($source);
	$dstsrc && $source = $dstsrc;
	MakeImage($sourcedb['type'], $sourcedb['source'], $source, $w_quality);
	isset($waterdb['source']) && imagedestroy($waterdb['source']);
	imagedestroy($sourcedb['source']);
	return true;
}
function MakeThumb($srcFile, &$dstFile, $dstW, $dstH, $cenTer = null, $sameFile = null, $fixWH = null) {
	global $db_quality;
	$minitemp = GetThumbInfo($srcFile, $dstW, $dstH, $cenTer);
	list($imagecreate, $imagecopyre) = GetImagecreate($minitemp['type']);
	if (empty($minitemp) || !$imagecreate) return false;
	//if ((empty($sameFile) && $dstFile === $srcFile) || empty($minitemp) || !$imagecreate) return false;
	//!empty($sameFile) && $dstFile = $srcFile;
	$imgwidth = $minitemp['width'];
	$imgheight = $minitemp['height'];
	$srcX = $srcY = 0;
	if (!empty($cenTer)) {
		$dsDivision = $imgheight / $imgwidth;
		$fixDivision = $dstH / $dstW;
		if ($dsDivision > $fixDivision) {
			$tmpimgheight = $imgwidth * $fixDivision;
			$srcY = round(($imgheight - $tmpimgheight) / 2);
			$imgheight = $tmpimgheight;
		} else {
			$tmpimgwidth = $imgheight / $fixDivision;
			$srcX = round(($imgwidth - $tmpimgwidth) / 2); 
			$imgwidth = $tmpimgwidth;
		}
	}
	$dstX = $dstY = 0;
	$thumb = $imagecreate($minitemp['dstW'], $minitemp['dstH']);
	
	if (function_exists('ImageColorAllocate') && function_exists('ImageColorTransparent')) {
		//背景透明处理
		$black = ImageColorAllocate($thumb,0,0,0);
		$bgTransparent = ImageColorTransparent($thumb,$black);
	}

	$imagecopyre($thumb, $minitemp['source'], $dstX, $dstY, $srcX, $srcY, $minitemp['dstW'], $minitemp['dstH'], $imgwidth, $imgheight);
	MakeImage($minitemp['type'], $thumb, $dstFile, $db_quality);
	imagedestroy($thumb);
	return array(
		$minitemp['dstW'],
		$minitemp['dstH']
	);
}
function GetThumbInfo($srcFile, $dstW, $dstH, $cenTer = null) {
	$imgdata = array();
	$imgdata = GetImgInfo($srcFile);
	if (empty($imgdata) || (($dstW && $imgdata['width'] <= $dstW) && ($dstH && $imgdata['height'] <= $dstH))) return false;
	
	if (empty($dstW) && $dstH > 0 && $imgdata['height'] > $dstH) {
		if (!empty($cenTer)) {
			$imgdata['dstW'] = $imgdata['dstH'] = $dstH;
		} else {
			$imgdata['dstH'] = $dstH;
			$imgdata['dstW'] = round($dstH / $imgdata['height'] * $imgdata['width']);
		}
	} elseif (empty($dstH) && $dstW > 0 && $imgdata['width'] > $dstW) {
		if (!empty($cenTer)) {
			$imgdata['dstW'] = $imgdata['dstH'] = $dstW;
		} else {
			$imgdata['dstW'] = $dstW;
			$imgdata['dstH'] = round($dstW / $imgdata['width'] * $imgdata['height']);
		}
	} elseif ($dstW > 0 && $dstH > 0) {
		if (($imgdata['width'] / $dstW) < ($imgdata['height'] / $dstH)) {
			if (!empty($cenTer)) {
				$imgdata['dstW'] = $dstW;
				$imgdata['dstH'] = $dstH;
			} else {
				$imgdata['dstW'] = round($dstH / $imgdata['height'] * $imgdata['width']);
				$imgdata['dstH'] = $dstH;
			}
		} elseif (($imgdata['width'] / $dstW) > ($imgdata['height'] / $dstH)) {
			if (!empty($cenTer)) {
				$imgdata['dstW'] = $dstW;
				$imgdata['dstH'] = $dstH;
			} else {
				$imgdata['dstW'] = $dstW;
				$imgdata['dstH'] = round($dstW / $imgdata['width'] * $imgdata['height']);
			}
		} else {
			$imgdata['dstW'] = $dstW;
			$imgdata['dstH'] = $dstH;
		}
	} else {
		return false;
		//$imgdata['dstW'] = $imgdata['width'];
		//$imgdata['dstH'] = $imgdata['height'];
	}
	return $imgdata;
}
function GetImgInfo($srcFile) {
	$imgdata = (array) GetImgSize($srcFile);
	if ($imgdata['type'] == 1) {
		$imgdata['type'] = 'gif';
	} elseif ($imgdata['type'] == 2) {
		$imgdata['type'] = 'jpeg';
	} elseif ($imgdata['type'] == 3) {
		$imgdata['type'] = 'png';
	} elseif ($imgdata['type'] == 6) {
		$imgdata['type'] = 'bmp';
	} else {
		return false;
	}
	if (empty($imgdata) || !function_exists('imagecreatefrom' . $imgdata['type'])) {
		return false;
	}
	$imagecreatefromtype = 'imagecreatefrom' . $imgdata['type'];
	$imgdata['source'] = $imagecreatefromtype($srcFile);
	!$imgdata['width'] && $imgdata['width'] = imagesx($imgdata['source']);
	!$imgdata['height'] && $imgdata['height'] = imagesy($imgdata['source']);
	return $imgdata;
}
function MakeImage($type, $image, $filename, $quality = '75') {
	$makeimage = 'image' . $type;
	if (!function_exists($makeimage)) {
		return false;
	}
	if ($type == 'jpeg') {
		$makeimage($image, $filename, $quality);
	} else {
		$makeimage($image, $filename);
	}
	return true;
}
function GetImgSize($srcFile, $srcExt = null) {
	empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile, '.'), 1));
	$srcdata = array();
	$exts = array('jpg', 'jpeg', 'jpe', 'jfif');
	if (function_exists('read_exif_data') && in_array($srcExt, $exts)) {
		$datatemp = @read_exif_data($srcFile);
		$srcdata['width'] = $datatemp['COMPUTED']['Width'];
		$srcdata['height'] = $datatemp['COMPUTED']['Height'];
		$srcdata['type'] = 2;
		unset($datatemp);
	}
	!$srcdata['width'] && list($srcdata['width'], $srcdata['height'], $srcdata['type']) = @getimagesize($srcFile);
	if (!$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, $exts))) { //noizy fix
		return false;
	}
	return $srcdata;
}
function GetImagecreate($imagetype) {
	if ($imagetype != 'gif' && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')) {
		return array(
			'imagecreatetruecolor',
			'imagecopyresampled'
		);
	} elseif (function_exists('imagecreate') && function_exists('imagecopyresized')) {
		return array(
			'imagecreate',
			'imagecopyresized'
		);
	} else {
		return array();
	}
}

function modeImageThumb($srcFile, $dstFile, $dstX, $dstY) {
	global $db_quality;
	$imgdata = array();
	list($imgdata['width'], $imgdata['height'], $imgdata['type']) = @getimagesize($srcFile);
	switch ($imgdata['type']) {
		case 1:
			$imgdata['type'] = 'gif';
			break;
		case 2:
			$imgdata['type'] = 'jpeg';
			break;
		case 3:
			$imgdata['type'] = 'png';
			break;
		default:
			return false;
	}
	if (!empty($imgdata) && function_exists('imagecreatefrom' . $imgdata['type'])) {
		$imagecreatefromtype = 'imagecreatefrom' . $imgdata['type'];
	} else {
		return false;
	}
	$imgdata['source'] = $imagecreatefromtype($srcFile);
	!$imgdata['width'] && $imgdata['width'] = imagesx($imgdata['source']);
	!$imgdata['height'] && $imgdata['height'] = imagesy($imgdata['source']);

	list($imagecreate, $imagecopyre) = GetImageCreate($imgdata['type']);

	$thumb = $imagecreate($dstX, $dstY);
	$color = @ImageColorAllocate($thumb, 255, 255, 255);
	@imagefilledrectangle($thumb, 0, 0, $dstX, $dstY, $color);

	$pX = $pY = $pW = $pH = 0;

	if ($dstX && !$dstY) {
		$dstY = $imgdata['height'] * $dstX / $imgdata['width'];
	} else if (!$dstX && $dstY) {
		$dstX = $imgdata['width'] * $dstY / $imgdata['height'];
	}

	$p = ($dstX / $dstY);
	if ($imgdata['width'] / $imgdata['height'] > $p) { //说明宽度太大
		$pH = $dstY;
		$pW = $pH * $p;
		$imgdata['width'] = $imgdata['height']*$p;
	} else {
		$pW = $dstX;
		$pH = $pW / $p;
		$imgdata['height'] = $imgdata['width']/$p;
	}

	$imagecopyre($thumb, $imgdata['source'], 0, 0, 0, 0, $pW, $pH, $imgdata['width'], $imgdata['height']);
	MakeImage($imgdata['type'], $thumb, $dstFile, $db_quality);
	imagedestroy($thumb);
	return 1;
}

/**
 *  创建头像动态gif缩略图
 * @param $imageSource
 * @param $srcW
 * @param $srcH
 * @param $dstW
 * @param $dstH
 */
function makeAvatarGifThumb($imageSource, $srcW, $srcH, $dstW, $dstH) {
	if ($srcW <= $dstW && $srcH <= $dstH) return $imageSource;
	$imageSource = imagecreatefromstring($imageSource);
	list($imagecreate, $imagecopyre) = GetImageCreate('gif');
	if (($srcW / $dstW) < ($srcH / $dstH)) {
		$finalW = round($dstH / $srcH * $srcW);
		$finalH = $dstH;
	} elseif (($srcW / $dstW) > ($srcH / $dstH)) {
		$finalW = $dstW;
		$finalH = round($dstW / $srcW * $srcH);
	} else {
		$finalW = $dstW;
		$finalH = $dstH;
	}
	$thumb = $imagecreate($finalW, $finalH);
	if(function_exists('ImageColorAllocate') && function_exists('ImageColorTransparent')){
		//背景透明处理
		$black = ImageColorAllocate($thumb,0,0,0);
		$bgTransparent = ImageColorTransparent($thumb,$black);
	}
	$imagecopyre($thumb, $imageSource, 0, 0, 0, 0, $finalW, $finalH, $srcW, $srcH);
	ob_start();
	imagegif($thumb);
	imagedestroy($thumb);
	$imageStream = ob_get_contents();
	ob_end_clean();
	return $imageStream;
}
?>
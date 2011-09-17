<?php
!function_exists('readover') && exit('Forbidden');

list($GLOBALS['db_ifupload'],$GLOBALS['db_imgheight'],$GLOBALS['db_imgwidth'],$GLOBALS['db_imgsize']) = explode("\t",$GLOBALS['db_upload']);
//!$GLOBALS['db_imgwidth'] && !$GLOBALS['db_imgheight'] && $GLOBALS['db_imgwidth'] = 100;
!$GLOBALS['db_imgwidth'] && $GLOBALS['db_imgwidth'] = 120;
!$GLOBALS['db_imgheight'] && !$GLOBALS['db_imgheight'] = 120;

function showfacedesign($usericon,$show_a = null,$imgtype = null) {
	global $imgpath;
	$user_a = explode('|',$usericon);
	$faceurl = '';
	$user_a[1] = (int)$user_a[1];
	if ($user_a[4]) {
		$faceurl = "$imgpath/pig.gif";
		$user_a[1] = 4;
	} elseif ($user_a[1] == 3) {
		list($tempuid) = explode('.',$user_a[0]);
		list($tempdir,$tempuid) = explode('/',$tempuid);
		!$tempuid && $tempuid = $tempdir;
		if ((int)$tempuid > 0) {
			global $db_ftpweb,$attachpath,$attachdir;
			$user_a[6] && !$imgtype && $imgtype = 'm';
			list($imgtypedir,$ifUseThumb) = getUploadTypeDir($imgtype,$user_a[5]);
			$old_user_a_0 = $user_a[0];
			if ($ifUseThumb == 1 && !$user_a[6] && strpos($user_a[0],'.') != false) {
				$user_a[0] = substr($user_a[0],0,strrpos($user_a[0],'.')+1).'jpg';
			}
			if ($db_ftpweb && !file_exists("$attachdir/$imgtypedir/$user_a[0]")) {
				if (strpos($imgtypedir,'middle') !== false || strpos($imgtypedir,'small') !== false) {
					$faceurl = "$db_ftpweb/$imgtypedir/$user_a[0]";
				} else {
					$faceurl = "$db_ftpweb/upload/$old_user_a_0";
				}
			} else {
				if (file_exists("$attachdir/$imgtypedir/$user_a[0]")) {
					$faceurl = "$attachpath/$imgtypedir/$user_a[0]";
				} elseif((strpos($imgtypedir,'middle') !== false || strpos($imgtypedir,'small') !== false) && file_exists("$attachdir/upload/$old_user_a_0")) {
					$faceurl = "$attachpath/upload/$old_user_a_0";
				} else {
					$faceurl = "$imgpath/face/none.gif";
				}
			}
		}
	} elseif ($user_a[1] == 2 && strncmp($user_a[0],'http',4) == 0) {
		$faceurl = $user_a[0];
	}
	if (!$faceurl || (!$user_a[0] && empty($user_a[4])) || strpos($faceurl,'<')!==false || $user_a[1]<1) {
		$user_a[1] = 1;
	}
	if ($user_a[1] == 1) {
		global $imgdir;
		if (!$user_a[0] || !file_exists("$imgdir/face/$user_a[0]")) {
			$user_a[0] = 'none.gif';
		}
		$faceurl = "$imgpath/face/$user_a[0]";
	}
	$imglen = '';
	if ($user_a[1] == 2 || ($user_a[1] == 3 && !$imgtype) || $user_a[1] == 1) {
		list($user_a[2],$user_a[3]) = getfacelen($user_a[2],$user_a[3]);
		if ($user_a[2]) $imglen .= ($user_a[2] > 120) ? " width=120" : " width=\"$user_a[2]\"";
		if ($user_a[3]) $imglen .= ($user_a[3] > 120) ? " height=120" : " height=\"$user_a[3]\"";
	}

	if ($user_a[1] == 1&&$user_a[6]){
		$faceurl.="?".$user_a[6];
	}else if ($user_a[1] == 2&&$user_a[6]){
		$faceurl.="?".$user_a[6];
	}else if ($user_a[1] == 3&&$user_a[7]){
		$faceurl.="?".$user_a[7];
	}
	
	if (empty($show_a)) {
		
		return "<img class=\"pic\" src=\"$faceurl\"$imglen border=\"0\" />";
	} else {
		!$user_a[2] && $user_a[2] = '80';
		!$user_a[3] && $user_a[3] = '80';
		return array($faceurl, $user_a[1], $user_a[2], $user_a[3], $user_a[0], $user_a[4],$user_a[5], $imglen);
	}
}

function getUploadTypeDir($imgtype,$check){
	if (!$check) return array('upload','0');
	$imgtype = strtolower($imgtype);
	if ($imgtype == 'm') {
		return array('upload/middle','1');
	} elseif ($imgtype == 's') {
		return array('upload/small','1');
	} else {
		return array('upload','0');
	}
}

function flexlen($srcw, $srch, $dstw, $dsth) {
	!$dstw && $dstw = $GLOBALS['db_imgwidth'];
	!$dsth && $dsth = $GLOBALS['db_imgheight'];
	if ($srcw && !$srch) {
		$srch = ($dsth / $dstw) * $srcw;
	} elseif (!$srcw && $srch) {
		$srcw = ($dstw / $dsth) * $srch;
	} elseif (!$srcw && !$srch) {
		$srcw = $dstw;
		$srch = $dsth;
	}
	return getfacelen($srcw, $srch);
}

function getfacelen($img_w, $img_h) {
	global $db_imgheight,$db_imgwidth;
	$img_w = intval($img_w);
	$img_h = intval($img_h);
	$temp_w = $img_w ? $img_w : $db_imgwidth;
	$temp_h = $img_h ? $img_h : $db_imgheight;

	if ($db_imgwidth && ($img_w < 1 || $img_w > $db_imgwidth)) {
		$temp_w = $db_imgwidth;
	}
	if ($db_imgheight && ($img_h < 1 || $img_h > $db_imgwidth)) {
		$temp_h = $db_imgheight;
	}
	if ($temp_w && $temp_h && $img_w && $img_h) {
		if (($temp_w / $temp_h) < ($img_w / $img_h)) {
			$temp_h = ($img_h / $img_w) * $temp_w;
		} else {
			$temp_w = ($img_w / $img_h) * $temp_h;
		}
	}
	return array(round($temp_w), round($temp_h));
}

/*
function getfacelen($img_w,$img_h){
	global $db_imgheight,$db_imgwidth;
	$temp_w = ((int)$img_w<1 || $img_w>$db_imgwidth) ? $db_imgwidth : $img_w;
	$temp_h = ((int)$img_h<1 || $img_h>$db_imgheight) ? $db_imgheight : $img_h;
	$w = $db_imgwidth-$temp_w; $h = $db_imgheight-$temp_h;
	if ($w<$h) {
		$img_w = $temp_w;
	} elseif ($w>$h) {
		$img_h = $temp_h;
	} else {
		$img_w = $temp_w;
		$img_h = $temp_h;
	}
	return array($img_w,$img_h);
}
*/

function DelIcon($filename) {
	if (strpos($filename,'..') !== false) {
		return false;
	}
	require_once(R_P.'require/functions.php');
	pwDelatt("upload/$filename",$GLOBALS['db_ifftp']);
	pwDelatt("upload/middle/$filename",$GLOBALS['db_ifftp']);
	pwDelatt("upload/small/$filename",$GLOBALS['db_ifftp']);
	return true;
}

function setIcon($proicon, $facetype, $oldface) {
	global $timestamp;
	if (empty($proicon))
		return '';
	if ($facetype == 1) {
		global $imgdir;
		if (!file_exists("$imgdir/face/$proicon")) {
			$proicon = 'none.gif';
		}
		$oldface[2] = $oldface[3] = 0;
	} elseif ($facetype == 2) {

	} elseif ($facetype == 3) {
		//$oldface[2] = $oldface[3] = 0;
	} else {
		return '';
	}
	$oldface[2] < 1 && $oldface[2] = '';
	$oldface[3] < 1 && $oldface[3] = '';
	$oldface[4] = $oldface[4] ? 1 : '';

	$usericon = "$proicon|$facetype|$oldface[2]|$oldface[3]|$oldface[4]";
	if ($facetype == 3) {
		$usericon .= "|1";
	} else {
		$oldface[1] == 3 && DelIcon($oldface[0]);
	}
	$usericon .= '|1';
	$usericon .= "|$timestamp";
	strlen($usericon) > 255 && Showmsg('illegal_customimg');
	return $usericon;
}
?>
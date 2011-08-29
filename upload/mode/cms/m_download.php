<?php
!defined('M_P') && exit('Forbidden');

set_time_limit(300);
$aid = (int)S::getGP('aid');
empty($aid) && Showmsg('job_attach_error');

S::gp(array('type'), 'GP');

if (!$windid && GetCookie('winduser') && $ol_offset) {
	$userdb = explode("\t", getuserdb(D_P . "data/bbscache/online.php", $ol_offset));
	if ($userdb && $userdb[2] == $onlineip) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$winddb = $userService->get($userdb['8']);
		$winduid = $winddb['uid'];
		$groupid = $winddb['groupid'];
		$groupid == '-1' && $groupid = $winddb['memberid'];
		$userrvrc = round($winddb['rvrc'] / 10, 1);
		$windid = $winddb['username'];
		if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
			//* require_once pwCache::getPath(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
			pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
		} else {
			//* require_once pwCache::getPath(D_P . "data/groupdb/group_1.php");
			pwCache::getData(D_P . "data/groupdb/group_1.php");
		}
	}
	define('FX', 1);
}

$cmsAttachService = C::loadClass('cmsattachservice');;
$attach = $cmsAttachService->getAttachById($aid);
if (!$attach) {
	Showmsg('job_attach_error');
}

$fgeturl = $attach['attachurl'];

$fileext = substr(strrchr($attach['attachurl'], '.'), 1);
$filesize = 0;

if (strpos($pwServer['HTTP_USER_AGENT'], 'MSIE') !== false && $fileext == 'torrent') {
	$attachment = 'inline';
} else {
	$attachment = 'attachment';
}
$attach['name'] = trim(str_replace('&nbsp;', ' ', $attach['name']));
if ($db_charset == 'utf-8') {
	if (function_exists('mb_convert_encoding')) {
		$attach['name'] = mb_convert_encoding($attach['name'], "gbk", 'utf-8');
	} else {
		L::loadClass('Chinese', 'utility/lang', false);
		$chs = new Chinese('UTF8', 'gbk');
		$attach['name'] = $chs->Convert($attach['name']);
	}
}

if (strpos($fgeturl,'http')===false) {
	$fgeturl = R_P . $fgeturl;
	$filesize = filesize($fgeturl);
}


$ctype = '';
switch ($fileext) {
	case "pdf":
		$ctype = "application/pdf";
		break;
	case "rar":
	case "zip":
		$ctype = "application/zip";
		break;
	case "doc":
		$ctype = "application/msword";
		break;
	case "xls":
		$ctype = "application/vnd.ms-excel";
		break;
	case "ppt":
		$ctype = "application/vnd.ms-powerpoint";
		break;
	case "gif":
		$ctype = "image/gif";
		break;
	case "png":
		$ctype = "image/png";
		break;
	case "jpeg":
	case "jpg":
		$ctype = "image/jpeg";
		break;
	case "wav":
		$ctype = "audio/x-wav";
		break;
	case "mpeg":
	case "mpg":
	case "mpe":
		$ctype = "video/x-mpeg";
		break;
	case "mov":
		$ctype = "video/quicktime";
		break;
	case "avi":
		$ctype = "video/x-msvideo";
		break;
	case "txt":
		$ctype = "text/plain";
		break;
	default:
		$ctype = "application/octet-stream";
}
ob_end_clean();
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
header('Expires: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
header('Cache-control: max-age=86400');
header('Content-Encoding: none');
header("Content-Disposition: $attachment; filename=\"{$attach['name']}\"");
header("Content-type: $ctype");
header("Content-Transfer-Encoding: binary");
$filesize && header("Content-Length: $filesize");
$i = 1;
while (!@readfile($fgeturl)) {
	if (++$i > 3) break;
}
exit();
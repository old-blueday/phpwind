<?php
!defined('P_W') && exit('Forbidden');
define('AJAX', 1);
S::gp(array(
	'uid',
	'type',
	'verify',
	'urls'
), 'P');
S::gp(array('type'));
$uid = intval($uid);
$uid < 1 && $uid = $winduid;
checkVerify();

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$winddb = $userService->get($uid);//groupid,memberid
(!$winddb) && showExtraMsg('not_login');
$groupid = $winddb['groupid'] == '-1' ? $winddb['memberid'] : $winddb['groupid'];

if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
	pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
} else {
	pwCache::getData(D_P . 'data/groupdb/group_1.php');
}

if ($_G['allowremotepic'] == 0) {
	showExtraMsg('download_group_right');
}

if ($type == 'active') {
	L::loadClass('activeupload', 'upload', false);
	$mutiupload = new activeMutiUpload($uid, intval($_POST['cid']));
} elseif ($type == 'diary') {
	L::loadClass('diaryupload', 'upload', false);
	$mutiupload = new diaryMutiUpload($uid);
} elseif ($type == 'message') {
	L::loadClass('messageupload', 'upload', false);
	$mutiupload = new messageMutiUpload($uid);
} elseif ($type == 'cms') {
	require_once(R_P . 'mode/cms/lib/upload/articleupload.class.php');
	$mutiupload = new articleMutiUpload($uid);
} elseif ($type && file_exists(R_P . "require/extents/attach/{$type}mutiupload.class.php")) {
	$class = $type . 'MutiUpload';
	require_once S::escapePath(R_P . "require/extents/attach/{$type}mutiupload.class.php");
	$mutiupload = new $class($uid);
} else {
	L::loadClass('attmutiupload', 'upload', false);
	$mutiupload = new AttMutiUpload($uid, intval($_POST['fid']));
}
if (($return = $mutiupload->check()) !== true) {
	showExtraMsg($return);
}
L::loadClass('download','',false);
$downloaded = PwDownload::getRemoteFiles($urls,$mutiupload);
$attachs = $mutiupload->getAttachs();


//组装输出
$tmpArray = array();
foreach ($urls as $k=>$v) {
	if (isset($downloaded[$k])) {
		$attachInfo = array_shift($attachs);
		if ($attachInfo['attachurl']){
			list($attachInfo['attachurl']) = geturl($attachInfo['attachurl'], 'lf', $attachInfo['ifthumb']&1);
			//list($attachInfo['attachurl']) = geturl($attachInfo['attachurl'], 'lf');
		}
		$tmpArray[$attachInfo['aid']] = $attachInfo;
	} else {
		$tmpArray[] = array();
	}
}
$output = '';
foreach ($tmpArray as $key=>$value) {
	if (!S::isArray($value)) {
		$output .= "'$key' : [],";
	} else {
		$output .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
	}
}
$output = rtrim($output,',');
echo $output;
ajax_footer();

function showExtraMsg($msg) {
	echo $msg;
	ajax_footer();
}
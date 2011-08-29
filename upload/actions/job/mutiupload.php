<?php
!defined('P_W') && exit('Forbidden');

if (empty($_POST['step'])) {
	header("Content-type: text/html; charset=$db_charset");
	$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
	$filetype = '';
	foreach ($db_uploadfiletype as $key => $value) {
		$filetype .= ($filetype ? ',' : '') . $key . ':' . $value;
	}
	$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	$swfhash = GetVerify($winduid);
	echo pwJsonEncode(array('uid'=>$winduid,'step'=>2,'verify'=>$swfhash));

} else {
	
	define('AJAX', 1);
	S::gp(array(
		'uid',
		'type',
		'verify',
		'ua'
	), 'P');
	S::gp(array('type'));

	$uid = intval($uid);
	!$ua && $pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	$swfhash = GetVerify($uid?$uid:'');
	checkVerify('swfhash');

	if (!$db_allowupload) {
		showExtraMsg('upload_close');
	}
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$winddb = $userService->get($uid);//groupid,memberid
	(!$winddb) && showExtraMsg('not_login');
	$groupid = $winddb['groupid'] == '-1' ? $winddb['memberid'] : $winddb['groupid'];

	if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
		//* require_once pwCache::getPath(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
		pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
	} else {
		//* require_once pwCache::getPath(D_P . 'data/groupdb/group_1.php');
		pwCache::getData(D_P . 'data/groupdb/group_1.php');
	}
	if ($_G['allowupload'] == 0) {
		showExtraMsg('upload_group_right');
	}
	if ($_G['allownum'] > 0 && $winddb['uploadnum'] >= $_G['allownum']) {
		showExtraMsg('upload_num_error');
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
	PwUpload::upload($mutiupload);
	$attachInfo = $mutiupload->getAttachInfo();
	if ($ua && $attachInfo) {
		$attachService = L::loadClass('attachs','forum');
		if ($attachInfo['path']){
			$tmp = $db_ifftp ? $db_ftpweb : $attachpath;
			strpos($attachInfo['path'], "$tmp/") === 0 && $tmpPath = substr($attachInfo['path'], strlen("$tmp/"));
			strpos($tmpPath, "thumb/") === 0 && $tmpPath = substr($tmpPath, 6);
			$tmpPath && $attachInfo['path'] = $attachService->getThreadAttachMini($tmpPath);
			$attachInfo['attachurl'] = $tmpPath ? $tmpPath : $attachInfo['path'];
		}
	}	
	echo pwJsonEncode($attachInfo);
	ajax_footer();
}

function showExtraMsg($msg) {
	echo $msg;
	ajax_footer();
}
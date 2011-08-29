<?php
!defined('P_W') && exit('Forbidden');

define('AJAX', 1);
S::gp(array('type'));

reset($_FILES);
$filekey = key($_FILES);
$aid = substr($filekey, strpos($filekey, '_') + 1);

if (!$aid) {
	echo 'fail';ajax_footer();
}

$modifyattach = getAttModifyFactory($type, $aid);

if (($return = $modifyattach->check()) !== true) {
	showExtraMsg($return);
}
PwUpload::upload($modifyattach);

echo "ok\t" . $modifyattach->getAttachName();
ajax_footer();

function getAttModifyFactory($type, $aid) {
	if ($type == 'active') {
		L::loadClass('activeupload', 'upload', false);
		return new ActiveModify($aid);
	}
	if ($type == 'cms') {
		require_once(R_P . 'mode/cms/lib/upload/articleupload.class.php');
		return new ArticleModify($aid);
	}
	if ($type && file_exists(R_P . "require/extents/attach/{$type}modify.class.php")) {
		$class = $type . 'Modify';
		require_once S::escapePath(R_P . "require/extents/attach/{$type}modify.class.php");
		return new $class($aid);
	}
	L::loadClass('AttModify', 'upload', false);
	return new AttModify($aid);
}

function showExtraMsg($msg) {
	echo "fail\t" . getLangInfo('msg', $msg);
	ajax_footer();
}
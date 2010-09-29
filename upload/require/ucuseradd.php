<?php
!defined('P_W') && exit('Forbidden');

if (empty($detail) && GetCookie('ucuser')) {
	require_once(R_P . 'uc_client/uc_client.php');
	list($winduid, $md5pwd) = explode("\t",addslashes(StrCode(GetCookie('ucuser'),'DECODE')));
	$detail = uc_user_check($winduid, $md5pwd);
}
if ($detail['uid'] > 0) {
	$register = L::loadClass('Register', 'user');
	$register->appendUser($detail['uid'], $detail['username'], $detail['password'], $detail['email']);
	$detail = getUserByUid($detail['uid']);
}
?>
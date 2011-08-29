<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
!$tid && Showmsg('data_error');
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($winduid, false, false, true); //replyinfo
$rt['replyinfo'] = str_replace(",$tid,", ',', $rt['replyinfo']);
$rt['replyinfo'] == ',' && $rt['replyinfo'] = '';


$userService->update($winduid, array(), array(), array('replyinfo'=>$rt['replyinfo']));
//$db->update("UPDATE pw_threads SET ifmail='0' WHERE tid=" . S::sqlEscape($tid));
pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('ifmail'=>0));

if (getstatus($winddb['userstatus'], PW_USERSTATUS_NEWRP) && !$rt['replyinfo']) {
	$userService->setUserStatus($winduid, PW_USERSTATUS_NEWRP, false);
}
Showmsg('operate_success');

<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('touid'), 'GP', 2);
(!$winduid && !$touid) && Showmsg('undefined_action');

PostCheck();
if ($touid == $winduid) {
	Showmsg('undefined_action');
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$member = $userService->get($touid);//uid,username,icon
$errorname = $member['username'];
!$member && Showmsg('user_not_exists');

$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
if (($return = $attentionService->delFollow($winduid, $touid)) !== true) Showmsg($return);
$userCache = L::loadClass('UserCache', 'user'); /* @var $userCache PW_Usercache */
$userCache->delete($winduid, 'recommendUsers');
echo "success\t";
ajax_footer();




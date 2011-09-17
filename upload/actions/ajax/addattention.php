<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
S::gp(array('touid', 'recommend'), 'GP', 2);
(!$winduid && !$touid) && Showmsg('undefined_action');

PostCheck();
if ($touid == $winduid) {
	Showmsg('attention_self_add_error');
}
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$member = $userService->get($touid);//uid,username,icon
if (!$member) {
	$errorname = '';
	Showmsg('user_not_exists');
}

$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
if (($ifAttention = $attentionService->isFollow($winduid, $touid)) && !$recommend) {
	Showmsg('attention_already_exists');
}
if ($attentionService->isInBlackList($touid, $winduid)) {
	Showmsg('对方已设置隐私，您无法加为关注!');
}

if (!$ifAttention && ($return = $attentionService->addFollow($winduid, $touid)) !== true) {
	Showmsg($return);
}
if ($recommend) {
	$userCache = L::loadClass('UserCache', 'user');
	$userCache->delete($winduid, 'recommendUsers');
}
echo "success\t";
ajax_footer();


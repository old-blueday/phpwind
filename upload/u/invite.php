<?php
!defined('R_P') && exit('Forbidden');

//empty($o_invite) && Showmsg('mode_o_invite_close');
if($uid) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if (!$userService->isExist($uid)) Showmsg('invite_user_not_exist');
}

S::gp(array('hash','type','id'));

//验证hash的合法性
$hash != appkey($uid,$type) && Showmsg('mode_o_invite_hash_error');

Cookie('o_invite',"$uid\t$hash\t$type");

if($type == 'group') {
	ObHeader("apps.php?q=group&cyid=".$id);
} elseif ($type == 'groupactive') {
	require_once(R_P . 'apps/groups/lib/active.class.php');
	$newActive = new PW_Active();
	$cyid = $newActive->getCyidById($id);
	ObHeader("apps.php?q=group&a=active&job=view&cyid=$cyid&id=$id");
} else {
	ObHeader(USER_URL.$uid);
}

?>
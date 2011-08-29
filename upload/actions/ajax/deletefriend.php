<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array(
	'fuid'
), 'GP', 2);
$ckuser = $db->get_value("SELECT m.username FROM pw_friends f LEFT JOIN pw_members m ON f.uid=m.uid WHERE f.uid=" . S::sqlEscape($fuid) . " AND f.friendid=" . S::sqlEscape($winduid));
if ($ckuser) {
	$db->update('DELETE FROM pw_friends WHERE uid=' . S::sqlEscape($fuid) . " AND friendid=" . S::sqlEscape($winduid));
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$user = $userService->get($fuid);
	$user['f_num'] > 0 && $userService->updateByIncrement($fuid, array(), array('f_num' => -1));
	
	$ckuser2 = $db->get_value("SELECT friendid FROM pw_friends WHERE uid=" . S::sqlEscape($winduid) . " AND friendid=" . S::sqlEscape($fuid));
	if ($ckfuid2) {
		$db->update('DELETE FROM pw_friends WHERE uid=' . S::sqlEscape($winduid) . " AND friendid=" . S::sqlEscape($fuid));
		$user = $userService->get($winduid);
		$user['f_num'] > 0 && $userService->updateByIncrement($winduid, array(), array('f_num' => -1));
	}

	M::sendNotice(
		array($ckuser),
		array(
			'title' => getLangInfo('writemsg','friend_delete_title',array('username'=>$windid)),
			'content' => getLangInfo('writemsg','friend_delete_content',array('uid'=>$winduid,'username'=>$windid)),
		)
	);
	Showmsg('friend_delete');
} else {
	Showmsg('undefined_action');
}

<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('touid'), 'GP', 2);

if (!$winduid) Showmsg('undefined_action');
if (!$touid) Showmsg('undefined_action');

$check = $db->get_one("SELECT m.uid FROM pw_friends f LEFT JOIN pw_members m ON f.friendid=m.uid LEFT JOIN pw_memberdata md ON f.friendid=md.uid WHERE f.uid=" . S::sqlEscape($winduid)." AND f.friendid=" . S::sqlEscape($touid)." AND f.status=0");
if ($check) {
	$friendService = L::loadClass('friend', 'friend'); /* @var $friendService PW_Friend */
	if ($friendService->delFriend($winduid ,$touid)) {
		echo "success";
		ajax_footer();
	}
	Showmsg('undefined_action');
} else {
	Showmsg('undefined_action');
}

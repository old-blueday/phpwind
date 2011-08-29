<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('u'), 'P', 2);
if (!$u) Showmsg('undefined_action');
if ($u != $winduid) Showmsg('undefined_action');
$friends = getFriends($winduid, 0, 8, false, 1, 's');
$str = '';
if ($friends) {
	$friend_online = array();
	foreach ($friends as $key => $value) {
		if ($value['uid'] == $winduid) continue;
		if ($value['thisvisit'] + $db_onlinetime * 1.5 > $timestamp) {
			$friend_online[] = array(
				'uid' => $value['uid'],
				'face' => $value['face'],
				'username' => $value['username']
			);
		}
	}
	if ($friend_online) {
		$str = pwJsonEncode($friend_online);
	}
}
echo "success\t$str";
ajax_footer();

<?php
!defined('P_W') && exit('Forbidden');

if (empty($_POST['step'])) {
	S::gp(array('recall'));
	$friend = getFriends($winduid);
	if (empty($friend)) Showmsg('no_friend');
	foreach ($friend as $key => $value) {
		$frienddb[$value['ftid']][] = $value;
	}
	$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid) . " ORDER BY ftid");
	$friendtype = array();
	while ($rt = $db->fetch_array($query)) {
		$friendtype[$rt['ftid']] = $rt;
	}
	$no_group_name = getLangInfo('other', 'no_group_name');
	$friendtype[0] = array(
		'ftid' => 0,
		'uid' => $winduid,
		'name' => $no_group_name
	);
	require_once PrintEot('ajax');
	ajax_footer();
} else {
	S::gp(array('selid'));
	if ($selid) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$memebers = $userService->getByUserIds($selid);
		foreach ($memebers as $rt) {
			$usernamedb[] = $rt['username'];
		}
		$usernamedbs = implode(",", $usernamedb);
		echo $usernamedbs;
		ajax_footer();
	} else {
		echo '';
		ajax_footer();
	}
}

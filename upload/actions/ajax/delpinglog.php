<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'fid',
	'tid',
	'pid',
	'pingid'
), null, 2);

//权限
$foruminfo = $db->get_one('SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=' . pwEscape($fid));
!$foruminfo && Showmsg('data_error');
$isGM = $isBM = $admincheck = 0;
if ($groupid != 'guest') {
	$isGM = CkInArray($windid, $manager);
	$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
}

$pingdata = $db->get_one("SELECT * FROM pw_pinglog WHERE id=" . pwEscape($pingid));
!$pingdata && Showmsg('data_error');
!($admincheck || ($_G['markable'] && $pingdata['pinger'] == $windid)) && Showmsg('data_error');

$db->update("UPDATE pw_pinglog SET ifhide=1 WHERE id=" . pwEscape($pingid) . " LIMIT 1");
if ($db->affected_rows()) {
	echo "success";
	require_once R_P . 'require/pingfunc.php';
	update_markinfo($fid, $tid, $pid);
	# memcache reflesh
	if ($db_memcache) {
		$threads = L::loadClass('Threads', 'forum');
		$threads->delThreads($tid);
	}
} else {
	echo "data_error";
}
ajax_footer();

<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'fid',
	'tid',
	'pid',
	'pingid'
), null, 2);

//权限
$foruminfo = $db->get_one('SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=' . S::sqlEscape($fid));
!$foruminfo && Showmsg('data_error');
$isGM = $isBM = $admincheck = 0;
if ($groupid != 'guest') {
	$isGM = S::inArray($windid, $manager);
	$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
}

$pingdata = $db->get_one("SELECT * FROM pw_pinglog WHERE id=" . S::sqlEscape($pingid));
!$pingdata && Showmsg('data_error');
!($admincheck || ($_G['markable'] && $pingdata['pinger'] == $windid)) && Showmsg('data_error');

//$db->update("UPDATE pw_pinglog SET ifhide=1 WHERE id=" . S::sqlEscape($pingid) . " LIMIT 1");
pwQuery::update('pw_pinglog', 'id=:id  LIMIT 1', array($pingid), array('ifhide'=>1));	
if ($db->affected_rows()) {
	$pingService = L::loadClass("ping", 'forum');
	$pingTotal = $pingService->getPingLogAll($tid,$pid);
	$pingTotal = pwJsonEncode($pingTotal);
	echo "success\t$pingTotal";
	$pingService->update_markinfo($tid, $pid);
	# memcache reflesh
	if ($db_memcache) {
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tid);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$tid));		
	}
} else {
	echo "data_error";
}
ajax_footer();

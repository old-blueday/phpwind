<?php
!defined('P_W') && exit('Forbidden');

require_once (R_P . 'require/showimg.php');

S::gp(array(
	'page',
	'fid',
	'tid',
	'pid'
), null, 2);
$perpage = 10;
$creditnames = pwCreditNames();
//权限
$foruminfo = $db->get_one('SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=' . S::sqlEscape($fid));
!$foruminfo && Showmsg('data_error');
$isGM = $isBM = $admincheck = 0;
if ($groupid != 'guest') {
	$isGM = S::inArray($windid, $manager);
	$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
}

$count = $db->get_value("SELECT COUNT(*) FROM pw_pinglog WHERE  fid=" . S::sqlEscape($fid) . " AND tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid) . "  AND ifhide=0");
$total = ceil($count / $perpage);
if ($page < 2) $page = 2;
$offset = ($page - 1) * $perpage;

$ping_db = array();
$query = $db->query("SELECT a.*,b.uid,b.icon FROM pw_pinglog a LEFT JOIN pw_members b ON a.pinger=b.username WHERE a.fid=" . S::sqlEscape($fid) . " AND a.tid=" . S::sqlEscape($tid) . " AND a.pid=" . S::sqlEscape($pid) . " AND ifhide=0 ORDER BY a.pingdate DESC LIMIT $offset,$perpage");
while ($rt = $db->fetch_array($query)) {
	list($rt['pingtime'], $rt['pingdate']) = getLastDate($rt['pingdate']);
	$rt['record'] = $rt['record'] ? $rt['record'] : "-";
	if ($rt['point'] > 0) $rt['point'] = "+" . $rt['point'];
	$tmp = showfacedesign($rt['icon'], true);
	$rt['icon'] = $tmp[0];
	isset($creditnames[$rt['name']]) && $rt['name'] = $creditnames[$rt['name']];
	$ping_db[] = $rt;
}

require_once PrintEot('ajax');
ajax_footer();

<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('pid'));
S::gp(array('cyid', 'floor','uid'), 'GP', 2);

!$pid && $floor == 0 && $pid = 'tpc';
$linkurl = $cyid ? "apps.php?q=group&cyid=$cyid&a=read&tid=$tid" : "read.php?tid=$tid&displayMode=1";

if ($pid == 'tpc') {
	ObHeader("$linkurl#tpc");
}
$page = '';
if (empty($pid) && empty($floor)) {
	Showmsg('data_error');
}
$pw_posts = GetPtable('N', $tid);
$viewOrderType = GetCookie('rorder');
$viewOrderBy = $viewOrderType[$tid] == 'desc' ? 'desc' : 'asc';

if (!$pid && $floor) {
	$_totalSql = 'SELECT count(*) AS total FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid);
	$uid > 0 && $_totalSql = 'SELECT count(*) AS total FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid) . ' AND authorid=' . S::sqlEscape($uid);
	$totalPosts = $db->get_value($_totalSql);
	if ($totalPosts < $floor) {
		$lastPosition = $totalPosts - 1;
		$_lastSql = 'SELECT pid FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid) . ' ORDER BY postdate ' . $viewOrderBy . S::sqlLimit($lastPosition, 1);
		$uid > 0 && $_lastSql = 'SELECT pid FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid) . ' AND authorid=' . S::sqlEscape($uid) . ' ORDER BY postdate ' . $viewOrderBy . S::sqlLimit($lastPosition, 1);
		$pid = $db->get_value($_lastSql);
	} else {
		$startPosition = $viewOrderType[$tid] == 'desc' ? ($totalPosts - $floor) : ($floor - 1);
		$_sql = 'SELECT pid FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid) . ' ORDER BY postdate ' . $viewOrderBy . S::sqlLimit($startPosition, 1);
		$uid > 0 && $_sql = 'SELECT pid FROM ' . $pw_posts . ' WHERE tid=' . S::sqlEscape($tid) . ' AND authorid=' . S::sqlEscape($uid) . ' ORDER BY postdate ' . $viewOrderBy . S::sqlLimit($startPosition, 1);
		$pid = $db->get_value($_sql);
	}
	!$pid && ObHeader("$linkurl&uid=$uid#tpc");
}
$postdb = $db->get_one("SELECT p.postdate,p.ifreward,t.special,f.forumset FROM $pw_posts p LEFT JOIN pw_threads t ON p.tid=t.tid LEFT JOIN pw_forumsextra f ON p.fid=f.fid WHERE p.pid=" . S::sqlEscape($pid));
!$postdb && Showmsg('data_error');
$postdate = $postdb['postdate'];
$forumset = unserialize($postdb['forumset']);
$special = $postdb['special'];
$ifreward = $postdb['ifreward'];
if ($winddb['p_num']) {
	$db_readperpage = $winddb['p_num'];
} elseif ($forumset['readnum']) {
	$db_readperpage = $forumset['readnum'];
}
$numCondition = $viewOrderBy == 'desc' ? '>=' : '<=';
$addCondition = $viewOrderBy == 'desc' ? '<=' : '>=';
$authorCondition = $uid > 0 ? ' AND authorid=' . S::sqlEscape($uid) : '';
if ($special) {
	if ($ifreward == 1) {
		$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND postdate" . $numCondition . S::sqlEscape($postdate) . " AND ifreward<>'0'" . $authorCondition);
		$postsnumadd = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND postdate" . $addCondition . S::sqlEscape($postdate) . " AND ifreward='2'" . $authorCondition);
		$postsnum = $postsnum + $postsnumadd;
	} elseif ($ifreward == 2) {
		$page = 1;
	} else {
		$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND postdate" . $numCondition . S::sqlEscape($postdate) . $authorCondition);
		$postsnumadd = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND postdate" . $addCondition . S::sqlEscape($postdate) . " AND ifreward<>'0'" . $authorCondition);
		$postsnum = $postsnum + $postsnumadd;
	}
} else {
	$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND postdate" . $numCondition . S::sqlEscape($postdate) . $authorCondition);
}
//* $threads = L::loadClass('Threads', 'forum');
//* $read = $threads->getThreads($tid,true);

$_cacheService = Perf::gatherCache('pw_threads');
$read = $_cacheService->getThreadAndTmsgByThreadId($tid);

$topNum = $read['topreplays'] ? $read['topreplays'] : 0;
empty($page) && $page = ceil(($postsnum + $topNum + 1) / $db_readperpage);
$headerUrl = $uid > 0 ? "$linkurl&page=$page&uid=$uid#$pid" : "$linkurl&page=$page#$pid";
ObHeader($headerUrl);
?>
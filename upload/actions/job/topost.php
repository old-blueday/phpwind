<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'pid',
	'cyid'
));
$linkurl = $cyid ? "apps.php?q=group&cyid=$cyid&a=read&tid=$tid" : "read.php?tid=$tid";
if ($pid == 'tpc' && is_numeric($tid)) {
	ObHeader("$linkurl#tpc");
}
$page = '';
if (!is_numeric($tid) || !is_numeric($pid)) {
	Showmsg('data_error');
}

$pw_posts = GetPtable('N', $tid);
$postdb = $db->get_one("SELECT p.postdate,p.ifreward,t.special,f.forumset FROM $pw_posts p LEFT JOIN pw_threads t ON p.tid=t.tid LEFT JOIN pw_forumsextra f ON p.fid=f.fid WHERE p.pid=" . pwEscape($pid));
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

if ($special) {
	if ($ifreward == 1) {
		$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND postdate<=" . pwEscape($postdate) . " AND ifreward<>'0'");
		$postsnumadd = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND postdate>" . pwEscape($postdate) . " AND ifreward='2'");
		$postsnum = $postsnum + $postsnumadd;
	} elseif ($ifreward == 2) {
		$page = 1;
	} else {
		$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND postdate<=" . pwEscape($postdate));
		$postsnumadd = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND postdate>" . pwEscape($postdate) . " AND ifreward<>'0'");
		$postsnum = $postsnum + $postsnumadd;
	}
} else {
	$postsnum = $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND postdate<=" . pwEscape($postdate));
}
empty($page) && $page = ceil(($postsnum + 1) / $db_readperpage);
ObHeader("$linkurl&page=$page#$pid");

<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
$rs = $db->get_one('SELECT tids,type FROM pw_favors WHERE uid=' . pwEscape($winduid));
if ($rs) {
	$count = 0;
	$tiddb = getfavor($rs['tids']);
	foreach ($tiddb as $key => $t) {
		if (is_array($t)) {
			if (CkInArray($tid, $t)) {
				Showmsg('job_favor_error');
			}
			$count += count($t);
		} else {
			unset($tiddb[$key]);
		}
	}
	$count > $_G['maxfavor'] && Showmsg('job_favor_full');
	
	InitGP(array(
		'type'
	), GP, 2);
	$typeid = array(
		'0' => 'default'
	);
	if ($rs['type']) {
		$typeid = array_merge($typeid, explode(',', $rs['type']));
		if (!isset($type)) {
			require_once PrintEot('ajax');
			ajax_footer();
		}
	} else {
		$type = 0;
	}
	!isset($typeid[$type]) && Showmsg('data_error');
	$read = $db->get_one('SELECT subject FROM pw_threads WHERE tid=' . pwEscape($tid));
	!$read && Showmsg('data_error');
	require_once (R_P . 'require/posthost.php');
	PostHost("http://push.phpwind.net/push.php?type=collect&url=" . rawurlencode("$db_bbsurl/read.php?tid=$tid") . "&tocharset=$db_charset&title=" . rawurlencode($read['subject']) . "&bbsname=" . rawurlencode($db_bbsname), "");
	$tiddb[$type][] = $tid;
	$newtids = makefavor($tiddb);
	$db->update("UPDATE pw_favors SET tids=" . pwEscape($newtids) . ' WHERE uid=' . pwEscape($winddb['uid']));
} else {
	$db->update("INSERT INTO pw_favors SET " . pwSqlSingle(array(
		'uid' => $winddb['uid'],
		'tids' => $tid
	)));
}

$db->update("UPDATE pw_threads SET favors=favors+1 WHERE tid=" . pwEscape($tid));

/*--- pwcache ---start*/
L::loadClass('elementupdate', '', false);
$elementupdate = new ElementUpdate();
$elementupdate->newfavorUpdate($tid, $fid);
if ($db_ifpwcache & 1024) {
	$elementupdate->hotfavorUpdate($tid, $fid);
}
updateDatanalyse($tid, 'threadFav', 1);
/*--- pwcache ---end*/
Showmsg('job_favor_success');

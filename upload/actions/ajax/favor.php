<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array('tid','fid'));
//$rs = $db->get_one('SELECT tids,type FROM pw_favors WHERE uid=' . S::sqlEscape($winduid));
$rts = array();
$result = $db->query("SELECT typeid FROM pw_collection WHERE type = 'postfavor' AND typeid = " . S::sqlEscape($tid) . " AND uid = " . S::sqlEscape($winduid));
while ($rt = $db->fetch_array($result)) {
	$rts[] = $rt['typeid'];
}
$rs['tids'] = implode(',',$rts);
$rs['type'] = '';
if ($rs['tids']) {
	$count = 0;
	$tiddb = getfavor($rs['tids']);
	foreach ($tiddb as $key => $t) {
		if (is_array($t)) {
			if (S::inArray($tid, $t)) {
				Showmsg('job_favor_error');
			}
			$count += count($t);
		} else {
			unset($tiddb[$key]);
		}
	}
	$count > $_G['maxfavor'] && Showmsg('job_favor_full');
	
	S::gp(array('type'), GP, 2);
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
	$read = $db->get_one('SELECT subject FROM pw_threads WHERE tid=' . S::sqlEscape($tid));
	!$read && Showmsg('data_error');
	require_once (R_P . 'require/posthost.php');
	PostHost("http://push.phpwind.net/push.php?type=collect&url=" . rawurlencode("$db_bbsurl/read.php?tid=$tid") . "&tocharset=$db_charset&title=" . rawurlencode($read['subject']) . "&bbsname=" . rawurlencode($db_bbsname), "");
	$tiddb[$type][] = $tid;
	$newtids = makefavor($tiddb);
//	$db->update("UPDATE pw_favors SET tids=" . S::sqlEscape($newtids) . ' WHERE uid=' . S::sqlEscape($winddb['uid']));
	$db->update("UPDATE pw_collection SET typeid=" . S::sqlEscape($newtids) . " WHERE type = 'postfavor' AND typeid = " . S::sqlEscape($tid) . " AND uid = " . S::sqlEscape($winddb['uid']));
} else {
	$_cacheService = Perf::gatherCache('pw_threads');
	$favor = $_cacheService->getThreadByThreadId($tid);
	empty($favor) && Showmsg('data_error');
	$collection['uid'] = $favor['authorid'];
	$collection['lastpost'] = $favor['lastpost'];
	$collection['link'] = $db_bbsurl.'/read.php?tid='.$tid;
	$collection['postfavor']['subject'] = $favor['subject'];
	$collectionDate = array(
					'typeid'	=> 	$tid,
					'type'		=> 	'postfavor',
					'uid'		=>	$winduid,
					'username'	=> $windid,
					'content'	=>	serialize($collection),
					'postdate'	=>	$timestamp
				);
	$collectionService = L::loadClass('Collection', 'collection');
	$collectionService->insert($collectionDate);
}

//$db->update("UPDATE pw_threads SET favors=favors+1 WHERE tid=" . S::sqlEscape($tid));
pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('favors=favors+1')));
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

<?php
!defined('P_W') && exit('Forbidden');

!$winduid && Showmsg('undefined_action');
require_once (R_P . 'require/forum.php');
S::gp(array(
	'ifmsg',
	'type'
));

$rt = $db->get_one('SELECT r.*,t.fid,t.author,t.authorid,t.postdate,t.fid,t.subject,t.ptable,t.special,t.state,f.forumadmin,f.fupadmin FROM pw_reward r LEFT JOIN pw_threads t ON r.tid=t.tid LEFT JOIN pw_forums f ON t.fid=f.fid WHERE r.tid=' . S::sqlEscape($tid));

if (empty($rt) || $rt['special'] != 3 || $rt['state'] != 0) {
	Showmsg('illegal_tid');
}
$fid = $rt['fid'];
$authorid = $rt['authorid'];
$author = $rt['author'];
$pw_posts = GetPtable($rt['ptable']);

if ($groupid != '3' && $groupid != '4' && !admincheck($rt['forumadmin'], $rt['fupadmin'], $windid)) {
	Showmsg('mawhole_right');
}
if (empty($_POST['step'])) {
	
	require_once (R_P . 'require/header.php');
	require_once PrintEot('reward');
	footer();

} else {
	
	PostCheck();
	require_once (R_P . 'require/credit.php');
	//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
	pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
	
	if ($type == '1') {
		//$db->update("UPDATE pw_threads SET state='2' WHERE tid=" . S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('state'=>2));
		$credit->addLog('reward_return', array(
			$rt['cbtype'] => $rt['cbval'] * 2
		), array(
			'uid' => $authorid,
			'username' => $author,
			'ip' => $onlineip,
			'fname' => $forum[$fid]['name']
		));
		$credit->set($authorid, $rt['cbtype'], $rt['cbval'] * 2);
	} else {
		if ($timestamp < $rt['timelimit'] && $groupid != '3' && $groupid != '4') {
			Showmsg('reward_time_limit');
		}
		//$db->update("UPDATE pw_threads SET state='3' WHERE tid=" . S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('state'=>3));
	}
	return_value($tid, $rt['catype'], $rt['caval']);
	
	if ($ifmsg) {
		if ($type == '1') {
			$affect = $credit->cType[$rt['cbtype']] . ":" . ($rt['cbval'] * 2);
		} else {
			$affect = '';
		}
		
		M::sendNotice(
			array($rt['author']),
			array(
				'title' => getLangInfo('writemsg','endreward_title_' . $type),
				'content' => getLangInfo('writemsg','endreward_content_' . $type,array(
					'manager' => $windid,
					'fid' => $fid,
					'tid' => $tid,
					'subject' => $rt['subject'],
					'postdate' => get_date($rt['postdate']),
					'forum' => $forum[$fid]['name'],
					'affect' => $affect,
					'admindate' => get_date($timestamp),
					'reason' => 'None'
				)),
			)
		);
	}
	refreshto("read.php?tid=$tid&ds=1", 'operate_success');
}

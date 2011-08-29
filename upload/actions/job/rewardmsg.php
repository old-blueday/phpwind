<?php
!defined('P_W') && exit('Forbidden');

!$winduid && Showmsg('undefined_action');
$rt = $db->get_one('SELECT r.timelimit,t.fid,t.subject,t.authorid,t.postdate,t.special,t.state,f.forumadmin FROM pw_reward r LEFT JOIN pw_threads t ON r.tid=t.tid LEFT JOIN pw_forums f ON t.fid=f.fid WHERE r.tid=' . S::sqlEscape($tid));

if (empty($rt) || $rt['timelimit'] > $timestamp || $rt['special'] != 3 || $rt['state'] != 0) {
	Showmsg('illegal_tid');
}
$rt['authorid'] != $winduid && Showmsg('reward_noright');
!$rt['forumadmin'] && Showmsg('reward_no_forumadmin');
//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
$admin_db = explode(',', substr($rt['forumadmin'], 1, -1));

M::sendRequest(
	$winduid,
	array($admin_db),
	array(
		'create_uid' => $winduid,
		'create_username' => $windid,
		'title' => getLangInfo('writemsg','rewardmsg_title'),
		'content' => getLangInfo('writemsg','rewardmsg_content',array(
			'fid' => $rt['fid'],
			'tid' => $tid,
			'subject' => $rt['subject'],
			'postdate' => get_date($rt['postdate']),
			'forum' => $forum[$rt['fid']]['name'],
			'admindate' => get_date($timestamp),
			'reason' => "None"
		)),
	)
);
Showmsg('rewardmsg_success');

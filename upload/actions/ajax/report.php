<?php
!defined('P_W') && exit('Forbidden');

!$_G['allowreport'] && Showmsg('report_right');
InitGP(array(
	'tid',
	'pid'
), 'GP', '2');
InitGP(array(
	'type',
	'own'
));


if (is_int($own)) {
	$ifown = $winduid == $own ? '1' : '0';
} else {
	$ifown = $windid == $own ? '1' : '0';
}
$ifown && Showmsg('report_own');

$checkdata = $db->get_one("SELECT * FROM pw_report WHERE type=" . pwEscape($type) . " AND tid=" . pwEscape($tid) . " AND pid=" . pwEscape($pid));
$checkdata && Showmsg('have_report');

if (empty($_POST['step'])) {
	$ch_type = getLangInfo('other', $type);
	require_once PrintEot('ajax');
	ajax_footer();
} else {
	InitGP(array(
		'reason','ifsendmessage'
	));
	$pwSQL = pwSqlSingle(array(
		'tid' => $tid,
		'pid' => $pid,
		'uid' => $winduid,
		'type' => $type,
		'reason' => $reason
	));
	if ($ifsendmessage) {
		$usernames = getSendToUsernames($type, $tid);
		$url = parseReportUrl($type,$pid,$tid); 
		$mcontent = getLangInfo('writemsg', 'report_content_0_0',
									array(
										'type'=>getLangInfo('other',$type),
										'admindate'=>get_date($timestamp),
										'reason'=> $reason,
										'url' => $db_bbsurl."/".$url,
									)
								);
		M::sendNotice($usernames, array('title' => getLangInfo('writemsg', 'report_title'), 'content' => $mcontent));	
	}
	
	$db->update("INSERT INTO pw_report SET $pwSQL");
	Showmsg('report_success');
}


function getSendToUsernames($type,$tid) {
	global $windid;
	$usernames = array();
	if (!$type || !$tid) return $usernames;
	switch ($type) {
		case 'topic' :
			$threadsService = L::loadClass('threads', 'forum'); /* @var $threadsService PW_threads */
			$threads  = $threadsService->getThreads($tid);
			$fid = $threads['fid'];
			require_once(R_P.'lib/forum/forum.class.php');
			$forumService = new PwForum($fid);
			$foruminfo = $forumService->foruminfo;
			$forumadmins = $foruminfo['forumadmin'];
			$forumadmins = explode(',',$forumadmins);
			foreach ($forumadmins as $forumadmin) {
				if (!$forumadmin || $forumadmin == $windid) continue;
				$usernames[] = $forumadmin;
			}
		break;
		
		default :
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */ 
			$userInfos = $userService->getByGroupId('3');
			foreach ($userInfos as $user) {
				if (!$user || $user['username'] == $windid) continue;
				$usernames[] = $user['username'];
			}
		break;
	}
	return $usernames;
}

function parseReportUrl($type, $pid, $tid) {
	global $db_bbsurl;
	$url = "";
	switch ($type) {
		case 'topic':
			$url = $pid ? "job.php?action=topost&tid=".$tid."&pid=".$pid : "read.php?tid=".$tid;
			break;
		case 'grouptopic':
			$url = $pid ? 'job.php?action=topost&tid='.$tid.'&pid='.$pid : 'read.php?tid='.$tid;
			break;
		case 'diary':
			$url = "apps.php?q=diary&uid=".$pid."&a=detail&did=".$tid;
			break;
		case 'photo':
			$url = 'apps.php?q=photos&a=view&uid='.$pid.'&pid='.$tid;
			break;
		case 'group':
			$url = 'apps.php?q=group&cyid='.$tid;
			break;
		case 'groupphoto':
			$url = 'apps.php?q=galbum&a=view&cyid='.$pid.'&pid='.$tid;
			break;
		case 'user':
			$url = 'u.php?uid='.$tid;
			break;
		default :
			$url = $pid ? "job.php?action=topost&tid=".$tid."&pid=".$pid : "read.php?tid=".$tid;
			break;
	}
	return $url;
}

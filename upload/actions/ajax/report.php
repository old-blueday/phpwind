<?php
!defined('P_W') && exit('Forbidden');
!$_G['allowreport'] && Showmsg('report_right');
S::gp(array('tid','pid'), 'GP', '2');
S::gp(array('type','own'));
$ifown = is_int($own) ? ($winduid == $own ? '1' : '0') : ($windid == $own ? '1' : '0');
$ifown && Showmsg('report_own');
$checkdata = $db->get_one("SELECT * FROM pw_report WHERE type=" . S::sqlEscape($type) . " AND tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid));
$checkdata && Showmsg('have_report');
if (empty($_POST['step'])) {
	$ch_type = getLangInfo('other', $type);
	require_once PrintEot('ajax');
	ajax_footer();
} else {
	S::gp(array('reason','ifsendmessage'));
	$pwSQL = S::sqlSingle(array('tid' => $tid,'pid' => $pid,'uid' => $winduid,'type' => $type,'reason' => $reason));
	if ($ifsendmessage) {
		$usernames = getSendToUsernames($type, $tid);
		if (!empty($usernames)) {
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
	}
	$db->update("INSERT INTO pw_report SET $pwSQL");
	Showmsg('report_success');
}

function getSendToUsernames($type,$tid) {
	global $windid, $db;
	$usernames = array();
	if (!$type || !$tid) return $usernames;
	$remindUsernames = $db->get_value("SELECT db_value FROM pw_config WHERE db_name = 'report_remind'");
	$remindUsernames = $remindUsernames ? unserialize($remindUsernames) : array();
	foreach ($remindUsernames as $key => $value) {
		if ($value['username'] == $windid) continue;
		$usernames[] = $value['username'];
	}
	if ($type != 'topic') return $usernames;
	$_cacheService = Perf::gatherCache('pw_threads');
	$threads = $_cacheService->getThreadByThreadId($tid);			
	$fid = $threads['fid'];
	L::loadClass('forum', 'forum', false);
	$forumService = new PwForum($fid);
	$foruminfo = $forumService->foruminfo;
	$forumadmins = $foruminfo['forumadmin'];
	$forumadmins = explode(',',$forumadmins);
	foreach ($forumadmins as $forumadmin) {
		if (!$forumadmin || $forumadmin == $windid) continue;
		$usernames[] = $forumadmin;
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
			$url = USER_URL.$tid;
			break;
		default :
			$url = $pid ? "job.php?action=topost&tid=".$tid."&pid=".$pid : "read.php?tid=".$tid;
			break;
	}
	return $url;
}

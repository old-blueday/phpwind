<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
!$_G['dig'] && Showmsg("dig_right");
$read = $db->get_one("SELECT t.author,t.subject,t.dig,f.forumset FROM pw_threads t LEFT JOIN pw_forumsextra f USING(fid) WHERE tid=" . S::sqlEscape($tid));
!$read && Showmsg('data_error');
$forumset = unserialize($read['forumset']);
!$forumset['dig'] && Showmsg('forum_dig_allow');

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($winduid, false, false, true); //uid,digtid
S::slashes($rt);
if (strpos(",$rt[digtid],", ",$tid,") === false) {
	$read['dig']++;
	//$db->update("UPDATE pw_threads SET dig=dig+1 WHERE tid=" . S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('dig=dig+1')));
	if ($rt) {
		strlen($rt['digtid']) > 2000 && $rt['digtid'] = '';
		$rt['digtid'] .= ($rt['digtid'] ? ',' : '') . $tid;
		$userService->update($winduid, array(), array(), array('digtid'=>$rt['digtid']));
	} else {
		$userService->update($winduid, array(), array(), array('digtid'=>$tid));
	}
	//reflush cache
	//* $threads = L::loadClass('Threads', 'forum');
	//* $threads->delThreads($tid);
	
	require_once (R_P . 'require/posthost.php');
	PostHost("http://push.phpwind.net/push.php?type=dig&url=" . rawurlencode("$db_bbsurl/read.php?tid=$tid") . "&tocharset=$db_charset&title=" . rawurlencode($read['subject']) . "&bbsname=" . rawurlencode($db_bbsname), "");
	Showmsg('dig_success');
} else {
	Showmsg("dig_limit");
}

<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('tid', 'fid'), 'GP', 2);
if ($tid < 1 || $fid < 1) quickPostMessage('undefined_action');

L::loadClass('forum', 'forum', false);
$pwforum = new PwForum($fid);
if (!$pwforum->isForum()) quickPostMessage('data_error');

list($isGM, $isBM, $forumset, $foruminfo) = array(S::inArray($windid, $manager), $pwforum->isBM($windid), $pwforum->forumset, $pwforum->foruminfo);
$cacheService = Perf::gatherCache('pw_threads');
$read = $cacheService->getThreadByThreadId($tid);
if (!$read) quickPostMessage('illegal_tid');

list($tpc_locked, $admincheck)  = array(($read['locked'] % 3 <> 0) ? 1 : 0, ($isGM || $isBM) ? 1 : 0);
//实名认证权限
if ($db_authstate && !$admincheck && $forumset['auth_allowrp'] && true !== ($authMessage = $pwforum->authStatus($winddb['userstatus'],$forumset['auth_logicalmethod']))) {
	quickPostMessage($authMessage . '_rp');
}
//$isAuthStatus = $isGM || (!$forumset['auth_allowrp'] || $pwforum->authStatus($winddb['userstatus'], $forumset['auth_logicalmethod']) === true);
if ((!$tpc_locked || $SYSTEM['replylock']) && ($admincheck || $pwforum->allowreply($winddb, $groupid))) {
	if (!$admincheck && !$foruminfo['allowrp'] && !$_G['allowrp']) quickPostMessage('reply_group_right');
	require_once PrintEot('quickpost');
	ajax_footer();
}
quickPostMessage('reply_group_right');

function quickPostMessage($message) {
	$message = getLangInfo('msg', $message);
	echo $message;
	ajax_footer();
}
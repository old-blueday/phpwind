<?php
!defined('P_W') && exit('Forbidden');

if ($db_replysitemail && getstatus($winddb['userstatus'], PW_USERSTATUS_NEWRP)) {
	//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
	pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->get($winduid, false, false, true); //replyinfo
	$replyinfo = explode(',', substr($rt['replyinfo'], 1, -1));
	$replyinfo = S::sqlImplode($replyinfo);
	$replydb = array();
	$query = $db->query("SELECT tid,fid,subject,postdate,lastpost FROM pw_threads WHERE tid IN($replyinfo) LIMIT 20");
	if ($db->num_rows($query) == 0) {
		Showmsg('newrp_error');
	}
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'], 55);
		$rt['fname'] = $forum[$rt['fid']]['name'];
		$rt['lastpost'] = get_date($rt['lastpost'], 'Y-m-d');
		$replydb[] = $rt;
	}
	require_once PrintEot('ajax');
	ajax_footer();

} else {
	Showmsg('newrp_error');
}

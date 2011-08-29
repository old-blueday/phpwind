<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('tid', 'pids'));

if (empty($tid) || empty($pids)) {
	echo 'fail';ajax_footer();
}
$pidArr = array_unique(explode(',', $pids));
$readdb = $_pids = array();

//* $threadService = L::loadClass('threads', 'forum');
//* $read = $threadService->getThreads($tid, in_array('tpc', $pidArr));
$_cacheService = Perf::gatherCache('pw_threads');
$read = (in_array('tpc', $pidArr)) ? $_cacheService->getThreadAndTmsgByThreadId($tid) : $_cacheService->getThreadByThreadId($tid);
if (empty($read)) {
	echo 'fail';ajax_footer();
}
if (!($foruminfo = L::forum($read['fid']))) {
	echo 'fail';ajax_footer();
}
extract(L::style());

$ptable = $read['ptable'];
$forumset = $foruminfo['forumset'];
list(,,$downloadmoney,$downloadimg) = explode("\t",$forumset['uploadset']);

$isGM = S::inArray($windid,$manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
$admincheck = ($isGM || $isBM) ? 1 : 0;
if (!$isGM) {#非创始人权限获取
	$pwSystem = pwRights($isBM);
	if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'] || $pwSystem['unite'] || $pwSystem['pingcp'] || $pwSystem['areapush'])) {
		$managecheck = 1;
	}
	$pwPostHide = $pwSystem['posthide'];
	$pwSellHide = $pwSystem['sellhide'];
	$pwEncodeHide = $pwSystem['encodehide'];
} else {
	$managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
}

if (in_array('tpc', $pidArr)) {
	$read['pid'] = 'tpc';
	$readdb[] = $read;
	$read['aid'] && $_pids['tpc'] = 0;
	$pidArr = array_diff($pidArr, array('tpc'));
}

if ($pidArr) {
	$pw_posts = GetPtable($ptable);
	$query = $db->query("SELECT * FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND ifcheck='1' AND pid IN(" . S::sqlImplode($pidArr) . ')');
	while ($read = $db->fetch_array($query)) {
		$readdb[] = $read;
		$read['aid'] && $_pids[$read['pid']] = $read['pid'];
	}
}
require_once(R_P . 'require/bbscode.php');

if ($_pids) {
	$attachShow = new attachShow(($isGM || $pwSystem['delattach']), $forumset['uploadset'], $forumset['viewpic']);
	$attachShow->init($tid, $_pids);
}
foreach ($readdb as $key => $read) {
	$readdb[$key] = viewread($read);
}
$GLOBALS += L::style('');
require_once PrintEot('readfloor');
ajax_footer();

function viewread($read) {
	global $winduid,$isGM,$pwSystem,$_G,$db_windpost,$tpc_buy,$tpc_pid,$tpc_tag,$tpc_author,$tid;
	$tpc_buy = $read['buy'];
	$tpc_pid = $read['pid'];
	$tpc_tag = NULL;
	$tpc_author = '';
	if ($read['anonymous']) {
		$anonymous = (!$isGM && $winduid != $read['authorid'] && !$pwSystem['anonyhide']);
	} else {
		$anonymous = false;
	}
	if (!$anonymous) {
		$tpc_author = $read['author'];
	}
	$read['ifsign']<2 && $read['content'] = str_replace("\n", "<br />", $read['content']);
	$read['leaveword'] && $read['content'] .= leaveword($read['leaveword'],$read['pid']);
	if ($read['ifwordsfb'] != $GLOBALS['db_wordsfb']) {
		$read['content'] = wordsConvert($read['content'], array(
			'id'	=> ($tpc_pid == 'tpc') ? $tid : $tpc_pid,
			'type'	=> ($tpc_pid == 'tpc') ? 'topic' : 'posts',
			'code'	=> $read['ifwordsfb']
		));
	}
	$read['content'] = convert($read['content'], $db_windpost);
	
	if ($read['aid'] && $GLOBALS['attachShow']->isShow($read['ifhide'], $tid)) {
		$read += $GLOBALS['attachShow']->parseAttachs($read['pid'], $read['content'], $winduid == $read['authorid']);
	}
	return $read;
}
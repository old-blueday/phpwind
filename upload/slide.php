<?php
define('SCR','read');
require_once('global.php');
L::loadClass('forum', 'forum', false);
require_once(R_P.'require/bbscode.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/cache_read.php',true);
pwCache::getData(D_P.'data/bbscache/cache_read.php');
S::gp(array('tid'));

if (Perf::checkMemcache()) {
	$_cacheService = Perf::getCacheService();
	$_thread = $_cacheService->get('thread_tid_' . $tid);
	$_thread && $_tmsg = $_cacheService->get('thread_tmsg_tid_' . $tid);
	$read = ($_thread && $_tmsg) ? array_merge($_thread, $_tmsg) : false;
	if (!$read) {
		$_cacheService = Perf::gatherCache('pw_threads');
		$read = ($page>1) ? $_cacheService->getThreadByThreadId($tid) : $_cacheService->getThreadAndTmsgByThreadId($tid);	
	}
} else {
	$read = $db->get_one("SELECT t.* ,tm.* FROM pw_threads t LEFT JOIN ".S::sqlMetadata(GetTtable($tid))." tm ON t.tid=tm.tid WHERE t.tid=" . S::sqlEscape($tid));
}
!$read && Showmsg('illegal_tid');

$pwforum = new PwForum($read['fid']);
if (!$pwforum->isForum()) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$forumset =& $pwforum->forumset;

if (!S::inArray($windid, $manager)) {
	$pwforum->forumcheck($winddb, $groupid);
}

if (!$foruminfo['allowvisit'] && $_G['allowread']==0 && $_COOKIE) {
	Showmsg('read_group_right');
}

/**************************************/

//帖子浏览及管理权限
$isGM = $isBM = $admincheck = $managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
$pwSystem = array();
if ($groupid != 'guest') {
	$isGM = S::inArray($windid,$manager);
	$isBM = $pwforum->isBM($windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
	if (!$isGM) {#非创始人权限获取
		$pwSystem = pwRights($isBM);
		if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'] || $pwSystem['unite'] || $pwSystem['pingcp'] || $pwSystem['areapush'] || $pwSystem['split'])) {
			$managecheck = 1;
		}
		$pwPostHide = $pwSystem['posthide'];
		$pwSellHide = $pwSystem['sellhide'];
		$pwEncodeHide = $pwSystem['encodehide'];
	} else {
		$managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
	}
}

//版块查看权限
if ($foruminfo['allowread'] && !$admincheck && !allowcheck($foruminfo['allowread'],$groupid,$winddb['groups'])) {
	Showmsg('forum_read_right');
}
if (!$admincheck) {
	$pwforum->creditcheck($winddb, $groupid);#积分限制浏览
	$pwforum->sellcheck($winduid);#出售版块
}
if ($read['ifcheck'] == 0 && !$isGM && $windid != $read['author'] && !$pwSystem['viewcheck']) {
	Showmsg('read_check');
}
if ($read['locked']%3==2 && !$isGM && !$pwSystem['viewclose']) {
	Showmsg('read_locked');
}
unset($S_sql,$J_sql,$foruminfo['forumset']);

//来自群组的帖子
if ($colony && (!$colony['ifopen'] && !$admincheck && (!$colony['ifcyer'] || $colony['ifadmin'] == -1))) {
	Showmsg('该群组话题内容仅对成员开放!');
}
//是否图酷、是否允许浏览
$isTucool = $forumset['iftucool'] && getstatus($read['tpcstatus'], 5);
$ifhide = ($read['ifhide'] && !ifpost($tid)) ? 1 : 0;
$isAllowViewPic = $admincheck || ($read['authorid'] == $winduid) || (!$ifhide && ($winduid || !$forumset['viewpic']));
(!$isTucool || !$isAllowViewPic) && ObHeader("read.php?tid=$tid&displayMode=1");

//禁言、屏蔽
$userService = L::loadClass('UserService', 'user');
$userInfo = $userService->get($read['authorid'],true,false,false);
$ifshieldThread = (($read['ifshield'] || ($userInfo['groupid'] == 6 && $db_shield)) && !$isGM)? 0 : 1;
!$ifshieldThread && ObHeader("read.php?tid=$tid&displayMode=1");

$attachsService = L::loadClass('Attachs', 'forum');
$tucoolAttachs = $attachsService->getSlidesByTidAndUid($tid,$read['authorid']);
!$tucoolAttachs && ObHeader("read.php?tid=$tid&displayMode=1");

//更新帖子点击
if ($db_hits_store == 0){
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('hits=hits+1')));	
}elseif ($db_hits_store == 1){
	$db->update('UPDATE pw_hits_threads SET hits=hits+1 WHERE tid='.S::sqlEscape($tid)); 
}elseif ($db_hits_store == 2){
	pwCache::writeover(D_P.'data/bbscache/hits.txt',$tid."\t", 'ab');
} 

//帖子浏览记录
$readlog = str_replace(",$tid,",',',GetCookie('readlog'));
$readlog.= ($readlog ? '' : ',').$tid.',';
substr_count($readlog,',')>11 && $readlog = preg_replace("/[\d]+\,/i",'',$readlog,3);
Cookie('readlog',$readlog);

require_once PrintEot('slide');
pwOutPut();

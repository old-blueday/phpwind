<?php
!defined('P_W') && exit('Forbidden');
$USCR = 'user_stopic';
//from header.php
if (file_exists(D_P."data/style/{$tplpath}_css.htm")) {
	$css_path = D_P."data/style/{$tplpath}_css.htm";
} else {
	$css_path = D_P.'data/style/wind_css.htm';
}

require_once(R_P.'require/forum.php');

S::gp(array('tid'), null , 2);
S::gp(array('job'));

$job = 'showtpc';

//* $threads = L::loadClass('Threads', 'forum');
//* $read = $threads->getThreads($tid);
$_cacheService = Perf::gatherCache('pw_threads');
$read = $_cacheService->getThreadByThreadId($tid);
!$read && stopic_topic_view_err('illegal_tid');

$fid = $read['fid'];
$ptable = $read['ptable'];
$ifcheck = $read['ifcheck'];
$pw_posts = GetPtable($ptable);

//读取版块信息及权限判断
if (!($foruminfo = L::forum($fid))) {
	$foruminfo	= $db->get_one("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE f.fid=".S::sqlEscape($fid));
	if ($foruminfo) {
		$foruminfo['creditset'] = unserialize($foruminfo['creditset']);
		$foruminfo['forumset'] = unserialize($foruminfo['forumset']);
		$foruminfo['commend'] = unserialize($foruminfo['commend']);
	}
}
!$foruminfo && stopic_topic_view_err('data_error');

//wind_forumcheck($foruminfo);
if ($foruminfo['f_type']=='former' && $groupid=='guest' && $_COOKIE) {
	stopic_topic_view_err('', '请先<a target="_blank" href="login.php">登录</a>，才能查看该内容块');//forum_former
}
if (!empty($foruminfo['style']) && file_exists(D_P."data/style/$foruminfo[style].php")) {
	$skin = $foruminfo['style'];
}
$pwdcheck = GetCookie('pwdcheck');
if ($foruminfo['password'] != '' && ($groupid=='guest' || $pwdcheck[$fid] != $foruminfo['password'] && !S::inArray($windid,$manager))) {
	if(!$_POST['wind_action']){
		$tplpath='wind';
		stopic_topic_view_err('forumpw_needpwd');
	} else{
		if($forum['password']==md5($_POST['wind_password']) && $groupid!='guest'){
			/**
			* 不同版块不同密码
			*/
			Cookie("pwdcheck[$fid]",$forum['password']);
		} elseif($groupid=='guest'){
			stopic_topic_view_err('forumpw_guest');
		} else{
			stopic_topic_view_err('forumpw_pwd_error');
		}
	}
}
if ($foruminfo['allowvisit'] && !allowcheck($foruminfo['allowvisit'],$groupid,$winddb['groups'],$fid,$winddb['visit'])){
	stopic_topic_view_err('forum_jiami');
}
if (!$foruminfo['cms'] && $foruminfo['f_type']=='hidden' && !$foruminfo['allowvisit']) {
	stopic_topic_view_err('forum_hidden');
}
//--end
	
$forumset  = $foruminfo['forumset'];

if (!$foruminfo['allowvisit'] && $_G['allowread']==0 && $_COOKIE) {
	stopic_topic_view_err('read_group_right');
}

//帖子浏览及管理权限
$isGM = $isBM = $admincheck = $managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
$pwSystem = array();
if ($groupid != 'guest') {
	$isGM = S::inArray($windid,$manager);
	$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
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
}

//版块查看权限
if ($foruminfo['allowread'] && !$admincheck && !allowcheck($foruminfo['allowread'],$groupid,$winddb['groups'])) {
	stopic_topic_view_err('forum_read_right');
}
if (!$admincheck) {
	!$foruminfo['allowvisit'] && forum_creditcheck();
	$foruminfo['forumsell'] && forum_sell($fid);
}
if ($read['ifcheck'] == 0 && !$isGM && $windid != $read['author'] && !$pwSystem['viewcheck']) {
	stopic_topic_view_err('read_check');
}
if ($read['locked']%3==2 && !$isGM && !$pwSystem['viewclose']) {
	stopic_topic_view_err('read_locked');
}
unset($foruminfo['forumset']);

$page = 1;

//团购主题帖
$pcid = $read['special'] - 20;
if (is_numeric($pcid) && $pcid > 0) {
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($read);
	list($fieldone,$topicvalue) = $postCate->getCatevalue($pcid);
	is_array($fieldone) && $read = array_merge($read,$fieldone);
	$isadminright = $postCate->getAdminright($pcid,$read['authorid']);
	list($pcuid) = $postCate->getViewright($pcid,$tid);
	$payway = $fieldone['payway'];
	$ifend = $read['endtime'] < $timestamp ? 1 : 0;
	
	$read['nums'] = intval($read['nums']);
	$special = 'read_pc';
}

//特殊主题处理
if ($read['special'] == 1 && ($foruminfo['allowtype'] & 2)) {#投票帖
	require_once(R_P.'require/readvote.php');
} elseif ($read['special'] == 5 && ($foruminfo['allowtype'] & 32)) {#辩论帖
	require_once(R_P.'require/readdebate.php');
}

//$special come from up
if (empty($special)) stopic_topic_view_err('不支持显示该主题类型');

require_once S::escapePath(dirname(__FILE__) . "/template/layout/topic.php");

function stopic_load_topic_view($topicType) {
	return S::escapePath(dirname(__FILE__) . "/template/topic/$topicType.htm");
}
function stopic_topic_view_err($msg_info, $append='') {
	$msg_info = getLangInfo('msg',$msg_info) . $append;
	$special = 'showmsg';
	require_once S::escapePath(dirname(__FILE__) . "/template/layout/topic.php");
	exit;
}

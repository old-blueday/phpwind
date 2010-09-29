<?php
!defined('R_P') && exit('Forbidden');

//if (!$winduid) ObHeader('index.php?m=o');
if (!$winduid) {
	Showmsg('not_login');
}
$USCR = 'user_home';

require_once(R_P . 'u/lib/space.class.php');
include_once(D_P.'data/bbscache/level.php');
$newSpace = new PwSpace($winduid);
$space = $newSpace->getInfo();
$finishPercentage = getMemberInfoFinishPercentage($winduid);

require_once(R_P.'require/showimg.php');
list($faceurl) = showfacedesign($winddb['icon'],1,'m');

$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
$weiboList = $weiboService->getUserAttentionWeibos($winduid, array(), 1, 20);
$weiboCount = $weiboService->getUserAttentionWeibosCount($winduid, array());
$weiboCount > 250 && $weiboService->deleteAttentionRelation($winduid, $weiboCount);
$o_weibopost == '0' && $weiboLiveList = $weiboService->getWeiboLives(21);//新鲜事直播
$weiboCount > 200 && $weiboCount = 200;
$pages = numofpage($weiboCount, 1, ceil($weiboCount/20), 'apps.php?q=weibo&do=attention&', 10, 'weiboList.filterWeibo');

if (!$db_toolbar) {
	$pwForumList = array();
	include_once(D_P.'data/bbscache/forumlist_cache.php');
	if ($pwForumAllList && $GLOBALS['groupid'] == 3) {
		$pwForumList = array_merge($pwForumList,$pwForumAllList);
	}
}

(empty($winddb['honor']) || !$_G['allowhonor']) && $winddb['honor'] = getLangInfo('other','whattosay');

//道具中心
$toolCenter = L::loadClass('ToolCenter', 'toolcenter');
$myTools = $randTools = array();
if ($db_toolifopen) {
	$myTools = $toolCenter->getToolsByUidAndNum($winduid, 3);
	if(empty($myTools)){
		$randTools = $toolCenter->getToolsByRandom(3);
	}
}

//任务
if ($db_job_isopen) {
	$isApplyJob = false;
	$jobService = L::loadclass("job", 'job');
	$myJobList = $jobService->appendJobDetailInfo($jobService->getAppliedJobs($winduid));
	if (count($myJobList)) $myJobList = array_slice($myJobList, 0, 3);
	if (empty($myJobList)) {
		$isApplyJob = true;
		$myJobList = $jobService->appendJobDetailInfo($jobService->getCanApplyJobs($winduid, $groupid));
		if (count($myJobList)) $myJobList = array_slice($myJobList, 0, 3);
	}
}

$modelList = array('recommendUsers' => 3,'visitor' => 5);
$o_weibopost == '0' && $modelList['friend'] = 6;
$spaceData = $newSpace->getSpaceData($modelList);
$o_weibopost == '0' && $myFriends = $spaceData['friend'];//我的好友
$latestVisits = $spaceData['visitor'];//最近访客
$recommendUsers = $spaceData['recommendUsers'];//我推荐关注模块

/* sinaweibo bind */
if (!$db_sinaweibo_status) {
	$isBindWeibo = false;
} else {
	$bindService = L::loadClass('weibobindservice', 'sns/weibotoplatform'); /* @var $bindService PW_WeiboBindService */
	$isBindWeibo = $bindService->isLocalBind($winduid, PW_WEIBO_BINDTYPE_SINA);
	if (!$isBindWeibo) {
		$bindSinaWeiboUrl = $bindService->getBindUrl($winduid);
	}
}

/*个人中心与消息中心的互动*/
//新消息中心消息数目统计
/*
$messageServer = L::loadClass('message', 'message');
list($messageNumber,$noticeNumber,$requestNumber,$groupsmsNumber) = $messageServer->getUserStatistics($winduid);
$messageNum = $messageNumber+$noticeNumber+$requestNumber+$groupsmsNumber;
$messageNumber = $messageNumber ? '('.$messageNumber.')' : '';
$noticeNumber = $noticeNumber ? '('.$noticeNumber.')' : '';
$requestNumber = $requestNumber ? '('.$requestNumber.')' : '';
$groupsmsNumber = $groupsmsNumber ? '('.$groupsmsNumber.')' : '';
*/
require_once(uTemplate::printEot('user_home'));
pwOutPut();

function getMemberInfoFinishPercentage($userId) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$data = $userService->get($userId, true, false, true);
	$needFields = array('introduce', 'oicq', 'aliww', 'signature', 'msn', 'yahoo', 'site', 'location', 'honor', 'bday');
	foreach (L::config('customfield','customfield') as $field) {
		$needFields[] = 'field_' . $field['id'];
	}
	$total = count($needFields);
	$finish = 0;
	foreach ($needFields as $field) {
		if ('' != $data[$field]) $finish++;
	}
	return ceil(round($finish * 1.0 / $total, 3) * 100);
}
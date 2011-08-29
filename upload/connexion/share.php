<?php
require_once('../global.php');

InitGP(array('type','action', 'tid', 'shareContent', 'isfollow', 'photo'));

if (!$winduid || !in_array($type, array('sinaweibo'))) return ;
// 站点是否绑定该类型
$weiboSiteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service');
if (!$weiboSiteBindService->isBind($type)) return ;

$db_bbsurl = substr($db_bbsurl, 0, strrpos($db_bbsurl, 'connexion'));

if ($tid) { // 取帖子内容
	//$threads = L::loadClass('Threads', 'forum');
	//$read = $threads->getByThreadId($tid);
	$read = $db->get_one("SELECT t.* ,tm.* FROM pw_threads t LEFT JOIN ".S::sqlMetadata(GetTtable($tid))." tm ON t.tid=tm.tid WHERE t.tid=" . S::sqlEscape($tid));
	
	if (!empty($read)) {
		$sinaWeiboContentTranslator = L::loadClass('SinaWeiboContentTranslator', 'sns/weibotoplatform/');
		$shareContent = $sinaWeiboContentTranslator->translate('article', array('content'=>preg_replace(array('/(&nbsp;){1,}/', '/( ){1,}/'), array(' ', ' '), substrs(stripWindCode(str_replace("\n", " ", strip_tags($read['content']))), 100)), 'objectid'=>$tid), array('title'=>$read['subject']));
		$title = urlencode(pwConvert($shareContent, 'UTF8', $db_charset));
		$query = $db->query("SELECT aid,attachurl,pid,type,ifthumb FROM pw_attachs WHERE pid=0 AND tid=" . S::sqlEscape($tid));
        $attachImg = '';
		while($rt = $db->fetch_array($query)){
			if ($rt['type'] != 'img') continue;
			$tmpUrl = geturl($rt['attachurl'],$rt['ifthumb']);
			if (is_array($tmpUrl)) $attachImg[] = false !== strpos($tmpUrl[0], 'http://') ? $tmpUrl[0] : $db_bbsurl . $tmpUrl[0];
		}
		$photoCount = count($attachImg);
	}
}

// 用户是否已经绑定了该类型的帐号 没有则引导
$weiboUserBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service');
$userInfo = $weiboUserBindService->getBindInfo($winduid, $type);
if (empty($userInfo)) { // 绑定引导
	$userBindList = $weiboUserBindService->getBindList($winduid);
	$bindUrl = $userBindList[$type]['bindUrl'];
	$action = 'bind';
} else {

	$weiboName = $userInfo['info']['name'];
	
	if ($action == 'share' && !empty($shareContent)) { // 分享
		$weiboSyncerService = L::loadClass('WeiboSyncer', 'sns/weibotoplatform');
		$result = $weiboSyncerService->shareContent($winduid, $shareContent, $photo);
		// 跳到关注官方帐号
		if ($result) {
			if (!$weiboUserBindService->isFollow($type, $winduid)) ObHeader($db_bbsurl . "connexion/share.php?type={$type}&action=isfollow");
			$action = 'sharesuccess';
		} else {
			$action = 'sharefail';
		}
	} elseif ($action == 'isfollow') { // 有官方微博帐号则引导关注 没有则提示分享成功
		$weiboSiteBindInfoService = L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service');
		$weiboAccount = $weiboSiteBindInfoService->getOfficalAccount($type);
		if (!$weiboAccount) $action = 'sharesuccess';
	} elseif ($action == 'follow') { // 关注
		$weiboSiteBindInfoService = L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service');
		$weiboAccount = $weiboSiteBindInfoService->getOfficalAccount($type);
		if ($weiboAccount && $isfollow) $result = $weiboUserBindService->follow($type, $winduid);
		$action = $result ? 'followsuccess' : 'followfail';
	}
}
include PrintTemplate('share_sina');
pwOutPut();

function PrintTemplate($template, $EXT = 'htm') {
	return R_P.'connexion/template/'.$template.".$EXT";
}

function getWeiboUserBindService() {
	return L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service');
}
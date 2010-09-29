<?php
!defined('P_W') && exit('Forbidden');
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	define('AJAX', '1');
}
empty($subtype) && $subtype = 'sms';
$normalUrl = $baseUrl . "?type=$subtype";
!empty($winduid) && $userId = $winduid;
InitGP(array(
	'smstype', 
	'page', 
	'redirect'), 'GP');
$smsCount = $smsAllCount = (int) $messageServer->countAllMessage($userId);
$notReadCount = (int) $messageServer->countMessagesNotRead($userId);
$emptyListTip = "";
$action = ($redirect && !in_array($action, array(
	'previous', 
	'info', 
	'next'))) ? '' : $action;
$nav = $action && $action != 'unread' ? array(
	$action => 'class = current') : ' class = current';
if (empty($action) || $action == 'all') {
	$redirect = 1;
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getAllMessages($userId, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何站内信，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'message') {
	$smstype = $messageServer->getConst('sms_message');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=message&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何短消息，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'rate') {
	$smstype = $messageServer->getConst('sms_rate');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=rate&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何评分，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'comment') {
	$smstype = $messageServer->getConst('sms_comment');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=comment&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何评论，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'guestbook') {
	$smstype = $messageServer->getConst('sms_guestbook');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=guestbook&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何留言，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'reply') {
	$smstype = $messageServer->getConst('sms_reply');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=reply&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何帖子回复，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";	
} elseif ($action == 'self') {
	$smsCount = $messageServer->countMessagesBySelf($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesBySelf($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=self&smsType=$smstype&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
} elseif ($action == 'other') {
	$smsCount = $messageServer->countMessagesByOther($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesByOther($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=other&smsType=$smstype&");
	list($today, $yesterday, $week, $tTimes, $yTimes, $wTimes, $mTimes) = getSubListInfo($smsList);
} elseif ($action == 'unread') {
	$pageCount = ceil($notReadCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesNotRead($userId, $page, $perpage);
	$pages = numofpage($notReadCount, $page, $pageCount, "$normalUrl&action=unread&");
	!$notReadCount && $emptyListTip = "暂无任何未读消息";
} elseif ($action == 'info') {
	InitGP(array(
		'mid', 
		'rid', 
		'page'), 'GP');
	empty($mid) && Showmsg("undefined_action");
	if (!$relation = $messageServer->getRelation($userId, $rid)) {
		Showmsg("该条消息你无权查看");
	}
	if (!($message = $messageServer->getMessage($mid))) {
		Showmsg("该条消息不存在");
	}
	$message['rid'] = $rid;
	$userListHtml = getAllUsersHtml($message);
	$smsList = $messageServer->getReplies($userId, $mid, $rid);
	$attachs = $messageServer->showAttachs($userId, $mid);
} elseif ($action == 'previous') {
	InitGP(array(
		'rid'), 'GP');
	empty($rid) && Showmsg("undefined_action");
	if (!($message = $messageServer->getUpMessage($userId, $rid, $smstype))) {
		Showmsg("已经是第一条");
	} else {
		$userListHtml = getAllUsersHtml($message);
		$smsList = $messageServer->getReplies($userId, $message['mid'], $message['rid']);
		$attachs = $messageServer->showAttachs($userId, $message['mid']);
	}
} elseif ($action == 'next') {
	InitGP(array(
		'rid'), 'GP');
	empty($rid) && Showmsg("undefined_action");
	if (!($message = $messageServer->getDownMessage($userId, $rid, $smstype))) {
		Showmsg("已经是最后一条");
	} else {
		$userListHtml = getAllUsersHtml($message);
		$smsList = $messageServer->getReplies($userId, $message['mid'], $message['rid']);
		$attachs = $messageServer->showAttachs($userId, $message['mid']);
	}
}

if ($subtype == 'sms') {
	$messageServer->resetStatistics(array(
		$userId), 'sms_num');
}
if ($smstype && in_array($action, array(
	'info', 
	'next', 
	'self',
	'other',
	'previous'))) {
	$navtype = $messageServer->getReverseConst($smstype);
	$navtype = explode('_', $navtype);
	$nav[$navtype[1]] = 'class = current';

}
$smsList = ($smsList) ? $smsList : array();

!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot($subtype);
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}

function getMessageIconTips($type,$creater){
	global $messageServer, $winduid;
	$_txt = $winduid == $creater ? '我发起的' : '我收到的';
	if (empty($type) || $type == $messageServer->getConst('sms_message')) {
		$_txt .= "消息";
	} elseif ($type == $messageServer->getConst('sms_rate')) {
		$_txt .= "评分";
	} elseif ($type == $messageServer->getConst('sms_share')) {
		$_txt .= "分享";
	} elseif ($type == $messageServer->getConst('sms_comment')) {
		$_txt .= "评论";
	} elseif ($type == $messageServer->getConst('sms_guestbook')) {
		$_txt .= "留言";
	}
	return $_txt;
}

?>
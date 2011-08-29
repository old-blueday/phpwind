<?php
!defined('P_W') && exit('Forbidden');
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	define('AJAX', '1');
}
empty($subtype) && $subtype = 'sms';
$normalUrl = $baseUrl . "?type=$subtype";
!empty($winduid) && $userId = $winduid;
S::gp(array(
	'smstype', 
	'page', 
	'redirect',
	'boxname',
	'itype'), 'GP');
$smsCount = $smsAllCount = (int) $messageServer->countAllMessage($userId);
$notReadCount = (int) $messageServer->countMessagesNotRead($userId);
$emptyListTip = "";
$action = ($redirect && !in_array($action, array(
	'previous', 
	'info', 
	'next', 'checkover' ))) ? '' : $action;
$nav = $action && $action != 'unread' ? array(
	$action => 'class = current') : ' class = current';
$boxname = ($boxname && in_array($boxname,array('inbox','outbox'))) ? $boxname : 'inbox';
$itypes = array('inbox'=>0,'outbox'=>1);
$isown = (isset($itypes[$boxname])) ? $itypes[$boxname] : 3;
$itype = $boxname;
//editer
$uploadfiletype = ($db_uploadfiletype) ? unserialize($db_uploadfiletype) : array();
$attachAllow = pwJsonEncode($uploadfiletype);
$imageAllow = pwJsonEncode(getAllowKeysFromArray($uploadfiletype, array('jpg','jpeg','gif','png','bmp')));
if (empty($action) || $action == 'inbox') {
	list($redirect,$boxname) = array(1,'inbox');
	$smsCount = (int) $messageServer->countInBox($userId);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getInBox($userId, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action={$action}&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何站内信，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'outbox') {
	list($redirect,$boxname) = array(1,'outbox');
	$smsCount = (int) $messageServer->countOutBox($userId);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getOutBox($userId, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action={$action}&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何站内信，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'message') {
	$smstype = $messageServer->getConst('sms_message');
	$smsCount = $messageServer->countMessageByTypeIdWithBoxName($userId, $smstype, $boxname);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessageByTypeIdWithBoxName($userId, $smstype, $page, $perpage,$boxname);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=message&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何短消息，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'rate') {
	$smstype = $messageServer->getConst('sms_rate');
	$smsCount = $messageServer->countMessageByTypeIdWithBoxName($userId, $smstype, $boxname);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessageByTypeIdWithBoxName($userId, $smstype, $page, $perpage, $boxname);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=rate&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何评分，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'reply') {
	$smstype = $messageServer->getConst('sms_reply');
	$smsCount = $messageServer->countMessageByTypeIdWithBoxName($userId, $smstype, $boxname);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessageByTypeIdWithBoxName($userId, $smstype, $page, $perpage, $boxname);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=reply&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何帖子回复，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'info') {
	S::gp(array(
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
	if($relation['relation'] == 2){
		$expand = (isset($message['expand'])) ? unserialize($message['expand']) : array();
		$message = $messageServer->getMessage($expand['parentid']);
		!$message && Showmsg("该条消息不存在");
		$mid = $message['mid'];
	}
	$message['rid'] = $rid;
	$userListHtml = getAllUsersHtml($message);
	$smsList = $messageServer->getReplies($userId, $mid, $rid);
	$attachs = $messageServer->showAttachs($userId, $mid);
	//parse attaches
	if ($attachs) {
		require_once R_P.'require/bbscode.php';
		$attachShow = new attachShow(true);
		foreach ($attachs as $k=>$v) {
			$atype = $attachShow->analyse($v);
			foreach($smsList as $k2=>$v2)
				$smsList[$k2]['content'] = $attachShow->parseContent($smsList[$k2]['content'],$atype,$v);
		}
	}
} elseif ($action == 'unread') {
	$pageCount = ceil($notReadCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesNotRead($userId, $page, $perpage);
	$pages = numofpage($notReadCount, $page, $pageCount, "$normalUrl&action=unread&");
	!$notReadCount && $emptyListTip = "暂无任何未读消息";
} elseif ($action == 'previous') {
	S::gp ( array ('rid' ), 'GP' );
	empty ( $rid ) && Showmsg ( "undefined_action" );
	!$smstype && $smstype = null;
	$message = $messageServer->getUpInfoByType ( $userId, $rid, $isown, $smstype );
	if (! ($message)) {
		Showmsg ( "已经是第一条" );
	} else {
		$userListHtml = getAllUsersHtml ( $message );
		$smsList = $messageServer->getReplies ( $userId, $message ['mid'], $message ['rid'] );
		$attachs = $messageServer->showAttachs ( $userId, $message ['mid'] );
	}
} elseif ($action == 'next') {
	S::gp ( array ('rid' ), 'GP' );
	!$smstype && $smstype = null;
	empty ( $rid ) && Showmsg ( "undefined_action" );
	$message = $messageServer->getDownInfoByType ( $userId, $rid, $isown, $smstype );
	if (! ($message)) {
		Showmsg ( "已经是最后一条" );
	}else{
		$userListHtml = getAllUsersHtml ( $message );
		$smsList = $messageServer->getReplies ( $userId, $message ['mid'], $message ['rid'] );
		$attachs = $messageServer->showAttachs ( $userId, $message ['mid'] );
	}
}elseif($action == 'checkover'){
	S::gp ( array ('rid', 'dir' ), 'GP' );
	!$smstype && $smstype = null;
	if($dir == 'previous'){
		$message = $messageServer->getUpInfoByType ( $userId, $rid , $isown, $smstype);
	}else{
		$message = $messageServer->getDownInfoByType ( $userId, $rid , $isown, $smstype);
	}
	if (($message)) {
		echo( "success\t" );
	}else{
		echo("over\t");
	}
	ajax_footer();
}
/*  modified for phpwind8.5
//收件箱和发件箱tab框样式
!$itype && $itype = 'all';
$itypes = array('all'=>3,'other'=>2,'self'=>1);
$isown = $itypes[$itype];
if (empty($action) || $action == 'all') {
	$redirect = 1;
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getAllMessages($userId, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何站内信，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'message') {
	$smstype = $messageServer->getConst('sms_message');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=message&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何短消息，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'rate') {
	$smstype = $messageServer->getConst('sms_rate');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=rate&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何评分，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'comment') {
	$smstype = $messageServer->getConst('sms_comment');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=comment&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何评论，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'guestbook') {
	$smstype = $messageServer->getConst('sms_guestbook');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=guestbook&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何留言，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";
} elseif ($action == 'reply') {
	$smstype = $messageServer->getConst('sms_reply');
	$smsCount = $messageServer->countMessage($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessages($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=reply&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
	!$smsCount && $emptyListTip = "暂无任何帖子回复，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧";	
} elseif ($action == 'self') {
	$smsCount = $messageServer->countMessagesBySelf($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesBySelf($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=self&smstype=$smstype&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
} elseif ($action == 'other') {
	$smsCount = $messageServer->countMessagesByOther($userId, $smstype);
	$pageCount = ceil($smsCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesByOther($userId, $smstype, $page, $perpage);
	$pages = numofpage($smsCount, $page, $pageCount, "$normalUrl&action=other&smstype=$smstype&");
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($smsList);
} elseif ($action == 'unread') {
	$pageCount = ceil($notReadCount / $perpage);
	$page = validatePage($page, $pageCount);
	$smsList = $messageServer->getMessagesNotRead($userId, $page, $perpage);
	$pages = numofpage($notReadCount, $page, $pageCount, "$normalUrl&action=unread&");
	!$notReadCount && $emptyListTip = "暂无任何未读消息";
} elseif ($action == 'info') {
	S::gp(array(
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
	if($relation['relation'] == 2){
		$expand = (isset($message['expand'])) ? unserialize($message['expand']) : array();
		$message = $messageServer->getMessage($expand['parentid']);
		!$message && Showmsg("该条消息不存在");
		$mid = $message['mid'];
	}
	$message['rid'] = $rid;
	$userListHtml = getAllUsersHtml($message);
	$smsList = $messageServer->getReplies($userId, $mid, $rid);
	$attachs = $messageServer->showAttachs($userId, $mid);
} elseif ($action == 'previous') {
	S::gp ( array ('rid' ), 'GP' );
	empty ( $rid ) && Showmsg ( "undefined_action" );
	!$smstype && $smstype = null;
	$message = $messageServer->getUpInfoByType ( $userId, $rid, $isown, $smstype );
	if (! ($message)) {
		Showmsg ( "已经是第一条" );
	} else {
		$userListHtml = getAllUsersHtml ( $message );
		$smsList = $messageServer->getReplies ( $userId, $message ['mid'], $message ['rid'] );
		$attachs = $messageServer->showAttachs ( $userId, $message ['mid'] );
	}
} elseif ($action == 'next') {
	S::gp ( array ('rid' ), 'GP' );
	!$smstype && $smstype = null;
	empty ( $rid ) && Showmsg ( "undefined_action" );
	$message = $messageServer->getDownInfoByType ( $userId, $rid, $isown, $smstype );
	if (! ($message)) {
		Showmsg ( "已经是最后一条" );
	}else{
		$userListHtml = getAllUsersHtml ( $message );
		$smsList = $messageServer->getReplies ( $userId, $message ['mid'], $message ['rid'] );
		$attachs = $messageServer->showAttachs ( $userId, $message ['mid'] );
	}	
}elseif($action == 'checkover'){
	S::gp ( array ('rid', 'dir' ), 'GP' );
	!$smstype && $smstype = null;
	if($dir == 'previous'){
		$message = $messageServer->getUpInfoByType ( $userId, $rid , $isown, $smstype);
	}else{
		$message = $messageServer->getDownInfoByType ( $userId, $rid , $isown, $smstype);
	}
	if (($message)) {
		echo( "success\t" );
	}else{
		echo("over\t");
	}
	ajax_footer();
}

*/

if ($subtype == 'sms') {
	$messageServer->resetStatistics(array(
		$userId), 'sms_num');
}

/*  modified for phpwind8.5
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
*/
$currentBoxs[$boxname] = 'class = current';
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
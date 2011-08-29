<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
empty($subtype) && $subtype = 'groupsms';
$normalUrl = $baseUrl . "?type=$subtype";
!empty($winduid) && $userId = $winduid;

S::gp(array('smstype','page'), 'GP');
!$page && $page = 1;

$selected_all = $selected_self = $selected_other = '';
$selected_all = $action == '' || $action == 'all' ? 'selected' : '';
$selected_self = $action == 'self' ? 'selected' : '';
$selected_other = $action == 'other' ? 'selected' : '';

$groupsmsCount = $groupsmsAllCount = (int) $messageServer->countAllGroupMessage($userId);
$notReadCount = (int) $messageServer->countGroupMessagesNotRead($userId);
//editer
$uploadfiletype = ($db_uploadfiletype) ? unserialize($db_uploadfiletype) : array();
$attachAllow = pwJsonEncode($uploadfiletype);
$imageAllow = pwJsonEncode(getAllowKeysFromArray($uploadfiletype, array('jpg','jpeg','gif','png','bmp')));
if (empty($action) || $action == 'all') {
	$pageCount = ceil($groupsmsCount / $perpage);
	$page = validatePage($page,$pageCount);
	$groupsmsList = $messageServer->getAllGroupMessages($userId, $page, $perpage);
	$url = "$normalUrl&";
	!$groupsmsCount && $emptyListTip = "暂无任何群消息";
} elseif ($action == 'unread') {
	$groupsmsCount = $notReadCount;
	$pageCount = ceil($groupsmsCount / $perpage);
	$page = validatePage($page,$pageCount);
	$groupsmsList = $messageServer->getGroupMessagesNotRead($userId, $page, $perpage);
	$url = "$normalUrl&action=unread&";
	$pages = numofpage($groupsmsCount, $page, $pageCount, $url);
	!$notReadCount && $emptyListTip = "暂无任何未读群消息";
} elseif ($action == 'self') {
	$groupsmsCount = $messageServer->countGroupMessagesBySelf($userId);
	$pageCount = ceil($groupsmsCount / $perpage);
	$page = validatePage($page,$pageCount);
	$groupsmsList = $messageServer->getGroupMessagesBySelf($userId, $page, $perpage);
	$url = "$normalUrl&action=self&";

} elseif ($action == 'other') {
	$groupsmsCount = $messageServer->countGroupMessagesByOther($userId);
	$pageCount = ceil($groupsmsCount / $perpage);
	$page = validatePage($page,$pageCount);
	$groupsmsList = $messageServer->getGroupMessagesByOther($userId, $page, $perpage);
	$url = "$normalUrl&action=other&";

} elseif ($action == 'info') {
	S::gp(array('mid', 'rid', 'page'), 'GP');
	(empty($mid) || empty($rid)) && Showmsg("undefined_action");
	if(!$relation = $messageServer->getRelation($userId,$rid)){
		Showmsg("该条消息你无权查看");
	}
	if (!($message = $messageServer->getGroupMessage($mid))) {
		Showmsg("该条消息不存在");
	}
	$message['rid'] = $rid;
	$message['typeid'] = $smstype;
	$smsInfo = array();
	if ($message['typeid'] != $messageServer->getConst('groupsms_colony')) {
		$userListHtml = getAllUsersHtml($message);
		$smsInfo = $messageServer->getGroupReplies($userId, $mid, $rid);
		$attachs = $messageServer->showAttachs($userId, $mid);
	}else{
		 $messageServer->markMessage($userId,$rid);
	}	
} elseif ($action == 'up') {
	S::gp(array('rid'), 'GP');
	empty($rid) && Showmsg("undefined_action");
	if (!($message = $messageServer->getGroupUpMessage($userId, $rid))) {
		Showmsg("已经是第一条");
	}
	if ($message['typeid'] != $messageServer->getConst('groupsms_colony')) {
		$userListHtml = getAllUsersHtml($message);
		$smsInfo = $messageServer->getGroupReplies($userId, $message['mid'], $message['rid']);
		$attachs = $messageServer->showAttachs($userId, $message['mid']);
	}

} elseif ($action == 'down') {
	S::gp(array('rid'), 'GP');
	empty($rid) && Showmsg("undefined_action");
	if (!($message = $messageServer->getGroupDownMessage($userId, $rid))) {
		Showmsg("已经是最后一条");
	}
	if ($message['typeid'] != $messageServer->getConst('groupsms_colony')) {
		$userListHtml = getAllUsersHtml($message);
		$smsInfo = $messageServer->getGroupReplies($userId, $message['mid'], $message['rid']);
		$attachs = $messageServer->showAttachs($userId, $message['mid']);
	}

}elseif($action == 'checkover'){
	S::gp ( array ('rid', 'dir' ), 'GP' );
	if($dir == 'up'){
		$message = $messageServer->getGroupUpMessage( $userId, $rid);
	}else{
		$message = $messageServer->getGroupDownMessage( $userId, $rid);
	}
	if (($message)) {
		echo( "success\t" );
	}else{
		echo("over\t");
	}
	ajax_footer();
}

$groups = $messageServer->getBlackColony($userId);
if (empty($action) || in_array($action, array('all', 'self', 'other'))) {
	list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($groupsmsList);
	$pages = numofpage($groupsmsCount, $page, $pageCount, $url);
}
if($subtype == 'groupsms'){
	$messageServer->resetStatistics(array($userId),'groupsms_num');
}

!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot($subtype);
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}

function getMessageIconTips($value){
	global $messageServer, $winduid, $groups;
	$_txt = $winduid == $value['create_uid'] ? '我发起的' : '我收到的';
	if ($value['typeid'] == $messageServer->getConst('groupsms_colony') && in_array($value['colonyid'], $groups)) {
		$_txt = "拒收的群消息";
	} elseif ($value['typeid'] == $messageServer->getConst('groupsms_colony') && !in_array($value['colonyid'], $groups)) {
		$_txt .= "群消息";
	} elseif ($value['typeid'] == $messageServer->getConst('groupsms_shield')) {
		$_txt = "屏蔽的多人对话";
	} else {
		$_txt .= "多人对话";
	}
	return $_txt;
}

?>
<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
empty($subtype) && $subtype = 'notice';
$normalUrl = $baseUrl . "?type=$subtype";
S::gp(array('smstype','page','mid','rid','redirect','ajax'), 'GP');
isset($rid) && empty($rid) && Showmsg("undefined_action");
isset($mid) && empty($mid) && Showmsg("undefined_action");
$page = empty($page) ? 1 :(int)$page;
$pageCount = $noticeCount = 0;
$messageServer->grabMessage($winduid,array($groupid),max($winddb['lastgrab'],$winddb['regdate']));
$noticeCount = $noticeAllCount = (int) $messageServer->countAllNotice($winduid);
$noticeList = array();
$chineseName = array('200' => '系统通知','201' => '团购通知','202'=>'活动通知','203'=>'应用通知','204'=>'评论通知','205'=>'留言通知',''=>$action == 'unread' ? '未读通知':'通知');
$action = $redirect && !in_array($action,array('previous','info','next','unread')) ? '' : $action;
$selected = $action ? array($action=>'selected') : 'selected';
$nav = $action && $action != 'unread' ? array($action=>'class = current') : ' class = current';
if (empty($action)) {
	$pageCount = ceil($noticeCount / $perpage);
	$page = validatePage($page,$pageCount);
	$noticeList = $messageServer->getAllNotices($winduid, $page, $perpage);
	$redirect = 1;
}elseif(in_array($action,array('system','postcate','active','apps','comment','guestbook'))) {
	$smstype = $messageServer->getConst($type.'_'.$action);
	$noticeCount = $messageServer->countNotice($winduid,$smstype);
	$pageCount = ceil($noticeCount / $perpage);
	$page = validatePage($page,$pageCount);
	$noticeList = $messageServer->getNotices($winduid, $smstype, $page, $perpage);
}elseif($action == 'unread') {
	$notReadCount = (int) $messageServer->countNoticesNotRead($winduid);
	$noticeCount = $notReadCount;
	$pageCount = ceil($notReadCount / $perpage);
	$page = validatePage($page,$pageCount);
	$noticeList = $messageServer->getNoticesNotRead($winduid, $page, $perpage);
	$redirect = 1;
}elseif($action == 'previous') {
	if(!$message = $messageServer->getUpNotice($winduid, $rid, $smstype)){
		Showmsg("已经是第一条".$chineseName[$smstype]);
	}
	$messageServer->markMessage($winduid,$message['rid']);
	$message['content'] = messageReplace($message['content']);
	$notReadCount = (int) $messageServer->countNoticesNotRead($winduid);
}elseif($action == 'next') {
	if(!$message = $messageServer->getDownNotice($winduid, $rid, $smstype)){
		Showmsg("已经是最后一条".$chineseName[$smstype]);
	}
	$messageServer->markMessage($winduid,$message['rid']);
	$message['content'] = messageReplace($message['content']);
	$notReadCount = (int) $messageServer->countNoticesNotRead($winduid);
}elseif ($action == 'info') {
	if(!$messageServer->getRelation($winduid,$rid)){
		Showmsg("该条消息你无权查看");
	}
	if(!($message = $messageServer->getMessage($mid))){
		Showmsg("该条消息不存在");
	}
	$message['rid'] = $rid;
	//改写通知是否已读状态
    $messageServer->markMessage($winduid,$rid);
    $message['content'] = messageReplace($message['content']);
	$notReadCount = (int) $messageServer->countNoticesNotRead($winduid);
	
}
if($smstype && in_array($action,array('info','next','previous'))){
	$navtype = $messageServer->getReverseConst($smstype);
	$navtype = explode('_',$navtype);
	$nav[$navtype[1]] = 'class = current';	
}
if(empty($action) || in_array($action,array('unread','system','postcate','active','apps','comment','guestbook'))){
	if($action != 'unread'){ 	
		$notReadCount = (int) $messageServer->countNoticesNotRead($winduid);
		list($today, $yesterday, $tTimes, $yTimes, $mTimes) = getSubListInfo($noticeList);
	}
	$pages = numofpage($noticeCount, $page,$pageCount, "$normalUrl&action=$action&");
}elseif($action == 'checkover'){
	S::gp ( array ('rid', 'dir' ), 'GP' );
	if($dir == 'previous'){
		$message = $messageServer->getUpNotice($winduid, $rid, $smstype);
	}else{
		$message = $messageServer->getDownNotice($winduid, $rid, $smstype);
	}
	if (($message)) {
		echo( "success\t" );
	}else{
		echo("over\t");
	}
	ajax_footer();
}
$messageServer->resetStatistics(array($winduid),'notice_num');
!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot($subtype);
if(defined('AJAX')){
	ajax_footer();
}else{
	pwOutPut();
}
function messageReplace($v){
	return nl2br($v);
}
?>
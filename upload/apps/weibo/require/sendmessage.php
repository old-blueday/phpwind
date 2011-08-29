<?php
!defined('A_P') && exit('Forbidden');
!$winduid && Showmsg('not_login');


S::gp(array('_usernames', 'atc_mctitle', 'atc_mccontent'));
$usernames = $_usernames;/*specia;*/

if(!$_G['allowmessege']){
	Showmsg('你所在的用户组不能发送消息');
}

if ("" == $usernames) {
	Showmsg('收件人不能为空');
}

if (in_array($windid,$usernames)) {
	Showmsg('你不能给自己发消息');
}

if (count($usernames) > 1 && intval($_G['multiopen']) < 1 ) {
	Showmsg('你不能发送多人消息');
}

$usernames = is_array($usernames) ? $usernames : explode(",", $usernames);
if (in_array($windid, array($usernames))) {
	unset($usernames[$windid]);
}

$messageService = L::loadClass("message", 'message'); /* @var $messageService PW_Message */
if(!($messageService->checkUserMessageLevle('sms',1))){
	Showmsg('你已超过每日发送消息数或你的消息总数已满');
}
list($bool,$message) = $messageService->checkReceiver($usernames);
if(!$bool){
	Showmsg($message);
	ajaxExport(array('bool' => $bool, 'message' => $message));
}
if ("" == $atc_mctitle) {
	Showmsg('标题不能为空');
}
if (200 < strlen($atc_mctitle)) {
	Showmsg('标题不能超过限度');
}
if ("" == $atc_mccontent) {
	Showmsg('内容不能为空');
}
if( isset($_G['messagecontentsize']) && $_G['messagecontentsize'] > 0 && strlen($atc_mccontent) > $_G['messagecontentsize']){
	Showmsg('内容超过限定长度'.$_G['messagecontentsize'].'字节');
}
$filterUtil = L::loadClass('filterutil', 'filter');
$atc_mctitle   = $filterUtil->convert($atc_mctitle);
$atc_mccontent = $filterUtil->convert($atc_mccontent);
$atc_mccontent = str_replace(array('&#46;&#46;','&#41;','&#60;','&#61;'), array('..',')','<','='), $atc_mccontent);
$messageInfo = array('create_uid' => $winduid, 'create_username' => $windid, 'title' => $atc_mctitle, 
	'content' => $atc_mccontent);
$messageId = $messageService->sendMessage($winduid, $usernames, $messageInfo);

<?php
!defined('P_W') && exit('Forbidden');
if(!$_G['allowmessege']) Showmsg ( 'msg_group_right' );
$messageServer = L::loadClass('message', 'message');
if(!($messageServer->checkUserMessageLevle('sms',1))) Showmsg ( '你已超过每日发送消息数或你的消息总数已满' );
list(,$showq) = explode("\t", $db_qcheck);

if (empty($_POST['step'])) {
	S::gp(array('touid','type'));
	if(!$touid) Showmsg('请指定发送的用户');
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$reinfo = $userService->get($touid);//uid,username
	if ($type == 'birth') {
		$subject = getLangInfo('writemsg', 'birth_title');
		$atc_content = getLangInfo('writemsg', 'birth_content');
	}
	list($bool,$message) = $messageServer->checkReceiver(array($reinfo['username']));
	if(!$bool){
		 Showmsg ( $message );
	}
	require_once PrintEot('ajax');
	ajax_footer();
} else {
	PostCheck(1, $db_gdcheck & 8);
	S::gp(array(
		'msg_title',
		'pwuser'
	), 'P');
	S::gp(array(
		'atc_content'
	), 'P', 0);
	
	$atc_content = S::escapeChar(trim($atc_content));
	
	if (!$atc_content || !$msg_title || !$pwuser) {
		Showmsg('msg_empty');
	} elseif (strlen($msg_title) > 75 || strlen($atc_content) > 1500) {
		Showmsg('msg_subject_limit');
	}
	if(isset($_G['messagecontentsize']) && $_G['messagecontentsize'] > 0 && strlen($atc_content) > $_G['messagecontentsize']){
		Showmsg('内容超过限定长度'.$_G['messagecontentsize'].'字节');
	}
	if($pwuser == $windid){
		Showmsg('send_message_to_self');
	}
	require_once (R_P . 'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($msg_title)) !== false) {
		Showmsg('title_wordsfb');
	}
	if (($banword = $wordsfb->comprise($atc_content, false)) !== false) {
		Showmsg('content_wordsfb');
	}
	//$msgq && Qcheck($_POST['qanswer'], $_POST['qkey']);
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->getByUserName($pwuser); //uid,banpm,msggroups
	if (!$rt) {
		$errorname = $pwuser;
		Showmsg('user_not_exists');
	}
	if ($rt['msggroups'] && strpos($rt['msggroups'], ",$groupid,") !== false || strpos(",$rt[banpm],", ",$windid,") !== false) {
		$errorname = $pwuser;
		Showmsg('msg_refuse');
	}

	M::sendMessage(
		$winduid,
		array($pwuser),
		array(
			'create_uid' => $winduid,
			'create_username' => $windid,
			'title' => $msg_title,
			'content' => stripslashes($atc_content),
		)
	);
	Showmsg('send_success');
}

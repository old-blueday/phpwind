<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 发送社区短消息或系统通知
 * 重构新消息中心 
 * @param array $msg 信息格式如下:
 * 	$msg = array(
 *		'toUser'	=> 'admin', //接收者用户名,可为数组群发:array('admin','abc')
 *		'toUid'		=> 1,		//接收者uid,可为数组群发:array(1,2),当与 toUser 同时存在时，自然失效
 *		'fromUid'	=> 2,		//发送者UID,与fromUser同时存在才有效 (可选,默认为'0')
 *		'fromUser'	=> 'pwtest',//发送者用户名,与fromUid同时存在才有效(可选,默认为'SYSTEM')
 *		'subject'	=> 'Test',	//消息标题
 *		'content'	=> '~KO~',	//消息内容
 *		'other'		=> array()	//其他信息变量
 *	);
 * @return boolean 返回消息发送是否完成
 */
function pwSendMsg($msg) {
	global $db,$timestamp;
	if ((!$msg['toUser'] && !$msg['toUid']) || !$msg['subject'] || !$msg['content']) {
		return false;
	}
	$msg['subject'] = getLangInfo('writemsg',$msg['subject'],$msg);
	$msg['content'] = getLangInfo('writemsg',$msg['content'],$msg);
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$usernames = ($msg['toUser']) ? $msg['toUser'] : $userService->getUserNameByUserId($msg['toUid']);
	$usernames = (is_array($usernames)) ? $usernames : array($usernames);
	if(!$msg['fromUid'] || !$msg['fromUser']){
		M::sendNotice($usernames,array('title' => $msg['subject'],'content' => $msg['content']));
	}else{
		M::sendMessage($msg['fromUid'],$usernames,array('create_uid'=>$msg['fromUid'],'create_username'=>$msg['fromUser'],'title' => $msg['subject'],'content' => $msg['content']));
	}
	return true;
}

function delete_msgc($ids = null) {
	return true;
}

function send_msgc($msg,$isNotify=true) {
	global $db;
	if (!is_array($msg)) return;
	$uid = $sql = $mc_sql = array();
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	foreach ($msg as $k => $v) {
		$username = $userService->getUserNameByUserId($v[0]);
		if (!$username) continue;
		M::sendNotice(
			array($username),
			array(
				'title' => $v[6],
				'content' => $v[7]
			)
		);
	}
}
?>
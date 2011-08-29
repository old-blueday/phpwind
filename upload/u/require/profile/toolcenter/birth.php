<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:生日卡
@type:会员类
@effect:对特定用户使用。

****/
S::gp(array('uid'),'GP',2);
if($tooldb['type']!=2){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
if(!$uid){
	Showmsg('tooluse_nobirther');
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userName = $userService->getUserNameByUserId($uid);
if(!$userName){
	Showmsg('tooluse_nobirther');
}
$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
M::sendNotice(
	array($userName),
	array(
		'title' => getLangInfo('writemsg','birth_title',array(
		'userName'=>$userName
	)),
		'content' => getLangInfo('writemsg','birth_content',array(
		'fromUsername'=>$windid
	))
));

$logdata = array(
	'type'		=>	'use',
	'descrip'	=>	'tool_16_descrip',
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'toname'	=>	$userName,
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
	'subject'	=>	$subject,
);

writetoollog($logdata);

Showmsg("已向您的好友发送了生日贺卡");
?>
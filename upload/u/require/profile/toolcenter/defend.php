<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:护身符
@type:会员类
@effect:使用后，不能对该用户实现猪头术效果。

****/
S::gp(array('uid'),'GP',2);
if($tooldb['type']!=2){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
if(!$uid){
	Showmsg('tooluse_nodefender');
}

$rt = $db->get_one("SELECT MAX(time) AS tooltime FROM pw_toollog WHERE touid=".S::sqlEscape($uid)."AND filename='defend'");
if($rt && $rt['tooltime']>$timestamp-3600*48){
	Showmsg('tooluse_deused');
}
$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata=array(
	'type'		=>	'use',
	'filename'	=>	'defend',
	'descrip'	=>	'tool_21_descrip',
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'touid'		=>	$uid,
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
);
writetoollog($logdata);
Showmsg('toolmsg_success');
?>
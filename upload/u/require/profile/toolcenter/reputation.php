<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:清零卡
@type:会员类
@effect:可将自已负威望清零

****/

if ($tooldb['type'] != 2) {
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($winduid, false, true);

if ($rt['rvrc'] < 0) {
	$userService->update($winduid, array(), array('rvrc' => 0));
	$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
	$logdata = array(
		'type'		=>	'use',
		'nums'		=>	'',
		'money'		=>	'',
		'descrip'	=>	'tool_1_descrip',
		'uid'		=>	$winduid,
		'username'	=>	$windid,
		'ip'		=>	$onlineip,
		'time'		=>	$timestamp,
		'toolname'	=>	$tooldb['name'],
		'from'		=>	'',
	);
	writetoollog($logdata);
	Showmsg('toolmsg_1_success');
} else {
	Showmsg('toolmsg_1_failed');
}
?>
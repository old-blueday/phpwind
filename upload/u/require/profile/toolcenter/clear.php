<?php
!function_exists('readover') && exit('Forbidden');

/**
 * @name:还原卡
 * @type:会员类
 * @effect:清除猪头卡的效果．
 */

S::gp(array('uid'),'GP',2);
if ($tooldb['type'] != 2){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
if (!$uid){
	Showmsg('tooluse_nopig');
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($uid);//icon,username
$user_a = explode('|',addslashes($rt['icon']));

if (empty($user_a[4])) {
	Showmsg('tooluse_nousepig');
} else {
	$user_a[4] = '';
	//$userface = "$user_a[0]|$user_a[1]|$user_a[2]|$user_a[3]";
	$userface = implode('|',$user_a);
}

$userService->update($uid, array('icon' => $userface));

$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata=array(
	'type'		=>	'use',
	'descrip'	=>	'tool_19_descrip',
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'toname'	=>	$rt['username'],
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
);
writetoollog($logdata);

//* $_cache = getDatastore();
//* $_cache->delete('UID_'.$uid);
Showmsg('toolmsg_success');
?>
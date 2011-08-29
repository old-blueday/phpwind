<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:运气卡
@type:会员类
@effect:使用后随机加减积分。

****/

if ($tooldb['type'] != 2) {
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}

$condition = unserialize($tooldb['conditions']);
$lucktype = $condition['luck']['lucktype'];
$num = $newcredit = '';
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($winduid, false, true); //$lucktype
$num = mt_rand($condition['luck']['range1'],$condition['luck']['range2']);
$newluck = $rt[$lucktype] + $num;


$credit->set($winduid,$lucktype,$num);
if ($num != 0) {
	$creditType = $num > 0 ? 'hack_creditluckadd'  : 'hack_creditluckdel';
	$credit->addLog($creditType, array($lucktype => $num), array(
		'uid'		=> $winduid,
		'username'	=> $windid,
		'ip'		=> $onlineip,
		'operator'	=> $windid
	));
	$credit->writeLog();
}
$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata = array(
	'type'		=>	'use',
	'descrip'	=>	'tool_15_descrip',
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
	'newluck'	=>	$newluck,
);

writetoollog($logdata);
$msg = '';
if ($num > 0) {
	$msg = '祝贺您获得'.$num.'个'.pwCreditNames($lucktype);
} elseif ($num < 0) {
	$msg = '不幸，您的'.pwCreditNames($lucktype).'被扣除'.abs($num).'个';
} elseif ($num == 0) {
	$msg = '波澜不惊，'.pwCreditNames($lucktype).'没有发生变化';
}
Showmsg($msg);
?>
<?php
!defined('P_W') && exit('Forbidden');
list(,$messageNumber,$noticeNumber,$requestNumber,$groupsmsNumber) = $messageServer->countAllByUserId($winduid);
$updateUserMessageTip = $updateUserDataMessageTip = array();
($winddb['newpm'] != 0 && ($messageNumber + $groupsmsNumber != $winddb['newpm'])) && ($updateUserMessageTip['newpm'] = $winddb['newpm'] = $messageNumber + $groupsmsNumber);
($winddb['newnotice'] != 0 && ($noticeNumber != $winddb['newnotice'])) && ($updateUserDataMessageTip['newnotice'] = $winddb['newnotice'] = $noticeNumber);
($winddb['newrequest'] != 0 && ($requestNumber != $winddb['newrequest'])) && ($updateUserDataMessageTip['newrequest'] = $winddb['newrequest'] = $requestNumber);

$messageNumber = $messageNumber ? '('.$messageNumber.')' : '';
$noticeNumber = $noticeNumber ? '('.$noticeNumber.')' : '';
$requestNumber = $requestNumber ? '('.$requestNumber.')' : '';
$groupsmsNumber = $groupsmsNumber ? '('.$groupsmsNumber.')' : '';
$totalMessage = $max = 0;
if($_G['maxmsg']){
	$numbers =$messageServer->statisticUsersNumbers(array($winduid));
	$totalMessage = isset($numbers[$winduid]) ? $numbers[$winduid] : 0;
	$max = (int)$_G['maxmsg'];
	$percent = (round($totalMessage/$max,4)*100) >= 100 ? '100'.'%' :(round($totalMessage/$max,4)*100).'%';
	$percentTip = ',最多可存消息'.$_G['maxmsg'].'条,'.'空间使用率'.$percent;
}

/* load u header */
$newSpace = new PwSpace($winduid);
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
if (S::isArray($updateUserMessageTip) || S::isArray($updateUserDataMessageTip)) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->update($winduid, $updateUserMessageTip, $updateUserDataMessageTip);
}
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
require_once(uTemplate::printEot('header'));
require messageEot('leftmenu');
?>
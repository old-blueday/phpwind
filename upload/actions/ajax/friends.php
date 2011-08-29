<?php
header('Content-type: text/html;charset='.$db_charset);
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
$output = array();
S::gp(array('key'), 'GP');
PostCheck();
$key = pwConvert(urldecode($key),$db_charset,'utf8');

if (!$winduid || !$key || strlen($key) > 15) {
	$output['status'] = 0;
	echo pwJsonEncode($output);
	exit; 
}
$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
$friends = $attentionService->getUidsInFollowList($winduid,1,500);


if (S::isArray($friends)) {
	$userService = L::loadClass('userservice','user');
	$usernames = $userService->getUserNamesByUserIds($friends);
	foreach ($usernames as $k=>$v) {
		if (strpos($v, $key) !== 0) unset($usernames[$k]);
	}
	$output['status'] = 1;
	foreach((array)$usernames as $k=>$v) {
		$output['users'][] = array('uid'=>$k,'uname'=>$v);
	}
} else {
	$output['status'] = 0;
}

echo pwJsonEncode($output);
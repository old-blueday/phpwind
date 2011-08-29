<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('uid','username'));

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$uid && $userExistById   = $userService->isExist($uid);
if($username){
	$uid = $userService->getUserIdByUserName($username);
	$userExistById =1;
}
if ($userExistById) {
	ObHeader('u.php?uid='.$uid);
} else {
	Showmsg('用户不存在');
}

?>
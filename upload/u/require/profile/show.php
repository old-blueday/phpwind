<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('uid','username'));

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userExistById   = $userService->isExist($uid);
$userExistByName = $userService->isExistByUserName($username);
if ($userExistById || $userExistByName) {
	ObHeader('u.php?'.($username ? 'username='.$username : 'uid='.$uid));
} else {
	Showmsg('用户不存在');
}

?>
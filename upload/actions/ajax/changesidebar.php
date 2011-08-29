<?php
!defined('P_W') && exit('Forbidden');
PostCheck();
S::gp(array('sidebar'));
if ($sidebar != getstatus($winddb['userstatus'], PW_USERSTATUS_SHOWSIDEBAR)) {	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->setUserStatus($winduid, PW_USERSTATUS_SHOWSIDEBAR, $sidebar);
}
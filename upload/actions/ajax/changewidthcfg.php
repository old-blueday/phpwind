<?php
!defined('P_W') && exit('Forbidden');
PostCheck();
S::gp(array('widthcfg'));

if ($widthcfg != getstatus($winddb['userstatus'], PW_USERSTATUS_SHOWWIDTHCFG)) {	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->setUserStatus($winduid, PW_USERSTATUS_SHOWWIDTHCFG, $widthcfg);
}
<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'editor'
));
if ($editor != getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR)) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->setUserStatus($winduid, PW_USERSTATUS_EDITOR, $editor);
}
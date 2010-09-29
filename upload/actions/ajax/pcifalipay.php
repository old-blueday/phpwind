<?php
!defined('P_W') && exit('Forbidden');

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$trade = $userService->get($winduid, false, false, true); //tradeinfo;
$trade = unserialize($trade['tradeinfo']);
if (!$trade['alipay']) {
	echo 'fail';
}
ajax_footer();

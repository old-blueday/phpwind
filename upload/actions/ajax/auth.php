<?php
!defined('P_W') && exit('Forbidden');

/*实名认证获取验证码*/
S::gp('mobile', 'type', 'P');
$authService = L::loadClass('Authentication', 'user');

if (empty($_POST['step'])) {
	
	$status = $authService->getverify('profile', $mobile, $winduid,false,'bind');
	echo $status;

} elseif ($_POST['step'] == '2') {
		
	S::gp(array('authverify'));

	if (empty($authverify)) { 
		echo '7';
		ajax_footer();
	}
	$status = $authService->checkverify($mobile, $winduid, $authverify);
		
	if ($status && $authService->syncuser($mobile, $winduid, $authverify, $winduid, $windid, 'modify')) {
		$authService->setCurrentInfo('profile');
		$userService = L::loadClass('userservice', 'user');/* @var $register PW_Register */
		$userService->update($winduid, array('authmobile' => $mobile));
		$userService->setUserStatus($winduid, PW_USERSTATUS_AUTHMOBILE, true);
		initJob($winduid,'doAuthMobile');
		echo 0;
	} else {
		echo 5;
	}
}
ajax_footer();
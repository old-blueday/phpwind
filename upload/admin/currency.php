<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=currency";
require_once(R_P."require/forum.php");

if(!$action){
	include PrintEot('currency');exit;
} elseif($action == 'edit'){
	if(!$_POST['step']){
		S::gp(array('uid','username'));
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if(is_numeric($uid)){
			$rt = $userService->get($uid, true, true);
		} else{
			$rt = $userService->getByUserName($username, true, true);
		}

		!$rt && adminmsg('user_not_exists');
		include PrintEot('currency');exit;
	} else{
		S::gp(array('uid','currency'),'P');
		$userService = L::loadclass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($uid, null, array('currency'=>$currency));
		adminmsg('operate_success');
	}
}
?>
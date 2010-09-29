<?php
!function_exists('readover') && exit('Forbidden');

function Signfunc($starttime,$currency) {
	global $db,$winduid,$windid,$onlineip,$groupid,$tdtime,$db_signgroup,$db_signmoney,$db_signcurtype,$credit;
	if (!in_array($db_signcurtype,array('money','rvrc','credit','currency'))) {
		return false;
	}
	$set_a = array();
	require_once(R_P.'require/credit.php');

	if (!$starttime) {
		$set_a = array($tdtime,$db_signmoney);
	} elseif (!$db_signmoney || strpos($db_signgroup,",$groupid,") === false) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), array('starttime' => 0));
	} else {
		$days = floor(($tdtime-$starttime)/86400);
		$cost = $days * $db_signmoney;
		$cost < 0 && $cost = 0;
		if ($currency >= $cost) {
			$set_a = array($tdtime,$cost);
		} else {
			$cost = $currency-$currency%$db_signmoney;
			$cost < 0 && $cost = 0;
			$set_a = array(0,$cost);
		}
	}
	if ($set_a) {
		$credit->addLog('main_showsign',array($db_signcurtype => -$set_a[1]),array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip
		));
		$credit->set($winduid,$db_signcurtype,-$set_a[1],false);
		$credit->runsql();
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), array('starttime' => $set_a[0]));
	}
	return true;
}
?>
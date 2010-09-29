<?php
!defined('P_W') && exit('Forbidden');

InitGP(array('action','step'));

if (!$action) {
	if (!$step) {
		include_once(D_P.'data/bbscache/area_config.php');
		include PrintMode('static_manage');exit;
	} else {
		InitGP(array('statictime'),'P',2);
		$ChannelService = L::loadClass('channelService', 'area');
		$ChannelService->updateStaticTime($statictime);
		adminmsg('operate_success');
	}
}




?>
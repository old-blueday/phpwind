<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('action','step'));

if (!$action) {
	if (!$step) {
		//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
		pwCache::getData(D_P.'data/bbscache/area_config.php');
		include PrintMode('static_manage');exit;
	} else {
		S::gp(array('statictime','portalstatictime'),'P',2);
		$statictime = (int)$statictime > 0 ? (int)$statictime : 0;
		$ChannelService = L::loadClass('channelService', 'area');
		$ChannelService->updateStaticTime($statictime);
		setConfig('db_portalstatictime', $portalstatictime);
		updatecache_c();
		adminmsg('operate_success');
	}
}
?>
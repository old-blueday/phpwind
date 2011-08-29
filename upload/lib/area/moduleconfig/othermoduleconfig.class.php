<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('moduleconfig','area/base',false);
class PW_OtherModuleConfig extends PW_ModuleConfig{
	function afterUpdate($sign) {
		$portalPageService = L::loadClass('portalpageservice', 'area');
		$portalPageService->setPortalStaticState($channel,1);
		portalEot($sign);
	}
	
	function getPath($alias) {
		return S::escapePath(PORTAL_PATH.$alias);
	}
	function getType() {
		return 'other';
	}
}
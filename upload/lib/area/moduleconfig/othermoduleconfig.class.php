<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('moduleconfig','area/base',false);
class PW_OtherModuleConfig extends PW_ModuleConfig{
	function afterUpdate($sign) {
		portalEot($sign);
	}
	
	function getPath($alias) {
		return S::escapePath(PORTAL_PATH.$alias);
	}
}
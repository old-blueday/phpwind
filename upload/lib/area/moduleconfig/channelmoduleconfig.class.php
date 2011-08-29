<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('moduleconfig','area/base',false);
class PW_ChannelModuleConfig extends PW_ModuleConfig{
	function afterUpdate() {
		areaEot('main'); //实时更新模板
	}
	
	function getPath($alias) {
		return S::escapePath(AREA_PATH.$alias);
	}
	function getType() {
		return 'channel';
	}
}
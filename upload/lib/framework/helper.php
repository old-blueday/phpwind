<?php
!defined('P_W') && exit('Forbidden');
/**
 * 助手基类
 */
class Helper {
	/**
	 * 获取全局变量 全局安全类库
	 * @param $key 键名
	 */
	function _getGlobal($key) {
		return isset($GLOBALS[$key]) ? $GLOBALS[$key] : '';
	}
	/**
	 * 组装视图模板
	 * @param $controller
	 * @param $action
	 */
	function _buildTemplate($controller,$action){
		return $controller.'.'.$action;
	}
	
}
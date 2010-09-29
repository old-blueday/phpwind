<?php
!defined('P_W') && exit('Forbidden');
/*
 * 路由分发类
 */
class PW_Router {
	function run($configs) {
		list($controller, $action, $viewerPath, $className, $actionName, $path) = $this->init($configs);
		if (!is_file($path)) {
			Error::showError("路径不存在" . $path);
		}
		require_once S::escapePath($path);
		if (!class_exists($className, true)) {
			Error::showError("类名不存在" . $className);
		}
		$obj = new $className();
		if ($action && !is_callable(array($obj,	$action))) {
			Error::showError("方法名不存在" . $action);
		}
		if (in_array($action, array($className,"execute","__construct","init","before","after"))) {
			Error::showError("方法调用有误" . $className);
		}
		$obj->execute($controller, $action, $viewerPath);
	}
	function init($configs) {
		$this->_check();
		if (!S::IsArray($configs)) {
			Error::showError("请指定路由配置");
		}
		$controller = ctype_alpha($configs['c']) ? strtolower(trim($configs['c'])) : "index";
		$action = ctype_alpha($configs['a']) ? strtolower(trim($configs['a'])) : "run";
		$className = $controller . "controller";
		$actionName = $action;
		$path = APP_CONTROLLER . $className . ".php";
		$viewerPath = APP_VIEWER;
		return array($controller,$action,$viewerPath,$className,$actionName,$path);
	}
	
	/**
	 * 检查初始化配置是否定义
	 */
	function _check() {
		if (!defined('APP_VIEWER') || !defined('APP_CONTROLLER')) {
			Error::showError("you shoule config APP_VIEWER|APP_CONTROLLER");
		}
	}
}
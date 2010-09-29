<?php
!defined('P_W') && exit('Forbidden');
/**
 * 基类控制层
 */
class Controller {
	var $_viewer; //全局视图变量
	var $_controller; //控制器
	var $_action; //动作
	var $_layoutFile; //布局文件
	var $_layoutExt = 'htm'; //布局文件后缀
	var $_viewPath; //视图路径
	var $_template; //视图模板
	var $_partial;//独立模板目录
	
	function __construct() {
		$this->_viewer = new stdClass();
		$this->_layoutExt = 'htm';
	}
	
	function Controller() {
		$this->__construct();
	}
	
	function execute($controller, $action, $viewerPath) {
		$this->_init($controller, $action, $viewerPath);
		if ($this->_before()) {
			$this->$action();
			$this->_after();
		}
		$this->_render();
		$this->_output();
	}
	/**
	 * 初始化抽象函数
	 */
	function _init($controller, $action, $viewerPath) {
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_setViewPath($viewerPath);
	}
	/**
	 * 执行动作前的操作
	 */
	function _before() {
		return true;
	}
	/**
	 * 默认执行动作
	 */
	function run() {
	}
	/**
	 * 执行动作后的操作
	 */
	function _after() {
	}
	/**
	 * 执行动作后的输出
	 */
	function _output() {
	}
	
	function _render() {
		$layoutService = L::loadClass('layout', 'framework');
		$layoutService->init($this->_viewPath, $this->_layoutFile, $this->_layoutExt);
		$layoutService->setPartial($this->_partial);
		$layoutService->setTemplate(($this->_template) ? $this->_template : $this->_controller . '.' . $this->_action);
		$layoutService->display($this->_layoutFile, $this->_viewer);
	}
	
	function _setTemplate($template){
		$this->_template = $template;
	}
	
	function _setViewPath($path){
		$this->_viewPath = $path;
	}
	
	function _setLayoutFile($file){
		$this->_layoutFile = $file;
	}
	
	function _setLayoutExt($ext){
		$this->_layoutExt = $ext;
	}
	
	function _setPartial($partial){
		$this->_partial = $partial;
	}
	
	/**
	 * 获取全局变量 全局安全类库
	 * @param $key 键名
	 */
	function _getGlobal($key) {
		return isset($GLOBALS[$key]) ? $GLOBALS[$key] : '';
	}
	/**
	 * 获取$_POST或$_GET参数
	 * @param array $params 变量数组
	 */
	function _gp($params) {
		if (!S::isArray($params)) return array();
		S::gp($params,null,1,false);
		$tmp = array();
		foreach ($params as $param) {
			$tmp[] = $this->_getGlobal($param);
		}
		return $tmp;
	}
	function _isPost() {
		return (strtolower($_SERVER['REQUEST_METHOD']) === 'post');
	}
}
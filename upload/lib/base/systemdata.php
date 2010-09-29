<?php
!defined('P_W') && exit('Forbidden');
class SystemData/*abstruct*/ {
	var $_lang=array();
	/**
	 * 获取数据对外接口
	 * @param array $config
	 * @param int $num
	 * return array
	 */
	function getSourceData($config,$num) {
	}
	/**
	 * 获取该数据源相关数据类型
	 * return string
	 */
	function getRelateType() {
	}
	/**
	 * 获取本数据源配置信息
	 */
	function getSourceConfig() {
	}
	/**
	 * 获取数据源的各个数据的名称
	 * @param string $key
	 * return string
	 */
	function getSourceLang($key) {
		$lang = $this->_lang;
		return isset($lang[$key]) ? $lang[$key] : '';
	}

}
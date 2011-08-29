<?php
/**
 * 错误与日志基类
 */
class PW_Errors {
	var $_errors = array(); //错误集合
	

	var $_logs = array(); //日志集合
	

	/**
	 * 添加一条错误信息
	 * @param $errorInfo	错误信息
	 */
	function addError($errorInfo) {
		$this->_errors[] = $errorInfo;
	}
	/**
	 * 添加一条提醒信息
	 * @param $logInfo
	 */
	function addLog($logInfo) {
		$this->_logs[] = $logInfo;
	}
	/**
	 * 记录错误信息
	 */
	function writeLog($method = 'rb+') {
		$logFile = D_P.'data/error.log';
		if (!$this->_logs) return false;
		$temp = pw_var_export($this->_logs);
		pwCache::setData($logFile,$temp, false, 'rb+');
	}
	/**
	 * 检查是否有错误信息，有的话及时报错
	 */
	function checkError($jumpurl = '') {
		foreach ($this->_errors as $error) {
			$this->showError($error,$jumpurl);
		}
	}
	/**
	 * 及时报错
	 * @param $error 错误信息
	 */
	function showError($error, $jumpurl = '') {
		Showmsg($error, $jumpurl);
	}
	
	function __destruct() {
		if (!defined('SHOWLOG')) return false;
		$this->writeLog();
	}
}
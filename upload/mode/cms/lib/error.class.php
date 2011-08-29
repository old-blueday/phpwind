<?php
!defined('P_W') && exit('Forbidden');

class PW_Error{
	var $_error = array();
	function PW_Error() {
		$this->__construct();
	}
	
	function addError($errorInfo) {
		$this->_error[] = $errorInfo;
	}
	
	function showError() {
		foreach ($this->_error as $value) {
			$this->_showError($value);
		}
	}
	
	function _showError($value) {
		Showmsg($value);
	}
	
	function __construct() {
		
	}
}
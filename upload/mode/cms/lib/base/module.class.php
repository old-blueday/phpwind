<?php
!defined('P_W') && exit('Forbidden');
class PW_Module {
	var $_error;
	function __construct() {
		$this->_error = C::loadClass('error');
	}
	function PW_Module() {
		$this->__construct();
	}
	
	function showError() {
		$this->_error->showError();
	}
	
	function addError($info) {
		$this->_error->addError($info);
	}
}

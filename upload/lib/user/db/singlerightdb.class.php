<?php
!defined('P_W') && exit('Forbidden');

class PW_SingleRightDB extends BaseDB {
	var $_tableName = "pw_singleright";
	var $_primaryKey = 'uid';
	
	function get($id) {
		return $this->_get($id);
	}
}
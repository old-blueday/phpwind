<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Forums {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Forums_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'insert', $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'update', $fields );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'delete', $fields );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Forums_Impl {
	function logForums($operate, $fields) {
		global $db_operate_log;
		if (! $db_operate_log || ! in_array ( 'db_log_forums', $db_operate_log ) || ! isset ( $fields ['fid'] )) {
			return false;
		}
		$forumIds = (is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] );
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logForums ( $operate, $fields );
	}
}
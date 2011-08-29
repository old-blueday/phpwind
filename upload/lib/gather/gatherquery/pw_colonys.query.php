<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Colonys {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Colonys_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logColonys ( 'insert', $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logColonys ( 'update', $fields );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logColonys ( 'delete', $fields );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Colonys_Impl {
	/*
	 * 记录群组新增/更新/删除操作 如果需要请部署(insert/update/delete)
	 */
	function logColonys($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['id'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_colonys', $db_operate_log ) || ! isset ( $fields ['id'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logColonys ( $operate, $fields );
	}
}
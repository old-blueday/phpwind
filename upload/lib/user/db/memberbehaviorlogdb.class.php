<?php
!defined('P_W') && exit('Forbidden');
class PW_MemberBehaviorLogDB extends BaseDB {
	var $_tableName = 'pw_member_behavior_log';
	var $_primaryKey = 'log_id';
	function get($logId) {
		return $this->_get($logId);
	}
	function insert($fieldData) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_insert($fieldData);
	}
	function update($fieldData,$logId){
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_update($fieldData,$logId);
	}
	function delete($logId){
		return $this->_delete($logId);
	}

	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}
	function getStruct() {
		return array('log_id','uid','behavior','change','timestamp');
	}
}
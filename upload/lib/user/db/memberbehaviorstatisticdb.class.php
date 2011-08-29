<?php
!defined('P_W') && exit('Forbidden');
class PW_MemberBehaviorStatisticDB extends BaseDB {
	var $_tableName = 'pw_member_behavior_statistic';
	function get($uid,$behavior) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($uid) . " AND behavior=" . $this->_addSlashes($behavior) . " LIMIT 1");
	}
	function insert($fieldData) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		if (!$fieldData['uid'] || !$fieldData['behavior']) return false;
		return pwQuery::insert($this->_tableName, $fieldData);
	}
	function update($fieldData,$uid,$behavior){
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return pwQuery::update($this->_tableName, "uid=:uid AND behavior=:behavior", array($uid,$behavior), $fieldData);
	}
	function delete($uid,$behavior){
		return pwQuery::delete($this->_tableName, "uid=:uid AND behavior=:behavior", array($uid,$behavior));
	}

	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}
	function getStruct() {
		return array('uid','behavior','lastday','num');
	}
}
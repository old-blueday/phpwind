<?php
!defined('P_W') && exit('Forbidden');
class PW_MedalApplyDB extends BaseDB {
	var $_tableName = 'pw_medal_apply';
	var $_primaryKey = 'apply_id';
	function get($applyId) {
		return $this->_get($applyId);
	}
	function insert($fieldData) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_insert($fieldData);
	}
	function update($fieldData,$applyId){
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_update($fieldData,$applyId);
	}
	function delete($applyId){
		return $this->_delete($applyId);
	}
	function deleteByMedalId($medalId) {
		$medalId = (int)$medalId;
		return pwQuery::delete($this->_tableName, "medal_id=:medal_id", array($medalId));
	}
	function getByUidAndMedalId($uid,$medalId) {
		$uid = (int) $uid;
		$medalId = (int) $medalId;
		$_sql = "SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($uid) . " AND medal_id=". $this->_addSlashes($medalId);
		return $this->_db->get_one($_sql);
	}
	function getUserMedalids($uid) {
		$query = $this->_db->query("SELECT medal_id FROM ".$this->_tableName." WHERE uid=".$this->_addSlashes($uid));
		$temp = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt['medal_id'];
		}
		return $temp;
	}
	function getAll($condition = array(),$page,$prePage=20) {
		$page = (int) $page;
		$page = $page-1;
		$page<=0 && $page =0;
		$_sql = $this->_cookSql($condition);
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." ".$_sql." ORDER BY apply_id DESC ".S::sqlLimit($page*$prePage,$prePage));
		$temp = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt;
		}
		return $temp;
	}
	function count($condition = array()) {
		$_sql = "SELECT COUNT(*) as count FROM ".$this->_tableName." ".$this->_cookSql($condition);
		return $this->_db->get_value($_sql);
	}
	
	function _cookSql($condition) {
		if (!S::isArray($condition)) return '';
		if (isset($condition['uid'])) return ' WHERE uid='.S::sqlEscape($condition['uid']).' ';
		if ($condition['medal_id']) return ' WHERE medal_id='.S::sqlEscape($condition['medal_id']).' ';
	}
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}
	function getStruct() {
		return array('apply_id','uid','medal_id','timestamp');
	}
}
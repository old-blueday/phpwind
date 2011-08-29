<?php
!defined('P_W') && exit('Forbidden');
class PW_MedalAwardDB extends BaseDB {
	var $_tableName = 'pw_medal_award';
	var $_primaryKey = 'award_id';
	function get($awardId) {
		return $this->_get($awardId);
	}
	function insert($fieldData) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_insert($fieldData);
	}
	function replace($fieldData) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return pwQuery::replace($this->_tableName,$fieldData);
	}
	function update($fieldData,$awardId){
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_update($fieldData,$awardId);
	}
	function updateByUidAndMedalId($fieldData,$uid,$medalId) {
		$fieldData	= $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return pwQuery::update($this->_tableName, "uid=:uid AND medal_id=:medal_id", array($uid,$medalId), $fieldData);
	}
	function updateDeadline($medelId,$time) {
		$medelId = (int) $medelId;
		$_sql = "UPDATE ".$this->_tableName." SET deadline=timestamp+".intval($time)." WHERE medal_id=".$this->_addSlashes($medelId);
		return $this->_db->update($_sql);
	}
	function delete($awardId){
		return $this->_delete($awardId);
	}
	function deleteByMedalId($medalId) {
		$medalId = (int)$medalId;
		return pwQuery::delete($this->_tableName, "medal_id=:medal_id", array($medalId));
	}
	function deleteByMedalIdAndUid($medalId,$uid) {
		$medalId = (int)$medalId;
		$uid = (int)$uid;
		return pwQuery::delete($this->_tableName, "medal_id=:medal_id AND uid=:uid", array($medalId,$uid));
	}
	function getByUidAndMedalId($uid,$medalId) {
		$uid = (int) $uid;
		$medalId = (int) $medalId;
		$_sql = "SELECT * FROM " . $this->_tableName . " WHERE medal_id=" . $this->_addSlashes($medalId) . " AND uid=". $this->_addSlashes($uid);
		return $this->_db->get_one($_sql);
	}
	function getUserMedals($uid) {
		$uid = (int) $uid;
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
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." ".$_sql." ORDER BY award_id DESC ".S::sqlLimit($page*$prePage,$prePage));
		$temp = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt;
		}
		return $temp;
	}
	function getAllOverdues() {
		global $timestamp;
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE deadline>0 AND deadline<".intval($timestamp)." LIMIT 1000");
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
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}
	function _cookSql($condition) {
		if (!S::isArray($condition)) return '';
		if ($condition['uid']) return ' WHERE uid='.S::sqlEscape($condition['uid']).' ';
		if ($condition['medal_id']) return ' WHERE medal_id='.S::sqlEscape($condition['medal_id']).' ';
		if (isset($condition['type'])) return ' WHERE type='.S::sqlEscape($condition['type']).' ';
	}
	function getStruct() {
		return array('award_id','medal_id','uid','type','timestamp','deadline');
	}
}
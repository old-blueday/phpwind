<?php
!defined('P_W') && exit('Forbidden');

class PW_ReplyRewardDB extends BaseDB {
	
	var $_tableName = 'pw_replyreward';
	var $_primaryKey = 'tid';
	var $_allowFields = array('tid', 'credittype', 'creditnum', 'rewardtimes', 'repeattimes', 'chance', 'lefttimes');
	
	function getRewardByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		return $this->_get($tid);
	}
	
	function getRewardByTids($tids) {
		if (!S::isArray($tids)) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($tids)));
		return $this->_getAllResultFromQuery($query, 'tid');
	}
	
	function addReward($data) {
		$data = $this->_checkAllowField($data, $this->_allowFields);
		if (!S::isArray($data)) return false;
		return $this->_insert($data);
	}
	
	function updateByTid($tid, $data) {
		$tid = intval($tid);
		$data = $this->_checkAllowField($data, $this->_allowFields);
		if ($tid < 1 || !S::isArray($data)) return false;
		return $this->_update($data, $tid);
	}
	
	function deleteByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		return $this->_delete($tid);
	}
	
	function deleteByTids($tids) {
		if (!S::isArray($tids)) return false;
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($tids));
	}
	
}
?>
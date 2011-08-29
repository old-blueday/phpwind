<?php
!defined('P_W') && exit('Forbidden');

class PW_ReplyRewardRecordDB extends BaseDB {
	
	var $_tableName = 'pw_replyrewardrecord';
	var $_allowFields = array('tid', 'pid', 'uid', 'credittype', 'creditnum', 'rewardtime');
	
	function getRewardRecordByTid($tid) {
		list($tid) = intval($tid);
		if ($tid < 1) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid=:tid", array($tid)));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getRewardRecordByUid($uid) {
		list($uid) = intval($uid);
		if ($uid < 1) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "uid=:uid", array($uid)));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getRewardRecordByUids($uids) {
		if (!S::isArray($uids)) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "uid IN(:uid)", array($uids)));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getRewardRecordByTidAndUid($tid, $uid) {
		list($tid, $uid) = array(intval($tid), intval($uid));
		if ($tid < 1 || $uid < 1) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid=:tid AND uid=:uid", array($tid, $uid)));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getRewardRecordByTidAndPid($tid, $pid) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		if ($tid < 1 || $pid < 1) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid=:tid AND pid=:pid", array($tid, $pid)));
		return $this->_db->fetch_array($query);
	}
	
	function getRewardRecordByTidAndPids($tid, $pids) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($pids)) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid=:tid AND pid IN(:pid)", array($tid, $pids)));
		return $this->_getAllResultFromQuery($query, 'pid');
	}
	
	function getRewardRecordByTids($tids) {
		if (!S::isArray($tids)) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid IN(:tid)", array($tids)));
		return $this->_getAllResultFromQuery($query);
	}
	
	function addRewardRecord($data) {
		$data = $this->_checkAllowField($data, $this->_allowFields);
		if (!S::isArray($data)) return false;
		return pwQuery::insert($this->_tableName, $data);
	}
	
	function updateRecordByTidAndPid($tid, $pid, $data) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		$data = $this->_checkAllowField($data, $this->_allowFields);
		if ($tid < 1 || $pid < 1 || !S::isArray($data)) return false;
		return pwQuery::update($this->_tableName, "tid=:tid AND pid=:pid", array($tid, $pid), $data);
	}
	
	function deleteByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		return pwQuery::delete($this->_tableName, "tid=:tid", array($tid));
	}
	
	function deleteByTids($tids) {
		if (!S::isArray($tids)) return false;
		return pwQuery::delete($this->_tableName, "tid IN(:tid)", array($tids));
	}
	
	function deleteByTidAndPid($tid, $pid) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		if ($tid < 1 || $pid < 1) return false;
		return pwQuery::delete($this->_tableName, "tid=:tid AND pid=:pid", array($tid, $pid));
	}
	
	function deleteByTidAndPids($tid, $pids) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($pids)) return false;
		return pwQuery::delete($this->_tableName, "tid=:tid AND pid IN(:pid)", array($tid, $pids));
	}
	
	function countRecordsByTidAndUid($tid, $uid) {
		list($tid, $uid) = array(intval($tid), intval($uid));
		if ($tid < 1 || $uid < 1) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "tid=:tid AND uid=:uid", array($tid, $uid), array(PW_EXPR => array('COUNT(*) AS total'))));
		$result = $this->_db->fetch_array($query);
		return $result['total'];
	}
}
?>
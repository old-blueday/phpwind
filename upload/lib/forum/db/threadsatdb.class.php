<?php
!defined('P_W') && exit('Forbidden');

class PW_ThreadsatDb extends BaseDB {
	var $_tableName  = 'pw_threads_at';
	var $_primaryKey = 'id';
	
	function adds($tid,$pid,$uids){
		$tid = intval($tid);
		$pid = intval($pid);
		if (!$tid || !S::isArray($uids)) {
			return false;
		}
		$data = array();
		foreach ($uids as $v) {
			$data[] = array(
				'tid' => $tid,
				'pid' => $pid,
				'uid' => intval($v)
			);
		}
		$data && $this->_db->update("REPLACE INTO {$this->_tableName} (tid,pid,uid) VALUES " . S::sqlMulti($data));
	}
	
	function gets($tid,$pids){
		$tid = intval($tid);
		if (!$tid || !S::isArray($pids)) {
			return false;
		}
		$query = $this->_db->query("SELECT pid,uid FROM {$this->_tableName} WHERE tid=$tid AND pid IN( " . S::sqlImplode($pids) . ')');
		return $this->_getAllResultFromQuery($query);
	}
	
	function delete($tid,$pids){
		$tid = intval($tid);
		if (!$tid || !S::isArray($pids)) {
			return false;
		}
		return $this->_db->update("DELETE FROM {$this->_tableName} WHERE tid=$tid AND pid IN( " . S::sqlImplode($pids) . ')');
	}
	
	function deleteByUids($tid,$pid,$uids){
		$tid = intval($tid);
		$pid = intval($pid);
		if (!$tid || !S::isArray($uids)) {
			return false;
		}
		return $this->_db->update("DELETE FROM {$this->_tableName} WHERE tid=$tid AND pid=$pid AND uid IN( " . S::sqlImplode($uids) . ')');
	}
}
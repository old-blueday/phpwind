<?php
!defined('P_W') && exit('Forbidden');

/**
 * ThreadCache
 * 
 * @package Thread
 */
class PW_Threads {
	var $_db;
	var $_connect = FALSE;
	var $_expire = 3600;
	var $_exist = FALSE;
	var $_prefixThreads = "threads_";
	var $_prefixTmsgs = "tmsgs_";
	
	function PW_Threads() {
		$this->_db = $GLOBALS['db'];
		if ($this->_isMemecacheOpen()) {
			$this->_exist = TRUE;
		}
	}
	
	function getThreads($tid, $isDetailed = false) {
		if ($this->_exist == FALSE) {
			return ($isDetailed ? $this->_getThreadsAndTmsgsByTidNoCache($tid) : $this->_getThreadsByTidNoCache($tid));
		}
		return ($isDetailed ? array_merge($this->_getThreadsByTid($tid), $this->_getTmsgsByTid($tid)) : $this->_getThreadsByTid($tid));
	}
	
	function delThreads($tids) {
		if ($this->_exist == FALSE) return null;
		if (is_array($tids)) {
			foreach ($tids as $tid) {
				$this->_delThreadsByTid($tid);
				$this->_delTmsgsByTid($tid);
			}
		} else {
			$this->_delThreadsByTid($tids);
			$this->_delTmsgsByTid($tids);
		}
	}
	
	function clearThreadByThreadId($threadId) {
		return $this->_delThreadsByTid($threadId);
	}
	
	function clearTmsgsByThreadId($threadId) {
		return $this->_delTmsgsByTid($threadId);
	}
	
	function _delThreadsByTid($tid) {
		if ($this->_exist == FALSE) return null;
		$key = $this->_getThreadsKey($tid);
		$memcacheConnection = $this->_getMemcacheConnection();
		$memcacheConnection->delete($key);
	}
	
	function _delTmsgsByTid($tid) {
		if ($this->_exist == FALSE) return null;
		$key = $this->_getTmsgsKey($tid);
		$memcacheConnection = $this->_getMemcacheConnection();
		$memcacheConnection->delete($key);
	}
	
	function _getThreadsByTid($tid) {
		if ($this->_exist == FALSE) return null;
		$key = $this->_getThreadsKey($tid);
		$memcacheConnection = $this->_getMemcacheConnection();
		$result = $memcacheConnection->get($key);
		if ($result === FALSE) {
			$result = $this->_getThreadsByTidNoCache($tid);
			if ($result) {
				$memcacheConnection->set($key, $result, $this->_expire);
			}
		}
		return $result ? $result : array();
	}
	
	function _getTmsgsByTid($tid) {
		if ($this->_exist == FALSE) return null;
		$key = $this->_getTmsgsKey($tid);
		$memcacheConnection = $this->_getMemcacheConnection();
		$result = $memcacheConnection->get($key);
		if ($result === FALSE) {
			$result = $this->_getTmsgsByTidNoCache($tid);
			if ($result) {
				$memcacheConnection->set($key, $result, $this->_expire);
			}
		}
		return $result ? $result : array();
	}
	
	function _getTmsgsByTidNoCache($tid) {
		$pw_tmsgs = GetTtable($tid);
		$read = $this->_db->get_one("SELECT * FROM $pw_tmsgs WHERE tid=" . pwEscape($tid));
		return $read;
	}
	
	function _getThreadsByTidNoCache($tid) {
		$read = $this->_db->get_one("SELECT * FROM pw_threads WHERE tid=" . pwEscape($tid));
		return $read;
	}
	
	function _getThreadsAndTmsgsByTidNoCache($tid) {
		$pw_tmsgs = GetTtable($tid);
		$read = $this->_db->get_one("SELECT t.* ,tm.* FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid WHERE t.tid=" . pwEscape($tid));
		return $read;
	}
	
	function _getThreadsKey($tid) {
		return $this->_prefixThreads . $tid;
	}
	
	function _getTmsgsKey($tid) {
		return $this->_prefixTmsgs . $tid;
	}
	
	function _getMemcacheConnection() {
		if ($this->_connect === FALSE) {
			$this->_connect = L::loadClass('Memcache', 'utility');
		}
		return $this->_connect;
	}
	
	function _getConnection() {
		return $GLOBALS['db'];
	}
	
	function _isMemecacheOpen() {
		return class_exists("Memcache") && strtolower($GLOBALS['db_datastore']) == 'memcache';
	}
}
?>
<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_ThreadsDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_threads';
	var $_tableName2 = 'pw_tmsgs';
	var $_primaryKey = 'tid';
	function getsBythreadIds($threadIds) {
		$threadIds = (is_array ( $threadIds )) ? $threadIds : explode ( ",", $threadIds );
		foreach ( $threadIds as $threadId ) {
			$table = GetTtable ( $threadId );
			$tables [$table] [] = $threadId;
		}
		$threads = array ();
		foreach ( $tables as $table => $tids ) {
			$t = $this->_getsBythreadIds ( $tids, $table );
			$threads = $threads + $t;
		}
		$tmp = array ();
		foreach ( $threads as $t ) {
			$tmp [$t ['tid']] = $t;
		}
		$result = array ();
		foreach ( $threadIds as $threadId ) {
			(isset ( $tmp [$threadId] )) ? $result [] = $tmp [$threadId] : '';
		}
		return $result;
	}
	
	function _getsBythreadIds($threadIds, $tmsgsTableName) {
		$this->_tableName2 = ($tmsgsTableName) ? $tmsgsTableName : $this->_tableName2;
		$threadIds = (is_array ( $threadIds )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $threadIds ) : $threadIds;
		$query = $this->_db->query ( "SELECT t.*,th.content FROM " . $this->_tableName . " t left join " . $this->_tableName2 . " th on t.tid=th.tid WHERE  t.tid in(" . $threadIds . ") ORDER BY t.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getThreadsByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		$threadIds = $this->_getThreadsByPage ( $start, $end );
		if (! $threadIds)
			return false;
		$tmp = array ();
		foreach ( $threadIds as $t ) {
			$tmp [] = $t ['tid'];
		}
		return $this->getsBythreadIds ( $tmp );
	}
	
	function _getThreadsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT t.tid FROM " . $this->_tableName . " t WHERE t.tid >= " . $this->_addSlashes ( $start ) . " AND t.tid <= " . $this->_addSlashes ( $end ) . " AND t.ifcheck = 1 AND t.fid != 0 " );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT t.tid FROM " . $this->_tableName . " t WHERE t.tid >= " . $this->_addSlashes ( $minId ) . " AND t.tid <= " . $this->_addSlashes ( $maxId ) . " AND t.ifcheck = 1 AND t.fid != 0 " );
		return $this->_getAllResultFromQuery ( $query );
	}

	function deleteThreadByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid = " . CLOUDWIND_SECURITY_SERVICE::sqlEscape($tid));
		return $this->_db->affected_rows();
	}
	
	function setThreadCheckedByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$this->_db->update("UPDATE " . $this->_tableName . " SET ifcheck = 1 WHERE tid = " . CLOUDWIND_SECURITY_SERVICE::sqlEscape($tid));
		return $this->_db->affected_rows();
	}
	
	function maxThreadId() {
		$result = $this->_db->get_one ( "SELECT max(tid) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countThreadsNum() {
		$result = $this->_db->get_one ( "SELECT count(tid) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}
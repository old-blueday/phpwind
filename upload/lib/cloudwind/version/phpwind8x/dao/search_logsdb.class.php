<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_LogsDb extends CloudWind_Base_Db {
	var $_demo = false;
	var $_fetchAll = false;
	var $_logTableNames = array ('pw_log_threads', 'pw_log_diary', 'pw_log_posts', 'pw_log_members', 'pw_log_forums', 'pw_log_colonys', 'pw_log_attachs', 'pw_log_weibos' );
	
	function getLogsBySegment($tableName, $startTime, $endTime, $page, $perpage) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		list ( $startTime, $endTime ) = $this->checkSegment ( $startTime, $endTime );
		$offset = intval ( ($page - 1) * $perpage );
		$limit = intval ( $perpage );
		if ($limit < 1)
			return false;
		$query = $this->_db->query ( "SELECT sid,operate FROM `" . addslashes ( $tableName ) . "`  WHERE modified_time >= " . $this->_addSlashes ( $startTime ) . " AND modified_time <= " . $this->_addSlashes ( $endTime ) . " LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function deleteLogsSegment($tableName, $startTime, $endTime) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		$total = $this->countLogsSegment ( $tableName, $startTime, $endTime );
		if ($total < 1) {
			return true;
		}
		list ( $startTime, $endTime ) = $this->checkSegment ( $startTime, $endTime );
		$this->_db->query ( "DELETE FROM `" . addslashes ( $tableName ) . "` WHERE modified_time >= " . $this->_addSlashes ( $startTime ) . " AND modified_time <= " . $this->_addSlashes ( $endTime ) );
		return $this->_db->affected_rows ();
	}
	
	function countLogsSegment($tableName, $startTime, $endTime) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		list ( $startTime, $endTime ) = $this->checkSegment ( $startTime, $endTime );
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM `" . addslashes ( $tableName ) . "` WHERE modified_time >= " . $this->_addSlashes ( $startTime ) . " AND modified_time <= " . $this->_addSlashes ( $endTime ) );
		return intval ( $result ['total'] );
	}
	
	function checkSegment($startTime, $endTime) {
		$startTime = intval ( $startTime );
		$endTime = intval ( $endTime );
		$startTime = ($startTime > 0) ? $startTime : 0;
		$endTime = ($endTime > 0) ? $endTime : CloudWind_getConfig ( 'g_timestamp' );
		return array ($startTime, $endTime );
	}
	
	function getLogs($tableName, $versionId, $page, $perpage) {
		if ($this->_fetchAll) {
			return $this->getAllLogs ( $tableName );
		}
		return $this->getLogsWithLimit ( $tableName, $versionId, $page, $perpage );
	}
	function getLogsByPage($tableName, $page, $perpage) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getLogsByPage ( $tableName, $start, $end );
	}
	
	function getLogsWithLimit($tableName, $versionId, $page, $perpage) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		$versionId = intval ( $versionId );
		if ($perpage < 1)
			return false;
		return $this->_getLogsWithLimit ( $tableName, $versionId, $start, $end );
	}
	
	function deleteLogsByVersionId($tableName, $versionId) {
		$versionId = intval ( $versionId );
		$this->_db->query ( "DELETE FROM `" . addslashes ( $tableName ) . "` WHERE modified_time <= " . $this->_addSlashes ( $versionId ) );
		return $this->_db->affected_rows ();
	}
	
	function countLogsByTypeAndTime($tableName, $starttime, $endtime) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		$result = $this->_db->get_one ( "SELECT count(*) as count FROM " . CLOUDWIND_SECURITY_SERVICE::sqlMetadata ($tableName) . " WHERE modified_time >= " . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $starttime ) . " AND modified_time <= " . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $endtime ) );
		return ($result && $result ['count'] > 0) ? $result ['count'] : 0;
	}
	
	function countLogs($tableName) {
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . addslashes ( $tableName ) );
		return intval ( $result ['total'] );
	}
	
	function _getLogsByPage($tableName, $start, $end) {
		$query = $this->_db->query ( "SELECT sid,operate FROM `" . addslashes ( $tableName ) . "` t WHERE id >= " . $this->_addSlashes ( $start ) . " AND id <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function _getLogsWithLimit($tableName, $versionId, $start, $end) {
		$query = $this->_db->query ( "SELECT sid,operate FROM `" . addslashes ( $tableName ) . "`  WHERE modified_time <= " . $this->_addSlashes ( $versionId ) . " LIMIT " . $start . "," . $end );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getAllLogs($tableName) {
		if (! in_array ( $tableName, $this->_logTableNames ))
			return false;
		return $this->_getAllLogs ( $tableName );
	}
	
	function _getAllLogs($tableName) {
		if ($this->_demo) {
			return $this->_getRands ();
		}
		$query = $this->_db->query ( "SELECT sid,operate FROM `" . addslashes ( $tableName ) . "`" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function _getRands() {
		$tmp = array ();
		for($i = 1; $i <= 20; $i ++) {
			$tmp [] = array ('sid' => rand ( 1, 100000 ), 'operate' => rand ( 0, 1 ) );
		}
		return $tmp;
	}

}
<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Defend_PostVerifyDb extends CloudWind_Base_Db {
	var $_tableName = "pw_log_postverify";
	var $_primaryKey = 'id';
	
	function replace($type, $tid, $pid) {
		if (! $tid || ! $type)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,type,tid,pid,modified_time) VALUES (null," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $type ) . "," . intval ( $tid ) . "," . intval ( $pid ) . "," . CloudWind_getConfig ( 'g_timestamp' ) . ")" );
	}
	
	function count() {
		return $this->_count ();
	}
	
	function gets($start, $end) {
		$query = $this->_db->query ( "SELECT * FROM `" . $this->_tableName . "` ORDER BY modified_time DESC LIMIT " . intval ( $start ) . "," . intval ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function deleteByTidAndPid($tid, $pid) {
		$query = $this->_db->query ( "DELETE FROM `" . $this->_tableName . "` WHERE tid=" . intval ( $tid ) . " AND pid=" . intval ( $pid ) );
		return $this->_getAllResultFromQuery ( $query );
	}

}
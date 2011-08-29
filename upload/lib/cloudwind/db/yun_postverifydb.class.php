<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 云盾审核记录表
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_PostVerifyDB extends YUN_BaseDB {
	var $_tableName = "pw_log_postverify";
	var $_primaryKey = 'id';
	
	function replace($type, $tid, $pid) {
		if (! $tid || ! $type)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,type,tid,pid,modified_time) VALUES (null," . pwEscape ( $type ) . "," . pwEscape ( $tid ) . "," . pwEscape ( $pid ) . "," . $GLOBALS ['timestamp'] . ")" );
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
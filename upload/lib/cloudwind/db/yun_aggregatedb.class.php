<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 聚合日志DAO
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_AggregateDB extends YUN_BaseDB {
	
	var $_tableName = 'pw_log_aggregate';
	
	function getAllLogs() {
		$query = $this->_db->query ( "SELECT sid,type,operate FROM `" . $this->_tableName . "` LIMIT 20" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function deleteAllLogs() {
		$this->_db->query ( "DELETE FROM `" . $this->_tableName . "`" );
		return $this->_db->affected_rows ();
	}

}
<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Platform_AggregateDb extends CloudWind_Base_Db {
	
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
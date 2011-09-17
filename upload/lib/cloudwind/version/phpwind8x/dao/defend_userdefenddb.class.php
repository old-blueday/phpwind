<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Defend_UserDefendDb extends CloudWind_Base_Db {
	
	var $_tableName = 'pw_log_userdefend';
	
	function getAll() {
		$query = $this->_db->query ( "SELECT * FROM `" . $this->_tableName . "`" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function deleteAll() {
		$this->_db->query ( "DELETE FROM `" . $this->_tableName . "`" );
		return $this->_db->affected_rows ();
	}

}
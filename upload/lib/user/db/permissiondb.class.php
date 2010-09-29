<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_PermissionDB extends BaseDB {
	var $_tableName = "pw_permission";
	function getsByRkey($rKey){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE rkey = ".$this->_addSlashes($rKey) );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>
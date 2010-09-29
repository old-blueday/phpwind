<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_CmembersDB extends BaseDB {
	var $_tableName = "pw_cmembers";
	var $_primaryKey = 'id';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}
	function delete($id){
		return $this->_delete($id);
	}
	function get($id){
		return $this->_get($id);
	}
	function count(){
		return $this->_count();
	}
	function getUserIdsByColonyId($colonyId){
		$query = $this->_db->query ( "SELECT uid  FROM " . $this->_tableName. " WHERE colonyid = ".$this->_addSlashes($colonyId));
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>
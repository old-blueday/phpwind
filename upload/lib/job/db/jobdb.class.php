<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_JobDB extends BaseDB {
	var $_tableName = "pw_job";

	function add($fieldData) {
		$this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id ();
	}

	function update($fieldData, $id) {
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . "WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function delete($id) {
		$this->_db->update ( "DELETE FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function get($id) {
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}

	function getAll() {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName." ORDER BY sequence ASC " );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function gets($offset,$limit) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " ORDER BY sequence ASC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function count() {
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName);
		return $result['total'];
	}
	
	function getByIds($ids) {
		if(!is_array($ids)){
			return array();
		}
		$ids = implode(",",$ids);
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE id in(".$ids.")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getByJobName($jobName){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE job=".$this->_addSlashes ( $jobName ) );
		return $this->_getAllResultFromQuery ( $query );
	}

	function getByAuto(){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE auto = 1" );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>
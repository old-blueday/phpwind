<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_JobDoerDB extends BaseDB {
	var $_tableName = "pw_job_doer";

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

	function gets() {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName );
		return $this->_getAllResultFromQuery ( $query );
	}

}
?>
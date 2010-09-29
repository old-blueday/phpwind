<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_AreaLevelDB extends BaseDB {
	var $_tableName = "pw_area_level";

	function add($fieldData) {
		$this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id ();
	}
	
	function update($fieldData, $userId) {
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . " WHERE uid=" . $this->_addSlashes ( $userId ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function delete($userId) {
		$this->_db->update ( "DELETE FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes ( $userId ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function get($userId) {
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes ( $userId ) . " LIMIT 1" );
	}

	function count() {
		$result = $this->_db->get_one ( "SELECT COUNT(*) AS total FROM " . $this->_tableName);
		return $result ['total'];
	}
	
	function gets($page,$perpage) {
		$start = intval(($page-1)*$perpage);
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " limit ".$start .",". $perpage  );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getAll() {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName);
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>
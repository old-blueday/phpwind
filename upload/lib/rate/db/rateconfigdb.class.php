<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_RateConfigDB extends BaseDB {
	var $_tableName = "pw_rateconfig";

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

	/**
	 * 获取某个分类下的评价选项
	 *
	 * @param int $typeId
	 * @return array
	 */
	function getsByTypeId($typeId) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE typeid=" . $typeId );
		return $this->_getAllResultFromQuery ( $query );
	}

	function getStruct() {
		return array ('id', 'title', 'icon', 'isopen', 'isdefault', "typeid", "creditset", "voternum", "authornum", "creator", "created_at", "updater", "update_at" );
	}

}
?>
<?php
!defined('P_W') && exit('Forbidden');

class PW_BanUserDB extends BaseDB {
	var $_tableName = "pw_banuser";
	var $_primaryKey = 'id';
	
	function add($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function findAllByUserId($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return array();
		
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($userId));
		return $this->_getAllResultFromQuery($query);
	}
	
	function deleteByUserId($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return 0;
		
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}

}

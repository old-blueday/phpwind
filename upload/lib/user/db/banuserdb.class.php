<?php
!defined('P_W') && exit('Forbidden');

class PW_BanUserDB extends BaseDB {
	var $_tableName = "pw_banuser";
	var $_primaryKey = 'id';
	
	function add($fieldData) {
		$pwSQL = S::sqlSingle($fieldData);
		return $this->_db->update("REPLACE INTO pw_banuser SET $pwSQL");
		//return $this->_insert($fieldData);
	}
	
	function findAllByUserId($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return array();
		
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($userId));
		return $this->_getAllResultFromQuery($query);
	}

	function checkByUidFid($uid,$fid){
		$uid = intval($uid);
		$fid = intval($fid);
		if ($uid <= 0) return array();
		return intval($this->_db->get_value(
			"SELECT COUNT(*) FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($uid) . " AND fid=".$this->_addSlashes($fid)
		));
	}
	
	function deleteByUserId($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return 0;
		
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}

}

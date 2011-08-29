<?php
!defined('P_W') && exit('Forbidden');

class PW_WeiboLoginUserDB extends BaseDB {
	var $_tableName = "pw_weibo_login_user";
	
	function add($fields) {
		if (!is_array($fields) || !count($fields)) return 0;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fields));
		return $this->_db->affected_rows();
	}
	
	function get($userId) {
		if ($userId <= 0) return null;
		$data = $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid=" . intval($userId));
		if (!$data) return null;
		
		return $data;
	}
	
	function update($userId, $fields) {
		if ($userId <= 0 || !is_array($fields) || !count($fields)) return 0;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fields) . " WHERE uid=" . intval($userId));
		return $this->_db->affected_rows();
	}
	
	function delete($userId) {
		if ($userId <= 0) return 0;
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE uid=" . intval($userId));
		return $this->_db->affected_rows();
	}
}


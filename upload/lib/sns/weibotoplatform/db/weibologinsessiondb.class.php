<?php
!defined('P_W') && exit('Forbidden');

class PW_WeiboLoginSessionDB extends BaseDB {
	var $_tableName = "pw_weibo_login_session";
	
	function get($sessionId) {
		if ('' == $sessionId) return null;
		$data = $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE sessionid=" . $this->_addSlashes($sessionId));
		if (!$data) return null;
		
		$data['sessiondata'] = $this->_decodeSessionData($data['sessiondata']);
		return $data;
	}
	
	function add($fields) {
		if (!isset($fields['sessionid']) || '' == $fields['sessionid']) return 0;
		if (isset($fields['sessiondata'])) $fields['sessiondata'] = $this->_encodeSessionData($fields['sessiondata']);
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fields));
		return $this->_db->affected_rows();
	}
	
	function update($sessionId, $fields) {
		if ('' == $sessionId || !$fields) return 0;
		if (isset($fields['sessiondata'])) $fields['sessiondata'] = $this->_encodeSessionData($fields['sessiondata']);
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fields) . " 
			WHERE sessionid=" . $this->_addSlashes($sessionId));
		return $this->_db->affected_rows();
	}
	
	function delete($sessionId) {
		if ('' == $sessionId) return 0;
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE sessionid=" . $this->_addSlashes($sessionId));
		return $this->_db->affected_rows();
	}
	
	function deletesByExpire($timestamp) {
		$timestamp = intval($timestamp);
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE expire<" . $this->_addSlashes($timestamp));
	}
	
	
	function _encodeSessionData($sessionData) {
		return '' == $sessionData ? '' : serialize($sessionData);
	}
	function _decodeSessionData($sessionDataString) {
		return '' == $sessionDataString ? '' : unserialize($sessionDataString);
	}
}


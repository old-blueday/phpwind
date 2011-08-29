<?php
!defined('P_W') && exit('Forbidden');

class PW_WeiboBindDB extends BaseDB {
	var $_tableName = "pw_weibo_bind";
	
	function get($userId, $weiboType) {
		if ($userId <= 0 || '' == $weiboType) return null;
		$data = $this->_db->get_one("SELECT * FROM " . $this->_tableName . " 
			WHERE uid=" . intval($userId) . " AND weibotype=" . $this->_addSlashes($weiboType));
		if (!$data) return null;
		
		$data['info'] = $this->_decodeBindingInfo($data['info']);
		return $data;
	}
	
	function gets($userIds) {
		if (!$userIds) return array();
		
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " 
			WHERE uid IN (" . $this->_getImplodeString($userIds) . ")");
		
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['info'] =  $this->_decodeBindingInfo($rt['info']);
			$result[$rt['weibotype']][$rt['uid']] = $rt;
		}
		return $result;
	}
	
	/**
	 * @param unknown_type $userId
	 * @return array weibotype as key
	 */
	function getAllByUserId($userId) {
		if ($userId <= 0) return array();
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid=" . intval($userId));
		
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['info'] =  $this->_decodeBindingInfo($rt['info']);
			$result[$rt['weibotype']] = $rt;
		}
		return $result;
	}
	
	function add($userId, $weiboType, $bindingInfo) {
		if ($userId <= 0 || '' == $weiboType) return 0;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString(array('uid' => $userId, 'weibotype' => $weiboType, 'info' => $this->_encodeBindingInfo($bindingInfo))));
		return $this->_db->affected_rows();
	}
	
	function update($userId, $weiboType, $bindingInfo) {
		if ($userId <= 0 || '' == $weiboType) return 0;
		$this->_db->update("UPDATE " . $this->_tableName . " SET info=" . $this->_addSlashes($this->_encodeBindingInfo($bindingInfo)) . " 
			WHERE uid=" . intval($userId) . " AND weibotype=" . $this->_addSlashes($weiboType));
		return $this->_db->affected_rows();
	}
	
	function delete($userId, $weiboType) {
		if ($userId <= 0 || '' == $weiboType) return 0;
		$this->_db->update("DELETE FROM " . $this->_tableName . " 
			WHERE uid=" . intval($userId) . " AND weibotype=" . $this->_addSlashes($weiboType));
		return $this->_db->affected_rows();
	}
	
	function _encodeBindingInfo($bindingInfo) {
		return '' == $bindingInfo ? '' : serialize($bindingInfo);
	}
	function _decodeBindingInfo($bindingInfoString) {
		return '' == $bindingInfoString ? '' : unserialize($bindingInfoString);
	}
}


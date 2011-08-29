<?php
!defined('P_W') && exit('Forbidden');

class PW_Attention_BlacklistDB extends BaseDB {

	var $_tableName = "pw_attention_blacklist";
	
	function getBlackList($uid) {
		$sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE uid=' . $this->_addSlashes($uid);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}

	function getBlackListToMe($uid, $uIds) {
		if (!$uid || !$uIds || !is_array($uIds)) {
			return array();
		}
		$sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE uid IN(' . $this->_getImplodeString($uIds) . ') AND touid=' . $this->_addSlashes($uid);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}

	function isInBlackList($uid, $touid) {
		$sql = 'SELECT uid FROM ' . $this->_tableName . ' WHERE uid=' . $this->_addSlashes($uid) . ' AND touid=' . $this->_addSlashes($touid);
		return $this->_db->get_value($sql);
	}

	function add($uid, $blackList) {
		if (!$uid || !$blackList || !is_array($blackList)) {
			return false;
		}
		$array = array();
		foreach ($blackList as $val) {
			$array[] = array($uid, $val);
		}
		$this->_db->update("INSERT INTO " . $this->_tableName . ' (uid, touid) VALUES ' . S::sqlMulti($array));
	}

	function del($uid, $blackList) {
		if (!$uid || !$blackList || !is_array($blackList)) {
			return false;
		}
		$this->_db->update('DELETE FROM ' . $this->_tableName . ' WHERE uid=' . $this->_addSlashes($uid) . ' AND touid IN(' . $this->_getImplodeString($blackList) . ')');
	}
}
?>
<?php
!defined('P_W') && exit('Forbidden');

class PW_KmdUserDB extends BaseDB {
	
	var $_tableName = 'pw_kmd_user';
	var $_primaryKey = 'uid';
	var $_allowFields = array('uid', 'phone', 'realname', 'invoice', 'address');
	
	/**
	 * 添加用户信息
	 *
	 * @param array $fieldData
	 * @return int
	 */
	function addKmdUser($fieldData){	
		$fieldData = $this->_checkAllowField($fieldData, $this->_allowFields);
		if (!S::isArray($fieldData)) return false;
		return pwQuery::replace($this->_tableName, $fieldData);
	}
	
	/**
	 * 删除用户信息
	 * @param int $uid
	 * @return bool
	 */
	function deleteKmdUserByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		return $this->_delete($uid);
	}
	
	/**
	 * 批量删除用户信息
	 * @param array $uids
	 * @return bool
	 */
	function deleteKmdUserByUids($uids) {
		if (!S::isArray($uids)) return false;
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($uids));
	}
	
	/**
	 * 更新用户信息
	 *
	 * @param array $fieldData
	 * @param int $uid
	 * @return bool
	 */
	function updateKmdUser($fieldData, $uid){
		list($fieldData, $uid) = array($this->_checkAllowField($fieldData, $this->_allowFields), intval($uid));
		if ($uid < 1 || !S::isArray($fieldData)) return false;
		return $this->_update($fieldData, $uid);
	}
	
	/**
	 * 获取用户信息
	 * @param int $uid
	 * @return array
	 */
	function getKmdUserByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		return $this->_get($uid);
	}
	
	/**
	 * 批量获取用户信息
	 * @param array $uids
	 * @return array
	 */
	function getKmdUsersByUids($uids) {
		if (!S::isArray($uids)) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($uids)));
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	/**
	 * 根据条件获取用户信息
	 * @param int uid
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	function getKmdUsersWithCondition($uid, $start, $limit) {
		list($uid, $start, $limit) = array(intval($uid), intval($start), intval($limit));
		$sql = $uid ? " AND km.uid=$uid" : '';
		$query = $this->_db->query("SELECT km.* FROM $this->_tableName km WHERE 1 $sql " . S::sqlLimit($start, $limit));
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	/**
	 * 根据条件统计数量
	 * @param int $uid
	 * @return int
	 */
	function countKmdUsersWithCondition($uid) {
		$uid = intval($uid);
		$sql = $uid ? " AND uid=$uid" : '';
		return $this->_db->get_value("SELECT COUNT(*) AS total FROM $this->_tableName WHERE 1 $sql");
	}
	
	/**
	 * 统计所有用户数量
	 * @return int
	 */
	function countKmdUsers() {
		$total = $this->_db->get_value(pwQuery::selectClause($this->_tableName, '', array(), array(PW_EXPR => array('COUNT(*) AS total'))));
		return $total;
	}
}
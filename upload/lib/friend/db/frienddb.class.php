<?php
!defined('P_W') && exit('Forbidden');

class PW_FriendDB extends BaseDB {

	var $_tableName = "pw_friends";
	var $_primaryKey = 'uid';
	
	function insert($fieldData) {//fixed
		return $this->_insert($fieldData);
	}
	
	/**
	 * 更新用户的状态
	 * 
	 * @param int $uid
	 * @param int $friendid
	 * @param array() $fieldData
	 */
	function updateByUidAndFid($uid ,$friendid, $fieldData) {//fixed
		$sql = "UPDATE " . $this->_tableName
				." SET " . $this->_getUpdateSqlString($fieldData, false)
				." WHERE uid=" . $this->_addSlashes($uid) . " AND friendid=" . $this->_addSlashes($friendid);
		return $this->_db->update($sql);
	}
		
	/**
	 * 根据用户和关注对象,找出相关信息
	 * 
	 * @param int	$uid	关注对象
	 * @param int	$friendid	被关注者uid
	 * @return	
	 */
	function getUserByUidAndFriendid($uid, $friendid) {//fixed
		$sql = "SELECT * FROM ".$this->_tableName. " WHERE uid = "
				. $this->_addSlashes($uid). " AND friendid = ".$this->_addSlashes($friendid);
		return $this->_db->get_one($sql);
	}
	
	/**
	 * 根据用户和好友对象，删除记录
	 * 
	 * 
	 * @param int $uid	用户
	 * @param int $friendid	好友uid
	 */
	function delByUidAndFriendid($uid, $friendid) {//fixed
		$sql = "DELETE FROM " . $this->_tableName . " WHERE uid="
				. $this->_addSlashes($uid) . " AND friendid=" . $this->_addSlashes($friendid);
		return $this->_db->update($sql);
	}

	function getFriendList($uid, $offset, $limit) {
		$sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE status=0 AND uid=' . $this->_addSlashes($uid) . $this->_limit($offset, $limit);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getFriendsByUid($uid) {//fixed
		$sql = 'SELECT * FROM '.$this->_tableName .' WHERE status=0 AND uid=' . $this->_addSlashes($uid);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function delFriendsByUids($uids) {//fixed
		if(!$uids) return false;
		$uids = $uids ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		return $this->_db->update("DELETE FROM " . $this->_tableName . " WHERE uid IN( " . $uids . " )");
	}
	
	function delFriendsByFriendsUids($uids) {//fixed
		if(!$uids) return false;
		$uids = $uids ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		return $this->_db->update("DELETE FROM " . $this->_tableName . " WHERE friendid IN( " . $uids . " )");
	}
}
?>
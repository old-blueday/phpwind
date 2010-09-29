<?php
!defined('P_W') && exit('Forbidden');

/**
 * 收藏数据层
 * 
 * @package PW_CollectionDB
 * @author	lmq
 * @abstract
 */

class PW_CollectionDB extends BaseDB {
	var $_tableName 	= 	"pw_collection";
	var $_primaryKey 	= 	'id';

	function insert($fieldDate) {
		return $this->_insert($fieldDate);
	}
	
	function delete($ids) {
		if (!$ids) return false; 
		$ids = is_array($ids) ? $this->_getImplodeString($ids) : $this->_addSlashes($ids);
		$sql = "DELETE FROM " . $this->_tableName . " WHERE id IN(" . $ids . ")";
		return $this->_db->update($sql);
	}

	function deleteByUids($uids) {
		if (!$uids) return false; 
		$uids = is_array($uids) ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		return $this->_db->update("DELETE FROM " . $this->_tableName . " WHERE uid IN(" . $uids . ")");
	}
	
	function get($id) {
		return $this->_get($id);
	}

	function countByUid($uid) {
		if (!$uid) return false; 
		$sql = "SELECT COUNT(id) FROM ".$this->_tableName. " WHERE uid=".$this->_addSlashes($uid).
				" AND type IN(".$this->_getImplodeString($this->getTypeMap()). ") AND ifhidden=0";
		return $this->_db->get_value($sql);
	}
	
	function countByUidAndType($uid,$type) {
		if (!$uid || !$type) return false; 
		$sql = "SELECT COUNT(id) FROM ".$this->_tableName. " WHERE uid=".$this->_addSlashes($uid).
				"AND type=".$this->_addSlashes($type)." AND ifhidden=0";
		return $this->_db->get_value($sql);
	}
	
	function findByUid($uid, $offset, $limit) {
		if (!$uid) return false; 
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName. " WHERE uid=".$this->_addSlashes($uid).
				" AND type IN(".$this->_getImplodeString($this->getTypeMap()). ") AND ifhidden=0 ORDER BY id DESC" .$this->_Limit($offset, $limit));
		return $this->_getAllResultFromQuery($query);
	}
	
	function findByUidAndType($uid, $type, $offset, $limit) {
		if (!$uid || !$type) return false; 
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName. " WHERE uid=".$this->_addSlashes($uid).
				"AND type=".$this->_addSlashes($type).
				" AND ifhidden=0 ORDER BY id DESC" .$this->_Limit($offset, $limit));
		return $this->_getAllResultFromQuery($query);
	}

	function getByTypeAndTypeid($uid ,$type, $typeid) {
		if (!$type || !$typeid) return false;
		$sql = "SELECT * FROM ".$this->_tableName. " WHERE uid=".$this->_addSlashes($uid)." AND type=".$this->_addSlashes($type).
				" AND typeid=".$this->_addSlashes($typeid);
		return $this->_db->get_one($sql);
	}
	
	/**
	 * 收藏类型map图 
	 */
	function getTypeMap(){
		$typeList = array(
			'0' => 'weibo',//新鲜事
			'1' => 'diary',//日志
			'2' => 'photo', //相册
			'3' => 'group', //群组
			'4' => 'active',//活动能够
			'5' => 'web', //网页
			'6' => 'multimedia',//多媒体
			'7' => 'cms',//文章
		);
		return $typeList;
	}
}
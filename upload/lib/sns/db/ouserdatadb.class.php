<?php
/**
 * 记录数据库操作对象
 * 
 * @package Write
 */

!defined('P_W') && exit('Forbidden');

/**
 * 用户相关数据库操作对象
 * 
 * @package Ouserdata 
 */
class PW_OuserdataDB extends BaseDB {

	var $_tableName = "pw_ouserdata";
	
	/**
	 * 根据用户范围，筛选记录权限
	 * 
	 * @param array		$userIds		array(1,2,3,4,......n)	
	 * @return array 	
	 */ 
	function findUserOwritePrivacy($userIds) {
		$qurey = $this->_db->query(" SELECT * FROM ".$this->_tableName . 
				" WHERE uid " . $this->_parseSqlIn($userIds) . " AND owrite_privacy !=2");
		return $this->_getAllResultFromQuery($qurey);
	}

	function findUserPhotoPrivacy($userIds) {
		if(empty($userIds) || !is_array($userIds)){
			return array();
		}
		$qurey = $this->_db->query(" SELECT photos_privacy,uid FROM ".$this->_tableName . 
				" WHERE uid " . $this->_parseSqlIn($userIds) . " AND owrite_privacy !=2");
		return $this->_getAllResultFromQuery($qurey);
	}
	
	/**
	 * 根据用户id，解析in 查询
	 * 
	 * @param $userIds
	 */
	function _parseSqlIn($userIds) {
		return "IN (" . S::sqlImplode($userIds) . ")";
	}
	
	/**
	 * 根据用户id，取得用户相关信息
	 * 
	 * @param $userId
	 */
	function get($userId) {
		static $sArray = array();
		if (!isset($sArray[$userId])) {
			$sArray[$userId] = $this->_get($userId);
		}
		return $sArray[$userId];
	}

	function _get($userId) {
		return $this->_db->get_one( "SELECT * FROM " . $this->_tableName . " WHERE uid=" . $this->_addSlashes($userId));
	}
}
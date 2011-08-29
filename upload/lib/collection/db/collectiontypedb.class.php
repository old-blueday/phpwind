<?php
!defined('P_W') && exit('Forbidden');

/**
 * 收藏分类数据层
 * 
 * @package PW_CollectionTypeDB
 * @author	panjl
 * @abstract
 */

class PW_CollectionTypeDB extends BaseDB {
	var $_tableName 	= 	"pw_collectiontype";
	var $_primaryKey 	= 	'ctid';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 新增id
	 */
	function insert($fieldsData) {
		if (S::isArray($fieldsData)) {
			return $this->_insert($fieldsData);
		}
	}

	/**
	 * 编辑
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @param int $ctid 分类ID
	 * @return int 最新编辑的分类ID
	 */
	function update($fieldsData,$ctid) {
		$ctid = intval($ctid);
		if ($ctid < 1) return null;
		if (S::isArray($fieldsData)) {
			return $this->_update($fieldsData,$ctid);
		}
	}

	/**
	 * 删除
	 * 
	 * @param int $ctid  分类ID
	 * @return int 删除行数
	 */
	function delete($ctid) {
		$ctid = intval($ctid);
		if ($ctid < 1) return null;
		return $this->_delete($ctid);
	}

	/**
	 * 根据分类ID取分类信息
	 * 
	 * @param int $ctid  分类ID
	 * @return array 
	 */
	function getTypeByCtid($ctid) {
		$ctid = intval($ctid);
		if ( $ctid < 1 ) return array();
		return $this->_db->get_one ("SELECT * FROM ".$this->_tableName." WHERE ctid = ". S::sqlEscape($ctid));
	}

	/**
	 * 根据用户uid取分类信息
	 * 
	 * @param int $uid  用户uid
	 * @return array 
	 */
	function getTypesByUid($uid) {
		$uid = intval($uid);
		if ( $uid < 1 ) return array();
		$query = $this->_db->query ( "SELECT * FROM ".$this->_tableName." WHERE uid = ". S::sqlEscape($uid));
		return $this->_getAllResultFromQuery ($query);
	}

	/**
	 * 按uid检测分类是否存在
	 * 
	 * @param int $userId 用户uid
	 * @param string $typeName
	 * @return int ctid
	 */
	function getCtidByUidAndName($uid, $typeName) {
		$uid = intval($uid);
		if ( $uid < 1 || !$typeName ) return 0;
		return $this->_db->get_value( "SELECT ctid FROM ".$this->_tableName." WHERE uid = ". S::sqlEscape($uid) . " AND name = " . S::sqlEscape($typeName));
	}

}
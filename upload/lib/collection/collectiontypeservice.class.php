<?php
!defined('P_W') && exit('Forbidden');

/**
 * 收藏分类service
 * 
 * @package PW_CollectionTypeService
 * @author	panjl
 * @abstract
 */

class PW_CollectionTypeService {
	
	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 新增id
	 */
	function insert ($fieldsData) {
		if (S::isArray($fieldsData)) {
			$typeDb = $this->_getCollectionTypeDB();
			return $typeDb->insert($fieldsData);
		}
	}

	/**
	 * 编辑
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @param int $ctid 分类ID
	 * @return int 最新编辑的分类ID
	 */
	function update ($fieldsData, $ctid) {
		$ctid = intval($ctid);
		if ($ctid < 1) return NULL;
		if (S::isArray($fieldsData)) {
			$typeDb = $this->_getCollectionTypeDB();
			return $typeDb->update($fieldsData, $ctid);
		}
	}

	/**
	 * 删除
	 * 
	 * @param int $ctid  分类ID
	 * @return int 删除行数
	 */
	function delete ($ctid) {
		$ctid = intval($ctid);
		if ($ctid != '-1' && $ctid < 1) return null;
		$typeDb = $this->_getCollectionTypeDB();
		return $typeDb->delete($ctid);
	}

	/**
	 * 按用户ID和分类名查ctid
	 *
	 * @param int $uid 用户uid
	 * @param string $typeName 分类名
	 * @return int 分类ID
	 */
	function getCtidByUidAndName($uid, $typeName) {
		$uid = (int)$uid;
		if ( $uid < 1 || !$typeName ) return 0;
		$typeDb = $this->_getCollectionTypeDB();
		return $typeDb->getCtidByUidAndName($uid, $typeName);
	}

	/**
	 * 按uid检测分类是否存在
	 *
	 * @param int $uid 用户uid
	 * @param string $typeName 分类名
	 * @param int ctid 分类ID
	 * @return boolen 
	 */
	function checkTypeExist($uid, $typeName, $ctid=null) {
		global $winduid;
		$uid = (int)$uid;
		$ctid   = (int)$ctid;
		if ( !$typeName || ($uid != $winduid) ) return false;
		if ( !$ctid ) {
			$isExistType = $this->getCtidByUidAndName($uid,$typeName);
			if ($isExistType > 0) return false;
		} else {
			$typeDb = $this->getTypeByCtid($ctid);
			if ($typeDb['name'] != $typeName) {
				$isExistType = $this->getCtidByUidAndName($uid,$typeName);
				if ($isExistType > 0) return false;
			}
		}
		 return true;
	}

	/**
	 * 根据用户id 查找收藏分类
	 *
	 * @param int $uid 用户uid
	 * @return array 收藏分类
	 */
	function getTypesByUid($uid) {
		$uid = (int)$uid;
		if ( $uid < 1 ) return array();
		$typeDb = $this->_getCollectionTypeDB();
		return $typeDb->getTypesByUid($uid);
	}

	/**
	 * 根据分类ID 查找收藏分类
	 *
	 * @param int $ctid 分类ID
	 * @return array
	 */
	function getTypeByCtid($ctid) {
		$ctid = (int)$ctid;
		if ( $ctid < 1 ) return array();
		$typeDb = $this->_getCollectionTypeDB();
		return $typeDb->getTypeByCtid($ctid);
	}

	/**
	 * get PW_CollectionTypeDB
	 * 
	 * @access protected
	 * @return PW_CollectionTypeDB
	 */
	function _getCollectionTypeDB() {
		return L::loadDB('CollectionType', 'collection');
	}
}


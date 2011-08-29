<?php
!defined('P_W') && exit('Forbidden');
/**
 * 友情链接关系服务层
 * @package  PW_SharelinksRelationService
 * @author panjl @2010-11-5
 */
class PW_SharelinksRelationService {

	/**
	 * 加载dao
	 * 
	 * @return PW_SharelinksRelationDB
	 */
	function _getTypeDB() {
		return L::loadDB('SharelinksRelation', 'site');
	}

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 新增id
	 */
	function insert($fieldsData) {
		if (S::isArray($fieldsData)) {
			$typeDb = $this->_getTypeDB();
			return $typeDb->insert($fieldsData);
		}
	}

	/**
	 * 根据分类ID删除关系数据
	 * 
	 * @param int $stid  链接分类ID
	 * @return int 删除行数
	 */
	function deleteByStid($stid) {
		$stid = intval($stid);
		if ($stid < 1) return null;
		$typeDb = $this->_getTypeDB();
		return $typeDb->deleteBySid($stid);
	}

	/**
	 * 根据链接ID删除关系数据
	 * 
	 * @param int $sid  链接ID
	 * @return int 删除行数
	 */
	function deleteBySid($sid) {
		$sid = intval($sid);
		if ($sid < 1) return null;
		$typeDb = $this->_getTypeDB();
		return $typeDb->deleteBySid($sid);
	}

	/**
	 * 根据链接ID查分类ID
	 * 
	 * @param int $sid 友情链接ID
	 * @return int 分类ID
	 */
	function findStidBySid($sid) {
		$sid = intval($sid);
		if ($sid < 1) return null;
		$typeDb = $this->_getTypeDB();
		$typeNames = $typeDb->findStidBySid($sid);
		foreach ($typeNames as $value) {
			$names[] = $value['stid'];
		}
		return $names;
	}

	/**
	 * 根据分类ID查链接ID
	 * 
	 * @param int $stid 分类ID
	 * @return array 数组友情链接ID
	 */
	function findSidByStid($stid) {
		$stid = intval($stid);
		if ($stid < 1) return array();
		$typeDb = $this->_getTypeDB();
		$stidsArray = $typeDb->findSidByStid($stid);
		$stids = array();
		foreach ($stidsArray as $sids) {
			$sid[] = $sids['sid'];
		}
		return $sid;
	}
}
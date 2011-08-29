<?php
!defined('P_W') && exit('Forbidden');
/**
 * 友情链接分类服务层
 * @package  PW_SharelinksTypeService
 * @author panjl @2010-11-5
 */
class PW_SharelinksTypeService {

	/**
	 * 加载dao
	 * 
	 * @return PW_SharelinkstypeDB
	 */
	function _getTypeDB() {
		return L::loadDB('sharelinkstype', 'site');
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
	 * 删除
	 * 
	 * @param int $typeId  分类ID
	 * @return int 删除行数
	 */
	function delete($typeId) {
		$typeId = intval($typeId);
		if ($typeId < 1) return null;
		$typeDb = $this->_getTypeDB();
		return $typeDb->delete($typeId);
	}

	/**
	 * 编辑
	 * 
	 * @param array $typeName 数据数组，以数据库字段为key
	 * @param int $typeId 分类ID
	 * @return int 最新编辑的分类ID
	 */
	function update($fieldsData,$typeId) {
		if (!intval($typeId)) return null;
		if (S::isArray($fieldsData)) {
			$typeDb = $this->_getTypeDB();
			return $typeDb->update($fieldsData,$typeId);
		}
	}

	/**
	 * 根据分类name查stid
	 * 
	 * @param string 分类名称
	 * @return array 查询结果数组
	 */
	function getTypeIdByName($name) {
		if ( !$name ) return array();
		$typeDb = $this->_getTypeDB();
		return $typeDb->getTypeIdByName($name);
	}

	/**
	 * 根据分类stid查name
	 * 
	 * @param stid 分类id
	 * @return array 查询结果数组
	 */
	function getTypesByStid($stid) {
		$stid = intval($stid);
		if ( !$stid ) return array();
		$typeDb = $this->_getTypeDB();
		return $typeDb->getTypesByStid($stid);
	}

	/**
	 * 查询链接分类
	 * 
	 * @return array 查询结果
	 */
	function getAllTypes() {
		$typeDb = $this->_getTypeDB();
		return $typeDb->getAllTypes();
	}

	/**
	 * 查询所有链接分类
	 * 
	 * @return array 查询结果
	 */
	function getAllTypesName() {
		$typeDb = $this->_getTypeDB();
		return $typeDb->getAllTypesName();
	}
}

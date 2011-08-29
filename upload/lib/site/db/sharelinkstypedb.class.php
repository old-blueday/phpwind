<?php
!defined('P_W') && exit('Forbidden');

/**
 * 友情链接数据层
 * 
 * @package PW_SharelinkstypeDB
 * @author	panjl @2010-11-5
 */

class PW_SharelinkstypeDB extends BaseDB {
	var $_tableName 	= 	'pw_sharelinkstype';
	var $_primaryKey 	= 	'stid';

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
	 * 删除
	 * 
	 * @param int $typeId  分类ID
	 * @return int 删除行数
	 */
	function delete($typeId){
		$typeId = intval($typeId);
		if ($typeId < 1) return null;
		return $this->_delete($typeId);
	}

	/**
	 * 编辑
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @param int $typeId 分类ID
	 * @return int 最新编辑的分类ID
	 */
	function update($fieldsData,$typeId){
		if (S::isArray($fieldsData)) {
			return $this->_update($fieldsData,$typeId);
		}
	}

	/**
	 * 根据分类name查stid
	 * 
	 * @param string 分类名称
	 * @return int stid值
	 */
	function getTypeIdByName($name) {
		if ( !$name ) return null;
		return $this->_db->get_one('SELECT stid FROM ' . $this->_tableName . ' WHERE ifable <> 0 AND name= ' . S::sqlEscape($name));
	}

	/**
	 * 根据分类stid查name
	 * 
	 * @param stid 分类id
	 * @return array 查询结果数组
	 */
	function getTypesByStid($stid) {
		$stid = intval($stid);
		if ( !$stid ) return null;
		return $this->_db->get_one('SELECT name FROM ' . $this->_tableName . ' WHERE ifable <> 0 AND stid= ' . S::sqlEscape($stid));
	}

	/**
	 * 查询启用的链接分类
	 * 
	 * @return array 查询结果
	 */
	function getAllTypes() {
		$query = $this->_db->query('SELECT stid,name,ifable,vieworder FROM ' . $this->_tableName . ' WHERE ifable <> 0 ORDER BY vieworder ASC');
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 查询所有链接分类
	 * 
	 * @return array 查询结果
	 */
	function getAllTypesName() {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' ORDER BY vieworder ASC');
		return $this->_getAllResultFromQuery($query);
	}
}
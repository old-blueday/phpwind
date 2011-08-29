<?php
!defined('P_W') && exit('Forbidden');

/**
 * 友情链接关系数据层
 * 
 * @package PW_SharelinkstypeDB
 * @author	panjl
 * @abstract
 */

class PW_SharelinksRelationDB extends BaseDB {
	var $_tableName 	= 	'pw_sharelinksrelation';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 新增id
	 */
	function insert($fieldsData) {
		if (S::isArray($fieldsData)) {
			pwQuery::insert($this->_tableName, $fieldsData);
			return $this->_db->insert_id();
		}
	}

	/**
	 * 根据分类ID删除数据
	 * 
	 * @param int $typeId 
	 * @return int 删除行数
	 */
	function deleteByStid($stid){
		$stid = intval($stid);
		if ($stid < 1) return null;
		pwQuery::delete($this->_tableName, "stid=:stid", array($stid));
		return $this->_db->affected_rows();
	}

	/**
	 * 根据链接ID删除数据
	 * 
	 * @param int $sid  链接ID
	 * @return int 删除行数
	 */
	function deleteBySid($sid) {
		$sid = intval($sid);
		if ($sid < 1) return null;
		pwQuery::delete($this->_tableName, "sid=:sid", array($sid));
		return $this->_db->affected_rows();
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
		$query = $this->_db->query('SELECT stid FROM ' . $this->_tableName . ' WHERE sid = ' . S::sqlEscape($sid) .' ORDER BY stid ASC');
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 按分类ID查所有sid
	 * 
	 * @param int $stid 分类ID
	 * @return array 数组链接ID
	 */
	function findSidByStid($stid) {
		$stid = intval($stid);
		if ($stid < 1) return array();
		$query = $this->_db->query('SELECT sid FROM ' . $this->_tableName . ' WHERE stid = ' . S::sqlEscape($stid));
		return $this->_getAllResultFromQuery($query);
	}

}
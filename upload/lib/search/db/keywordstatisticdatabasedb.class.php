<?php
!defined('P_W') && exit('Forbidden');

/**
 * @package  PW_KeywordStatisticDatabaseDb
 * @author panjl @2011-6-10
 */
class PW_KeywordStatisticDatabaseDb extends BaseDB {
	var $_tableName 	= 	'pw_temp_keywords';
	var $_primaryKey 	= 	'id';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData
	 * @return int
	 */
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		return $this->_insert($fieldsData);
	}

	/**
	 * 批量删除标签
	 * 
	 * @return boolean
	 */
	function deleteAll() {
		return pwQuery::delete($this->_tableName, "", array());
	}
	
	/**
	 * 从数据库中获得最后更新时间
	 * 
	 * @return array
	 */
	function getLastUpdateTime() {
		return $this->_db->get_value("SELECT MAX(created_time) FROM " . $this->_tableName );
	}

	
	/**
	 * 获取所有关键词
	 * 
	 * @return array
	 */
	function getAllKeywords() {
		$query = $this->_db->query("SELECT keyword FROM " .  $this->_tableName);
		return $this->_getAllResultFromQuery($query);
	}
}
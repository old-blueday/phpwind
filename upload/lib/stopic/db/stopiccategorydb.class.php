<?php
/**
 * 专题分类记录数据库操作对象
 * 
 * @package STopic
 */

!defined('P_W') && exit('Forbidden');

/**
 * 专题分类记录数据库操作对象
 * 
 * 封装了专题分类记录的增删改查等操作，为PW_STopicService提供数据库操作
 * 
 * @package STopic
 */
class PW_STopicCategoryDB extends BaseDB {
	
	var $_tableName = "pw_stopiccategory";
	
	function add($fieldData) {
		$fieldData = $this->_checkData($fieldData);
		if (!$fieldData) return null;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData));
		return $this->_db->insert_id();
	}
	
	function update($fieldData, $id) {
		$fieldData = $this->_checkData($fieldData);
		if (!$fieldData) return null;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . "WHERE id=" . $this->_addSlashes($id) . " LIMIT 1");
		return $this->_db->affected_rows();
	}
	
	function delete($id) {
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes($id) . " LIMIT 1");
		return $this->_db->affected_rows();
	}
	
	function get($id) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes($id) . " LIMIT 1");
	}
	
	function getByName($name) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE title=" . $this->_addSlashes($name) . " LIMIT 1");
	}
	
	function gets() {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " ");
		return $this->_getAllResultFromQuery($query);
	}
	
	function count() {
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " LIMIT 1");
	}
	
	/**
	 * 分类允许字段
	 *
	 * @return array
	 */
	function getStruct() {
		return array(
			'id',
			'title',
			'status',
			'num',
			'creator',
			'createtime'
		);
	}
	
	function _checkData($data) {
		if (!is_array($data) || !count($data)) Showmsg('data_is_not_array');
		$data = $this->_checkAllowField($data, $this->getStruct());
		return $data;
	}
}

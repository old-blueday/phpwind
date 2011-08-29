<?php
!defined('P_W') && exit('Forbidden');

class PW_CustomerFieldDB extends BaseDB {
	
	var $_tableName = "pw_customfield";
	var $_primaryKey = 'id';

	/**
	 * 
	 * 根据字段ID获取字段信息
	 * @param int $id fieldid
	 * @return array
	 */
	function get($id) {
		return $this->_get($id);
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function update($fieldData, $id) {
		return $this->_update($fieldData, $id);
	}
	
	function delete($fieldId) {
		$fieldId = (int) $fieldId;
		if ($fieldId < 1) return false;
		return $this->_delete($fieldId);
	}
	
	/**
	 * 
	 * 根据字段分类名获取字段列表
	 * @param string $categoryName
	 * @return array
	 */
	function getFieldsByCategoryName($categoryName){
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE category = " . S::sqlEscape($categoryName) . ' AND state = 1 ORDER BY vieworder ASC');
		return $this->_getAllResultFromQuery($query,$this->_primaryKey);
	}
	
	function getFieldByFieldName($fieldName) {
		if (!$fieldName) return false;
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE fieldname = " . S::sqlEscape($fieldName) .' limit 1');
	}
	
	/**
	 * 
	 * 根据资料首次填写区域获取字段列表
	 * @param int $complement
	 * @return array
	 */
	function getFieldsByComplement($complement) {
		$complement = (int) $complement;
		if (!S::inArray($complement, array(0,1,2))) return array();
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' WHERE complement = ' . S::sqlEscape($complement) . ' AND state = 1 ORDER BY vieworder ASC');
		return $this->_getAllResultFromQuery($query,$this->_primaryKey);
	}
	
	/**
	 * 分页取得所有字段信息
	 * @param int $start 起始位置
	 * @param int $num	 数量
	 * @return array
	 */
	function getAllFieldsWithPages($start, $num) {
		$fields = array();
		$start = (int) $start;
		$num = (int) $num;
		if ($start < 0 || $num < 1) return $fields;
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' ORDER BY vieworder ASC' . S::sqlLimit($start, $num));
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}
	
	/**
	 * 统计所有字段数目
	 * @return int
	 */
	function countAllFields() {
		return $this->_db->get_value('SELECT COUNT(*) as total FROM ' . $this->_tableName);
	}
}
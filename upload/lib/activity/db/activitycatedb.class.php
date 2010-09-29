<?php
/**
 * 活动主分类
 * 
 * @package activity
 */

!defined('P_W') && exit('Forbidden');

class PW_ActivityCateDB extends BaseDB {
	var $_tableName = 'pw_activitycate';
	var $_primaryKey = 'actid';
	
	function update($id, $fieldData) {
		return $this->_update($fieldData,$id);
	}
	function getCates() {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' ORDER BY vieworder, ' . $this->_primaryKey);
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}
	function get($id) {
		return $this->_get($id);
	}
	function delete($id) {
		return $this->_delete($id);
	}
}
<?php
/**
 * 活动主分类
 * 
 * @package activity
 */

!defined('P_W') && exit('Forbidden');

class PW_ActivityModelDB extends BaseDB {
	var $_tableName = 'pw_activitymodel';
	var $_primaryKey = 'actmid';
	function update($id, $fieldData) {
		return $this->_update($fieldData,$id);
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function getModels() {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' ORDER BY vieworder, ' . $this->_primaryKey);
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}
	
	function getModelsByCateId($id) {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' WHERE actid=' . $this->_addSlashes($id) . ' ORDER BY vieworder, ' . $this->_primaryKey);
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}
	
	function get($id) {
		return $this->_get($id);
	}
	
	function getFirstModelByCateId($id) {
		return $this->_db->get_one('SELECT * FROM ' . $this->_tableName . ' WHERE actid=' . $this->_addSlashes($id).' ORDER BY vieworder ASC');
	}
	
	function countModelByCateIdAndName($cateId, $name) {
		return $this->_db->get_value('SELECT COUNT(*) AS total FROM ' . $this->_tableName . ' WHERE actid=' . $this->_addSlashes($cateId).' AND name=' . $this->_addSlashes($name));
	}
	
	function countModelByCateId($cateId) {
		return $this->_db->get_value('SELECT COUNT(*) AS total FROM ' . $this->_tableName . ' WHERE actid=' . $this->_addSlashes($cateId));
	}
	
	function updateModelByCateIdInIds($cateId, $modelIds, $fieldData) {
		return $this->_db->update('UPDATE ' . $this->_tableName . ' SET ' . $this->_getUpdateSqlString($fieldData) . ' WHERE actid=' . $this->_addSlashes($cateId) . ' AND ' . $this->_primaryKey . ' IN(' . $this->_getImplodeString($modelIds) . ')');
	}
	
	function updateModelByCateIdNotInIds($cateId, $modelIds, $fieldData) {
		return $this->_db->update('UPDATE ' . $this->_tableName . ' SET ' . $this->_getUpdateSqlString($fieldData) . ' WHERE actid=' . $this->_addSlashes($cateId) . ' AND ' . $this->_primaryKey . ' NOT 
		IN(' . $this->_getImplodeString($modelIds) . ')');
	}
}
<?php
/**
 * 记录表情数据库操作对象
 * 
 * @package Smile
 */

!defined('P_W') && exit('Forbidden');

/**
 * 记录表情数据库操作对象
 * 
 * @package Smile
 */
class PW_SmileDB extends BaseDB {
	var $_tableName = "pw_write_smiles";
	var $_cacheKey = "writesmiles.php";
	
	function findByTypeId($typeId) {
		$data = array();
		if (file_exists($this->_getCacheKey())) {
			include S::escapePath($this->_getCacheKey());
		} else {
			$data = $this->findByTypeIdWithoutCache($typeId);
			pwCache::setData($this->_getCacheKey(), '<?php $data = ' . var_export($data, true) . ';');
		}
		return $data;
	}
	
	function findByTypeIdWithoutCache($typeId) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE typeid=" . intval($typeId) . " ORDER BY vieworder");
		return $this->_getAllResultFromQuery($query);
	}
	
	function delete($smileId) {
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE smileid=" . intval($smileId) . " LIMIT 1");
		$deletes = $this->_db->affected_rows();
		
		if ($deletes) $this->_cleanCache();
		return $deletes;
	}
	
	function deleteByTypeId($typeId) {
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE typeid=" . intval($typeId));
		$deletes = $this->_db->affected_rows();
		
		if ($deletes) $this->_cleanCache();
		return $deletes;
	}
	
	function update($smileId, $fieldsData) {
		if ($smileId <= 0 || !is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData) . " WHERE smileid=" . intval($smileId) . " LIMIT 1");
		$updates = $this->_db->affected_rows();
		
		if ($updates) $this->_cleanCache();
		return $updates;
	}
	
	function add($fieldsData) {
		if (!is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData));
		$insertId = $this->_db->insert_id();
		
		if ($insertId) $this->_cleanCache();
		return $insertId;
	}
	
	function _getCacheKey() {
		return D_P . "data/bbscache/" . $this->_cacheKey;
	}
	
	function _cleanCache() {
		pwCache::deleteData($this->_getCacheKey());
	}

}

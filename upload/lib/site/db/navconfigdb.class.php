<?php
/**
 * 导航配置数据库操作对象
 * 
 * @package Nav
 */

!defined('P_W') && exit('Forbidden');

/**
 * 导航配置数据库操作对象
 * 
 * @package Nav
 */
class PW_NavConfigDB extends BaseDB {
	var $_tableName = "pw_nav";
	var $_cachePrefix = "navcache_";
	
	function add($fieldsData) {
		if (!is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData));
		$insertId = $this->_db->insert_id();
		
		if ($insertId) $this->_cleanCache($fieldsData['type']);
		return $insertId;
	}
	
	function get($navId) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE nid=" . $this->_addSlashes($navId));
	}
	
	function getByKey($navKey, $navType='') {
		$navKey = trim($navKey);
		if ('' == $navKey) return null;
		$addSql = $navType ? " AND type=".$this->_addSlashes($navType) : '';
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE nkey=" . $this->_addSlashes($navKey) . $addSql);
	}
	
	function update($navId, $fieldsData) {
		if ($navId <= 0 || !is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData) . " WHERE nid=" . intval($navId) . " LIMIT 1");
		$updates = $this->_db->affected_rows();
		
		$this->_cleanCache();
		return $updates;
	}
	
	function updateByKey($navKey, $fieldsData) {
		$navKey = trim($navKey);
		if ('' == $navKey || !is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData) . " WHERE nkey=" . $this->_addSlashes($navKey) . " ");
		$updates = $this->_db->affected_rows();
		
		if ($updates) $this->_cleanCache();
		return $updates;
	}
	
	function deletes($navIds) {
		if (!is_array($navIds) || !count($navIds)) return 0;
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE nid IN (" . $this->_getImplodeString($navIds) . ")");
		$deletes = $this->_db->affected_rows();
		
		if ($deletes) $this->_cleanCache();
		return $deletes;
	}
	
	function deleteByKey($navKey) {
		$navKey = trim($navKey);
		if ('' == $navKey) return 0;
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE nkey=" . $this->_addSlashes($navKey) . " ");
		$deletes = $this->_db->affected_rows();
		
		if ($deletes) $this->_cleanCache();
		return $deletes;
	}
	
	function deleteByType($navType) {
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE type=" . $this->_addSlashes($navType) . " ");
		$deletes = $this->_db->affected_rows();
		
		if ($deletes) $this->_cleanCache($navType);
		return $deletes;
	}
	
	function findByType($navType) {
		$data = array();
		if (file_exists($this->_getCacheKey($navType))) {
			include $this->_getCacheKey($navType);
		} else {
			$data = $this->findByTypeWithoutCache($navType);
			writeover($this->_getCacheKey($navType), '<?php $data = ' . var_export($data, true) . ';');
		}
		return $data;
	}
	
	function findByTypeWithoutCache($navType) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE type=" . $this->_addSlashes($navType) . " ORDER BY upid,view");
		return $this->_getAllResultFromQuery($query);
	}
	
	function findSubNavsByType($navType, $parentNavId) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE type=" . $this->_addSlashes($navType) . " AND upid=" . $this->_addSlashes($parentNavId) . " ORDER BY view");
		return $this->_getAllResultFromQuery($query);
	}
	
	
	
	function _getCacheKey($navType) {
		return D_P . "data/bbscache/" . $this->_cachePrefix . $navType . ".php";
	}
	
	function _cleanCache($navType = '') {
		if ('' == $navType) {
			foreach ($this->_getNavTypes() as $navType) {
				P_unlink($this->_getCacheKey($navType));
			}
		} elseif($this->_checkNavType($navType)) {
			P_unlink($this->_getCacheKey($navType));
		}
	}
	
	function _checkNavType($navType) {
		return in_array($navType, $this->_getNavTypes());
	}
	
	function _getNavTypes() {
		return array(PW_NAV_TYPE_MAIN, PW_NAV_TYPE_HEAD_LEFT, PW_NAV_TYPE_HEAD_RIGHT, PW_NAV_TYPE_FOOT);
	}
}

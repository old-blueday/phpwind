<?php

! defined ( 'P_W' ) && exit ( 'Forbidden' );

class GatherCache_PW_Cache_Cache extends GatherCache_Base_Cache {
	
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'cache_';
	
	function getCacheByName($name){
		if (!$name) return false;
		$result = $this->_cacheService->get($this->_getCacheDataKey($name));
		if (!is_array($result)) {
			$result = (array)$this->_getCacheDataByNameNoCache($name);
			$this->_cacheService->set($this->_getCacheDataKey($name), $result);
		}
		return $result;
	}
	
	function _getCacheDataByNameNoCache($name) {
		global $db;
		if (!$name) return false;
		return $db->get_one("SELECT * FROM pw_cache WHERE name=" . S::sqlEscape($name));
	}
	
	function clearCacheByName($name) {
		if (!$name) return false;
		$this->_cacheService->delete($this->_getCacheDataKey($name));
		return true;
	}

	function _getCacheDataKey($name) {
		return $this->_prefix . '_name_' . $name;
	}
}
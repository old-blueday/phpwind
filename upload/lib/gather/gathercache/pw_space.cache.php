<?php

! defined ( 'P_W' ) && exit ( 'Forbidden' );

class GatherCache_PW_Space_Cache extends GatherCache_Base_Cache {
	
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'space_';
	
	function getSpaceByUid($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		$result = $this->_cacheService->get($this->_getSpaceDataKey($uid));
		if (!is_array($result)) {
			$result = $this->_getSpaceDataByUidNoCache($uid);
			$this->_cacheService->set($this->_getSpaceDataKey($uid), $result);
		}
		return $result;
	}
	
	function _getSpaceDataByUidNoCache($uid) {
		global $db;
		$uid = intval($uid);
		if ($uid < 1) return false;
		$space = $db->get_one("SELECT * FROM pw_space WHERE uid=" . S::sqlEscape($uid));
		return $space;
	}
	
	function clearCacheForSpaceByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		$this->_cacheService->delete($this->_getSpaceDataKey($uid));
		return true;
	}

	function _getSpaceDataKey($uid) {
		return $this->_prefix . '_uid_' . $uid;
	}
}
<?php
!defined('P_W') && exit('Forbidden');

class PW_PageCacheService {

	function __construct() {
		
	}
	function PW_PageCacheService() {
		
	}
	
	function getDataBySigns($array) {
		$pageCacheDB = $this->_getPageCacheDB();
		return $pageCacheDB->gets($array);
	}
	function update($sign,$array) {
		$pageCacheDB = $this->_getPageCacheDB();
		return $pageCacheDB->update($sign,$array);
	}
	
	function updates($array) {
		$pageCacheDB = $this->_getPageCacheDB();
		$pageCacheDB->updates($array);
	}
	
	function relpace($array) {
		$pageCacheDB = $this->_getPageCacheDB();
		$pageCacheDB->replace($array);
	}
	
	function deleteCache($sign) {
		$pageCacheDB = $this->_getPageCacheDB();
		$pageCacheDB->delete($sign);
	}
	
	function truncateCache() {
		$pageCacheDB = $this->_getPageCacheDB();
		$pageCacheDB->truncate();
	}
	
	function deleteCacheByType($type) {
		$pageCacheDB = $this->_getPageCacheDB();
		$pageCacheDB->deleteByType($type);
	}
	
	function _getPageCacheDB() {
		return L::loadDB('pagecache','pagecache');
	}
}
?>
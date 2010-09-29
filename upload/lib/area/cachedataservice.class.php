<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户化页面缓存数据表
 * @author xiejin
 *
 */
class PW_CacheDataService {

	function updateCacheDataPiece($invokepieceid) {
		$this->deleteCacheData($invokepieceid);
	}

	function deleteCacheData($invokepieceid) {
		$cacheDataDB = $this->_getCacheDataDB();
		$cacheDataDB->deleteData($invokepieceid);
	}

	function deleteCacheDatas($ids) {
		$cacheDataDB = $this->_getCacheDataDB();
		$cacheDataDB->deleteDatas($ids);
	}
	
	function updateCacheDatas($datas) {
		$cacheDataDB = $this->_getCacheDataDB();
		$cacheDataDB->updates($datas);
	}
	
	function _getCacheDataDB() {
		return L::loadDB('CacheData', 'area');
	}
}
<?php

! defined ( 'P_W' ) && exit ( 'Forbidden' );

class GatherCache_PW_Userapp_Cache extends GatherCache_Base_Cache {
	
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'userapp_';
	/*
	function getUserappCacheByUidAndAppid($uid,$appid){
		if (!$name) return false;
		$result = $this->_cacheService->get($this->_getUserappCacheDataKey($uid,$appid));
		if (!is_array($result)) {
			$result = (array)$this->_getUserappCacheDataByUidAndAppidNoCache($uid,$appid);
			$this->_cacheService->set($this->_getUserappCacheDataKey($uid,$appid), $result);
		}
		return $result;
	}
	*/
	function getUserappsCacheByUid($uid){
		$uid = intval($uid);
		if (!$uid < 1) return false;
		$result = $this->_cacheService->get($this->_getUserappsCacheDataKey($uid));
		if (!is_array($result)) {
			$result = $this->_getUserappsCacheDataByUidsNoCache($uid);
			$result = (array)$result[$uid];
			$this->_cacheService->set($this->_getUserappsCacheDataKey($uid), $result);
		}
		return $result;
	}
	
	function getUserappsCacheByUids($uids){
		is_numeric($uids) && $uids = array(intval($uids));
		if (! S::isArray ( $uids )) {
			return false;
		}
		$uids = array_unique ( $uids );
		$result = $_tmpResult = $keys = $_tmpUids = array ();
		foreach ( $uids as $uid ) {
			$keys[$this->_getUserappsCacheDataKey ($uid)] = $uid;
		}
		if (($userApps = $this->_cacheService->get(array_keys($keys)))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$uid){
				$_key = $_unique . $key;
				if (isset($userApps[$_key]) && is_array($userApps[$_key])){
					$_tmpUids [] = $uid;
					$result[$uid] = $userApps[$_key];
				}
			}
		}
		$uids = array_diff ( $uids, $_tmpUids );
		if ($uids) {
			$_tmpResult = $this->_getUserappsCacheDataByUidsNoCache ($uids);
			foreach ($uids as $uid){
				$this->_cacheService->set ( $this->_getUserappsCacheDataKey($uid), isset($_tmpResult[$uid]) ? $_tmpResult[$uid] : array());
			}
		}
		return (array)$result + (array)$_tmpResult;
	}
	
	/*
	function _getUserappCacheDataByUidAndAppidNoCache($uid,$appid) {
		global $db;
		$uid = intval($uid);
		$appid = intval($appid);
		if (!$uid < 1 || $appid < 1) return false;
		return $db->get_one("SELECT * FROM pw_userapp WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
	}
	*/
	function _getUserappsCacheDataByUidsNoCache($uids) {
		global $db;
		if (is_numeric($uids)) $uids = array(intval($uids));
		if (!is_array($uids)) return false;
		$apps = array();
		$query = $db->query("SELECT * FROM pw_userapp WHERE uid IN (". S::sqlImplode($uids). ')');
		while ($rt = $db->fetch_array($query)) {
			$apps[$rt['uid']] = $rt;
		}
		return $apps;
	}
	
	function clearCacheByUid($uid) {
		$uid = intval($uid);
		if (!$uid < 1) return false;
		$this->_cacheService->delete($this->_getUserappsCacheDataKey($uid));
		return true;
	}
	/*
	function _getUserappCacheDataKey($uid,$appid) {
		return $this->_prefix . '_uid_' . $uid . '_appid_' . $appid;
	}
	*/
	function _getUserappsCacheDataKey($uid){
		return $this->_prefix . '_uid_' . $uid;
	}
}
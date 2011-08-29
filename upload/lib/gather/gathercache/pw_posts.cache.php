<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherCache_PW_Posts_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'posts_';
	
	function getFirstPostsByTid($postTable,$tid,$limit,$offset) {
		$tid = S::int($tid);
		if ($tid < 1) return false;
		if (! $this->checkMemcache()) {
			return $this->_getFirstPostNoCache($postTable,$tid,$limit,$offset);
		}
		$key = $this->_getPostsKey($tid,$limit);
		$result = $this->_cacheService->get($key);
		if ($result === false) {
			$result = $this->_getFirstPostNoCache($postTable,$tid,$limit,$offset);
			$this->_cacheService->set($key, $result);
			if (count($result) < $offset) {
				$this->_cacheService->set($this->_getPostsLastKey($tid), $limit ,false);
			}
		}
		return $result;
	}
	
	function clearCacheForLastPage($tid) {
		$limit = $this->_cacheService->get($this->_getPostsLastKey($tid));
		if (!$limit) return $this->clearCacheForThreadPost($tid);
		$key = $this->_getPostsKey($tid,$limit);
		$this->_cacheService->delete($key);
	}
	
	function clearCacheForThreadPost($tid) {
		$this->_cacheService->increment($this->_getKeyForReadPostVersion($tid));
	}
	
	function _getPostsLastKey($tid) {
		return $this->_prefix . 'tid_' . $tid.'_last_ver_'.$this->_getPostsVersionId($tid);
	}
	
	function _getPostsKey($tid,$limit) {
		return $this->_prefix . 'tid_' . $tid.'_'.$limit.'_ver_'.$this->_getPostsVersionId($tid);
	}
	
	function _getPostsVersionId($tid){
		$key = $this->_getKeyForReadPostVersion($tid);
		$versionId = $this->_cacheService->get($key);
		if (!$versionId){
			$versionId = 1;
			$this->_cacheService->set($key, $versionId, 3600*24);
		}
		return $versionId;
	}
	
	function _getKeyForReadPostVersion($tid){
		return $this->_prefix . 'tid_version_' . $tid;
	}
	
	function _getFirstPostNoCache($postTable,$tid,$limit,$offset) {
		$readdb = array();
		$limit = S::sqlLimit($limit,$offset);
		$query = $GLOBALS['db']->query("SELECT t.* FROM $postTable t WHERE t.tid=".S::sqlEscape($tid)." AND t.ifcheck='1' ORDER BY t.postdate ASC $limit");
		while ($read = $GLOBALS['db']->fetch_array($query)) {
			$readdb[] = $read;
		}
		return $readdb;
	}
	
}
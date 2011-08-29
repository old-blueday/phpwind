<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherCache_PW_Threads_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'thread_';
	 
	/**
	 * 获取一条帖子基本信息
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */
	function getThreadByThreadId($threadId) {
		$threadId = S::int($threadId);
		if ($threadId < 1) return false;
		if (! $this->checkMemcache()) {
			return $this->_getThreadNoCache($threadId);
		}
		$key = $this->_getKeyForThread($threadId);
		$result = $this->_cacheService->get($key);
		if ($result === false) {
			$result = $this->_getThreadNoCache($threadId);
			$this->_cacheService->set($key, $result);
		}
		return $result;
	}
	
	/**
	 * 获取一组帖子的基本信息
	 *
	 * @param array $threadIds 帖子id数组
	 * @return array
	 */	
	function getThreadsByThreadIds($threadIds) {
		if (! S::isArray ( $threadIds )) {
			return array();
		}
		if (!$this->checkMemcache()) {
			return $this->_getThreadsNoCache($threadIds);
		}
		$result = $resultInCache = $resultInDb = $keys = $_cachedThreadIds = array ();
		foreach ( $threadIds as $threadId ) {
			$keys [] = $this->_getKeyForThread ( $threadId );
		}
		if (($threads = $this->_cacheService->get ( $keys ))) {
			foreach ( $threads as $value ) {
				$_cachedThreadIds [] = $value ['tid'];
				$resultInCache [$value ['tid']] = $value;
			}
		}
		$_noCachedThreadIds = array_diff ( $threadIds, $_cachedThreadIds );
		if ($_noCachedThreadIds && ($resultInDb = $this->_getThreadsNoCache ( $_noCachedThreadIds ))) {
			foreach ( $resultInDb as $value ) {
				$this->_cacheService->set ( $this->_getKeyForThread ( $value ['tid'] ), $value );
			}
		}
		$tmpResult = (array)$resultInCache + (array)$resultInDb;
		foreach ($threadIds as $threadId){
			$result[$threadId] = isset($tmpResult[$threadId]) ? $tmpResult[$threadId] : false;
		}
		return $result;
	}
	
	/**
	 * 获取帖子基本信息和详细信息
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */
	function getThreadAndTmsgByThreadId($threadId) {
		$threadId = S::int($threadId);
		if ($threadId < 1) return false;
		if (! $this->checkMemcache ()) {
			return $this->_getThreadAndTmsgByThreadIdNoCache($threadId);
		}
		$threadKey = $this->_getKeyForThread($threadId);
		$tmsgKey = $this->_getKeyForTmsg($threadId);
		//* $result = $this->_cacheService->get(array($threadKey, $tmsgKey));
		//* $thread = isset($result[$threadKey]) ? $result[$threadKey] : false;
		//* $tmsg = isset($result[$tmsgKey]) ? $result[$tmsgKey] : false;
		$thread = $this->_cacheService->get($threadKey);
		$tmsg = $this->_cacheService->get($tmsgKey);
		if ($thread === false){
			$thread = $this->_getThreadNoCache($threadId);
			$this->_cacheService->set($threadKey, $thread);
		}
		if ($tmsg === false){
			$tmsg = $this->_getTmsgNoCache($threadId);
			$this->_cacheService->set($tmsgKey, $tmsg);
		}
		return ($thread && $tmsg) ? array_merge($thread, $tmsg) : array();
	}
	
	/**
	 * 根据板块id获取帖子列表
	 *
	 * @param int $forumId
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	function getThreadListByForumId($forumId, $offset, $limit) {
		if (! $this->checkMemcache ()) {
			return $this->_getThreadListNoCache($forumId, $offset, $limit);
		}
		$key = $this->_getKeyForThreadList($forumId, $offset, $limit);
		$threadListIds = $this->_cacheService->get($key);
		if (!$threadListIds && ($threadList = $this->_getThreadListNoCache($forumId, $offset, $limit))) {
			$this->_cacheService->set($key, array_keys($threadList));
		}
		return $threadList ?  $threadList : $this->getThreadsByThreadIds($threadListIds);
	}	
	
	/**
	 * 清除帖子缓存
	 *
	 * @param array $threadIds 帖子id数组
	 * @return boolean 
	 */
	function clearCacheForThreadByThreadIds($threadIds){
		$threadIds = (array) $threadIds;
		foreach ($threadIds as $tid){
			$this->_cacheService->delete($this->_getKeyForThread($tid));
		}
		return true;
	}
	
	/**
	 * 清除帖子详细信息缓存
	 *
	 * @param array $threadIds 帖子id数组
	 * @return boolean 
	 */
	function clearCacheForTmsgByThreadIds($threadIds){
		$threadIds = (array) $threadIds;
		foreach ($threadIds as $tid){
			$this->_cacheService->delete($this->_getKeyForTmsg($tid));
		}
		return true;
	}	
	
	/**
	 * 清空某一板块的帖子列表
	 *
	 * @param array $forumIds 板块id
	 * @return int
	 */
	function clearCacheForThreadListByForumIds($forumIds){
		$forumIds = (array) $forumIds;
		foreach ($forumIds as $forumId){
			$this->_cacheService->increment($this->_getKeyForForumVersion($forumId));
		}
		return  true;
	}
		
	/**
	 * 获取帖子在memcache缓存的key
	 *
	 * @param int $threadId 帖子id
	 * @return string
	 */
	function _getKeyForThread($threadId) {
		return $this->_prefix . 'tid_' . $threadId;
	}
	
	/**
	 * 获取帖子列表缓存的key
	 *
	 * @param int $forumId 板块id
	 * @param int $offset
	 * @param int $limit
	 * @return string
	 */
	function _getKeyForThreadList($forumId, $offset, $limit){
		return $this->_prefix . 'fid_' . $forumId . '_offset_' . $offset . '_limit_' . $limit . '_ver_' . $this->_getForumVersionId($forumId); 
	}
	
	/**
	 * 获取帖子详细信息的缓存key
	 *
	 * @param int $threadId 帖子id
	 * @return string
	 */
	function _getKeyForTmsg($threadId){
		return $this->_prefix . 'tmsg_tid_' . $threadId;
	}
	
	/**
	 * 获取板块版本的缓存key
	 *
	 * @param int $forumId
	 * @return string
	 */
	function _getKeyForForumVersion($forumId){
		return $this->_prefix . 'forumversion_' . $forumId;
	}
	
	/**
	 * 获取板块的最新版本号
	 *
	 * @param int $forumId 板块id
	 * @return int
	 */
	function _getForumVersionId($forumId){
		$key = $this->_getKeyForForumVersion($forumId);
		$versionId = $this->_cacheService->get($key);
		if (!$versionId){
			$versionId = 1;
			$this->_cacheService->set($key, $versionId, 3600*24);
		}
		return $versionId;
	}
	
	/**
	 * 不通过缓存，直接从数据库获取一条帖子基本信息
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */	
	function _getThreadNoCache($threadId) {
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getThreadByThreadId ( $threadId );
	}
	
	/**
	 * 不通过缓存，直接从数据库获取一条帖子详细信息
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */		
	function _getTmsgNoCache($threadIds){
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getTmsgByThreadId($threadIds);		
	}	
	
	/**
	 * 不通过缓存，直接从数据库获取一组帖子基本信息
	 *
	 * @param int $threadIds 帖子id数组
	 * @return array
	 */		
	function _getThreadsNoCache($threadIds) {
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getThreadsByThreadIds($threadIds);
	}	
	
	/**
	 * 不通过缓存，直接从数据库获取某一板块的帖子基本信息
	 *
	 * @param int $forumId 板块id
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	function _getThreadListNoCache($forumId, $offset, $limit){
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getThreadsByFroumId($forumId, $offset, $limit);		
	}
	
	/**
	 * 不通过缓存，直接从数据库获取一个帖子的详细信息
	 *
	 * @param int $threadId
	 * @return array
	 */
	function _getThreadAndTmsgByThreadIdNoCache($threadId){
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getThreadAndTmsgByThreadId($threadId);			
	}
}
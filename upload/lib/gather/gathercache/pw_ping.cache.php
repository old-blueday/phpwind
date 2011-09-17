<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 用户评分缓存类，包含对如下表的缓存
 *
 */
class GatherCache_PW_Ping_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'ping_';
	
	/**
	 * 获取评分基本信息
	 *
	 * @param int $threadId 帖子id
	 * @param array $ping_logs 积分日志数组
	 * @return array
	 */
	function getPingsByThreadId($threadId,$ping_logs,$pingpage=null){
		$threadId = S::int($threadId);
		if($threadId < 1 || ! $this->checkMemcache()){
			return false;	
		}
		$pinglogKey = $this->_getPinglogKey($threadId);
		$pinglogSourceKey = $this->_getPinglogSourceKey($threadId);
		$result = $this->_cacheService->get($pinglogKey);
		if ($result === false || $this->_cacheService->get($pinglogSourceKey) != $ping_logs) {
			$pingService = L::loadClass("ping", 'forum');
			$result = $pingService->getPingLogs($threadId, $ping_logs,$pingpage);
			$this->_cacheService->set($pinglogSourceKey, $ping_logs);
			$this->_cacheService->set($pinglogKey, $result);
		}
		return $result;
		
	}

	function _getPinglogKey($threadId) {
		return 'ping_logs_'.$threadId;
	}
	function _getPinglogSourceKey($threadId) {
		return 'ping_logs_source_'.$threadId;
	}
	
	/**
	 * 清除用户的ping_logs信息
	 */
	function clearPingLogsCache($threadId) {
		$pinglogKey = $this->_getPinglogKey($threadId);
		$this->_cacheService->delete ($pinglogKey);
		return true;
	}
}
?>
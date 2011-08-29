<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherCache_PW_Bbsinfo_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE; 
	var $_prefix = 'bbsinfo_'; 
	
	/**
	 * 从缓存获取一条bbsinfo记录
	 *
	 * @param int $id
	 * @return array
	 */
	function getBbsInfoById($id){
		$id = S::int ( $id );
		if ($id < 1) return false;
		$key = $this->_getBbsInfoKeyById($id);
		if (! ($bbsInfo = $this->_cacheService->get($key))){
			$bbsInfo = $this->_getBbsInfoByIdNoCache($id);
			$bbsInfo && $this->_cacheService->set($key, $bbsInfo);
		}
		return $bbsInfo;
	}
	
	/**
	 * 不通过缓存，直接从bbsinfo获取一条记录
	 *
	 * @param int $id
	 * @return array
	 */
	function _getBbsInfoByIdNoCache($id){
		$bbsInfoDb = L::loadDB ( 'bbsInfo', 'forum' );
		return $bbsInfoDb->get( $id );		
	}
	
	/**
	 * 清除一条bbsinfo缓存
	 *
	 * @param int $id
	 */
	function clearBbsInfoCacheById($id){
		$this->_cacheService->delete($this->_getBbsInfoKeyById($id));
	}
	
	/**
	 * 批量清除缓存
	 *
	 * @param array $ids
	 */
	function clearBbsInfoCacheByIds($ids){
		$ids = (array) $ids;
		foreach ($ids as $id){
			$this->_cacheService->delete($this->_getBbsInfoKeyById($id));
		}
	}
	
	/**
	 * 获取bbsinfo在缓存中的key
	 *
	 * @param int $id
	 * @return array
	 */
	function _getBbsInfoKeyById($id){
		return $this->_prefix . 'id_' . $id;
	}
}
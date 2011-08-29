<?php

! defined ( 'P_W' ) && exit ( 'Forbidden' );

class GatherCache_PW_Weibo_content_Cache extends GatherCache_Base_Cache {
	
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'weibocontent_';
	
	/**
	 * 根据mid获取缓存的新鲜事数据
	 * Enter description here ...
	 */
	function getWeibosByMids($mids){
		is_numeric($mids) && $mids = array(intval($mids));
		if (! S::isArray ( $mids )) {
			return false;
		}
		$mids = array_unique ( $mids );
		$result = $_tmpResult = $keys = $_tmpMids = array ();
		foreach ( $mids as $mid ) {
			$keys[$this->_getWeibocontentDataKey ($mid)] = $mid;
		}
		
		if (($weiboContents = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$mid){
				$_key = $_unique . $key;
				if (isset($weiboContents[$_key]) && is_array($weiboContents[$_key])){
					$_tmpMids [] = $mid;
					$result[$mid] = $weiboContents[$_key];
				}
			}
		}
		$mids = array_diff ( $mids, $_tmpMids );
		if ($mids) {
			$_tmpResult = $this->_getWeiboContentsDataByMidsNoCache ($mids);
			foreach ($mids as $mid){
				$this->_cacheService->set ( $this->_getWeibocontentDataKey($mid), isset($_tmpResult[$mid]) ? $_tmpResult[$mid] : array() );
			}
		}
		return (array)$result + (array)$_tmpResult;
	}
	
	/**
	 * 不通过缓存直接从数据库获取weibo content信息
	 *
	 * @param array $mids
	 * @return array
	 */
	function _getWeiboContentsDataByMidsNoCache($mids) {
		if (! S::isArray ( $mids )) return false;
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getWeibosByMid($mids);
	}
	
	function clearCacheForWeiboContentsByMids($mids) {
		$mids = ( array ) $mids;
		foreach ( $mids as $mid ) {
			$this->_cacheService->delete ( $this->_getWeibocontentDataKey($mid));
		}
		return true;
	}
	/**
	 * 获取weibo_content Data信息的缓存key
	 *
	 * @param int $mid 用户id
	 * @return string
	 */
	function _getWeibocontentDataKey($mid) {
		return $this->_prefix . 'weibocontent_mid_' . $mid;
	}
}
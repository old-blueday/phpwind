<?php
!function_exists('readover') && exit('Forbidden');

/**
 * bbsinfo操作类
 *
 */
class PW_BbsInfoService {
	function getBbsInfoById($id){
		$id = S::int ( $id );
		if ($id < 1) return false;
		if (perf::checkMemcache()) {
			$_cacheService = Perf::gatherCache('pw_bbsinfo');
			return $_cacheService->getBbsInfoById($id);
		}
		$bbsInfoDb = $this->_getBbsInfoDB();
		return $bbsInfoDb->get($id);	
	}	
	
	/**
	 * @return PW_BbsInfoDB
	 */
	function _getBbsInfoDB() {
		return L::loadDB('bbsinfo', 'forum');
	}	
}
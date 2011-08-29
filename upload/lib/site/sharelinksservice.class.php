<?php
!defined('P_W') && exit('Forbidden');
/**
 * 友情链接服务层
 * @package  PW_SharelinksService
 * @author panjl @2010-11-5
 */
class PW_SharelinksService {

	/**
	 * 加载dao
	 * 
	 * @return PW_SharelinkstypeDB
	 */
	function _getLinksDB() {
		return L::loadDB('sharelinks', 'site');
	}

	/**
	 * 按照分类、是否有logo查找链接信息
	 * 
	 * @param int $num 条数
	 * @param bool $haveLogo=false 是否有logo
	 * @param int $sids 链接ID，如array(1,2,3)
	 * @return array 友情链接信息
	 */
	function getData($num,$stid = '',$haveLogo = false) {
		$num = (int) $num;
		$stid && $sids = '';
		if ($stid) { 
			$stid = (int) $stid;
			$relationService = L::loadClass('SharelinksRelationService', 'site');
			$stid && $sids = $relationService->findSidByStid($stid);
		}
		if($stid && !$sids) return array();
		$linksDb = $this->_getLinksDB();
		return $linksDb->getData($num,$sids,$haveLogo);
	}

}

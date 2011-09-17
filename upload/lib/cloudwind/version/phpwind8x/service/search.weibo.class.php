<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Weibo extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	
	function getWeibosByPage($page = 1) {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->getWeibosByPage ( $page, $this->_perpage );
	}
	
	function getWerbosByIds($ids) {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->getsweibosIds ( $ids );
	}
	
	function getIdsByRange($minId, $maxId) {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxWeiboId() {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->maxWeiboId();
	}
	
	function countWeibosNum() {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->countWeibosNum();
	}
	
	function createForAdd($weibo, $command = YUN_COMMAND_ADD) {
		if (! $weibo)
			return false;
		if (! isset ( $weibo ['content'] ) || ! $weibo ['content']) {
			return false;
		}
		$out = '';
		$data = array ();
		$data ['mid'] = intval ( $weibo ['mid'] );
		$data ['uid'] = intval ( $weibo ['uid'] );
		$data ['replies'] = intval ( $weibo ['replies'] );
		$data ['postdate'] = intval ( $weibo ['postdate'] );
		$data ['contenttype'] = intval ( $weibo ['contenttype'] );
		$data ['content'] = $this->_toolsService->_filterString ( $weibo ['content'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getWeiboFormat ( $data, $command );
	}
	
	function createForDelete($mid) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'mid', $mid );
	}
	
	function _getWeibosDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchWeiboDao();
		}
		return $dao;
	}
}
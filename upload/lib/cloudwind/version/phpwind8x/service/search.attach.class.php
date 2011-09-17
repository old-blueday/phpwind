<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Attach extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	
	function getAttachsByPage($page) {
		$attachdao = $this->_getAttachsDao ();
		return $attachdao->getAttachsByPage ( $page, $this->_perpage );
	}
	
	function getAttachsByAids($aids) {
		$attachdao = $this->_getAttachsDao ();
		return $attachdao->getsAttachsIds ( $aids );
	}
	
	function getIdsByRange($minId, $maxId) {
		$attachdao = $this->_getAttachsDao ();
		return $attachdao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxAttachId() {
		$attachdao = $this->_getAttachsDao ();
		return $attachdao->maxAttachId ();
	}
	
	function countAttachsNum() {
		$attachdao = $this->_getAttachsDao ();
		return $attachdao->countAttachsNum ();
	}
	
	function createForAdd($attach, $command = YUN_COMMAND_ADD) {
		if (! $attach)
			return false;
		$data = array ();
		$data ['tid'] = intval ( $attach ['tid'] );
		$data ['fid'] = intval ( $attach ['fid'] );
		$data ['pid'] = intval ( $attach ['pid'] );
		$data ['did'] = intval ( $attach ['did'] );
		$data ['uid'] = intval ( $attach ['uid'] );
		$data ['mid'] = intval ( $attach ['mid'] );
		$data ['size'] = intval ( $attach ['size'] );
		$data ['hits'] = intval ( $attach ['hits'] );
		$data ['special'] = intval ( $attach ['special'] );
		$data ['postdate'] = intval ( $attach ['uploadtime'] );
		$data ['name'] = $this->_toolsService->_filterString ( $attach ['name'] );
		$data ['descrip'] = $this->_toolsService->_filterString ( $attach ['descrip'] );
		$data ['ctype'] = $this->_toolsService->_filterString ( $attach ['ctype'] );
		$data ['type'] = $this->_toolsService->_filterString ( $attach ['type'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getAttachFormat ( $data, $command );
	}
	
	function createForDelete($aid) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'aid', $aid );
	}
	
	function _getAttachsDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchAttachDao();
		}
		return $dao;
	}

}
<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Colony extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	
	function getColonysByPage($page) {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->getColonysByPage ( $page, $this->_perpage );
	}
	
	function getColonysByCids($colonyIds) {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->getsByColonyIds ( $colonyIds );
	}
	
	function getIdsByRange($minId, $maxId) {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->getsByColonyIds ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxColonyId() {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->maxColonyId();
	}
	
	function countColonysNum() {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->countColonysNum();
	}
	
	function createForAdd($array, $command = YUN_COMMAND_ADD) {
		if (! $array)
			return false;
		$data = array ();
		$data ['id'] = $array ['id'];
		$data ['classid'] = $array ['classid'];
		$data ['cname'] = $this->_toolsService->_filterString ( strip_tags ( $array ['cname'] ) );
		$data ['link'] = $this->_getColonyUrl ( $array ['id'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getColonyFormat ( $data, $command );
	}
	
	function createForDelete($id) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'id', $id );
	}
	
	function _getColonyUrl($id) {
		return $this->_bbsUrl . '/apps.php?q=group&cyid=' . $id;
	}
	
	function _getColonysDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchColonyDao();
		}
		return $dao;
	}

}
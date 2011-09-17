<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Forum extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	
	function getForumsByPage($page) {
		$forumDao = $this->_getForumsDao ();
		return $forumDao->getForumsByPage ( $page, $this->_perpage );
	}
	
	function getForumsByFids($forumIds) {
		$forumDao = $this->_getForumsDao ();
		return $forumDao->getsByForumIds ( $forumIds );
	}
	
	function getIdsByRange($minId, $maxId) {
		$forumDao = $this->_getForumsDao ();
		return $forumDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxForumId() {
		$forumDao = $this->_getForumsDao ();
		return $forumDao->maxForumId();
	}
	
	function countForumsNum() {
		$forumDao = $this->_getForumsDao ();
		return $forumDao->countForumsNum();
	}
	
	function createForAdd($array, $command = YUN_COMMAND_ADD) {
		if (! $array)
			return false;
		$data = array ();
		$data ['fid'] = $array ['fid'];
		$data ['name'] = $this->_toolsService->_filterString ( strip_tags ( $array ['name'] ) );
		$data ['link'] = $this->_getForumUrl ( $array ['fid'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getForumFormat ( $data, $command );
	}
	
	function createForDelete($id) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'fid', $id );
	}
	
	function _getForumUrl($fid) {
		return $this->_bbsUrl . '/thread.php?fid=' . $fid;
	}
	
	function _getForumsDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchForumDao();
		}
		return $dao;
	}
}
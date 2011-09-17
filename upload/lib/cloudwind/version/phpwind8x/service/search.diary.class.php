<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Diary extends CloudWind_General_Abstract {
	
	function getDiarysByPage($page) {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->getDiarysByPage ( $page, $this->_perpage );
	}
	
	function getDiarysByDids($dids) {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->getsByDids ( $dids );
	}
	
	function getIdsByRange($minId, $maxId) {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxDiaryId() {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->maxDiaryId();
	}
	
	function countDiarysNum() {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->countDiarysNum();
	}
	
	function createForAdd($array, $command = YUN_COMMAND_ADD) {
		if (! $array)
			return false;
		$data = array ();
		$data ['did'] = $array ['did'];
		$data ['uid'] = $array ['uid'];
		$data ['username'] = $this->_toolsService->_filterString ( $array ['username'] );
		$data ['subject'] = $this->_toolsService->_filterString ( $array ['subject'], 300 );
		$data ['content'] = $this->_toolsService->_filterString ( $array ['content'] );
		$data ['postdate'] = $array ['postdate'];
		$data ['link'] = $this->_getDiaryUrl ( $array ['uid'], $array ['did'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getThreadFormat ( $data, $command );
	}
	
	function createForDelete($id) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'did', $id );
	}
	
	function _getDiaryUrl($uid, $did) {
		return $this->_bbsUrl . '/apps.php?q=diary&uid=' . $uid . '&a=detail&did=' . $did;
	}
	
	function _getDiarysDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchDiaryDao();
		}
		return $dao;
	}
}
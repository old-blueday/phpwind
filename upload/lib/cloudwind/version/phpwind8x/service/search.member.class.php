<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Member extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	
	function getMembersByPage($page) {
		$memberDao = $this->_getMembersDao ();
		return $memberDao->getMembersByPage ( $page, $this->_perpage );
	}
	
	function getMembersByUids($userIds) {
		$memberDao = $this->_getMembersDao ();
		return $memberDao->getsByUserIds ( $userIds );
	}
	
	function getIdsByRange($minId, $maxId) {
		$memberDao = $this->_getMembersDao ();
		return $memberDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function maxMemberId() {
		$memberDao = $this->_getMembersDao ();
		return $memberDao->maxMemberId();
	}
	
	function countMembersNum() {
		$memberDao = $this->_getMembersDao ();
		return $memberDao->countMembersNum();
	}
	
	function createForAdd($array, $command = YUN_COMMAND_ADD) {
		if (! $array)
			return false;
		$data = array ();
		$data ['uid'] = $array ['uid'];
		$data ['username'] = $this->_toolsService->_filterString ( $array ['username'] );
		$data ['link'] = $this->_getMemberUrl ( $array ['uid'] );
		$data ['regdate'] = $array ['regdate'];
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getMemberFormat ( $data, $command );
	}
	
	function createForDelete($id) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'uid', $id );
	}
	
	function _getMemberUrl($uid) {
		return $this->_bbsUrl . '/u.php?uid=' . $uid;
	}
	
	function _getMembersDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchMemberDao();
		}
		return $dao;
	}
}
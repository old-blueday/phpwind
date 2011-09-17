<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Sync extends CloudWind_Core_Service {
	
	function dispatch() {
		if (! CloudWind_getConfig ( 'yunsearch_search' ) && ! CloudWind_getConfig ( 'yundefend_shield' )) {
			exit ( '1' );
		}
		$this->syncPost ();
		$this->syncData ();
		$this->syncUser ();
		$this->syncUserDefinedData ();
		exit ( '1' );
	}
	
	function syncData() {
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		if (! CloudWind_getConfig ( 'yunsearch_search' ) || $yunModel ['search_model'] == 100) {
			return true;
		}
		$yunSyncService = $this->_getYunSearchSyncService ();
		if (($data = $yunSyncService->syncData ())) {
			$setting = $this->getPlatformSettings ();
			$this->_sendPost ( array ('data' => $data, 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), 'search' );
		}
		return true;
	}
	
	function syncPost() {
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		if (! CloudWind_getConfig ( 'yundefend_shield' ) || $yunModel ['postdefend_model'] != 100) {
			return true;
		}
		$service = $this->getDefendGeneralService ();
		return $service->syncDefend ();
	}
	
	function syncUser() {
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		if (! CloudWind_getConfig ( 'yundefend_shield' ) || $yunModel ['userdefend_model'] != 100) {
			return true;
		}
		$setting = $this->getPlatformSettings ();
		$service = $this->getDefendSyncService ();
		if (! ($data = $service->getUserDefends ())) {
			return true;
		}
		$data = $this->_sendPost ( array ('data' => $data, 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), 'batch' );
		return true;
	}
	
	function syncUserDefinedData() {
		list ( $typename ) = $this->getRequest ( array ('typename' ) );
		if (! $typename) {
			return false;
		}
		$userDefinedService = $this->getYunUserDefinedService ();
		return $userDefinedService->postUserDefinedData ( $typename );
	}
	
	function getYunUserDefinedService() {
		require_once CLOUDWIND . '/client/search/search.userdefined.class.php';
		return new CloudWind_Search_UserDefined ();
	}
	
	function getUserDefendDao() {
		$searchDaoFactory = $this->getDaoFactory ();
		return $searchDaoFactory->getSearchUserDefendDao ();
	}
	
	function _sendPost($data, $action, $timeout = 5) {
		return $this->sendPost ( "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=" . $action, $data, $timeout );
	}
	
	function _getYunSearchSyncService() {
		return $this->getSearchSyncService();
	}
	
	function getSearchSyncService() {
		static $searchSyncService = null;
		if (!$searchSyncService) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$searchSyncService = $factory->getSearchSyncService ();
		}
		return $searchSyncService;
	}
	
	function getDefendSyncService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$service = $factory->getDefendSyncService ();
		}
		return $service;
	}
	
	function getDefendGeneralService() {
		static $service = null;
		if (! $service) {
			$factory = $this->getDefendFactory ();
			$service = $factory->getDefendGeneralService ();
		}
		return $service;
	}

}
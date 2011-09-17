<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Search_Route extends CloudWind_Core_Service {
	function dispatch() {
		if (! $this->_verify ()) {
			return exit ( 'what do you want to do,not authority' );
		}
		list ( $doing ) = $this->getRequest ( array ('doing' ) );
		if (! $doing || ! in_array ( $doing, $this->getDoings () )) {
			return exit ( 'forbidden' );
		}
		$doing = $doing . 'Router';
		if (! method_exists ( $this, $doing )) {
			return exit ( 'forbidden' );
		}
		return $this->$doing ();
	}
	
	function addRouter() {
		list ( $page, $type, $starttime, $endtime ) = $this->getRequest ( array ('page', 'type', 'starttime', 'endtime' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->alterIndex ( $type, $page, $starttime, $endtime );
	}
	
	function addlistRouter() {
		list ( $type, $hashid, $starttime, $endtime ) = $this->getRequest ( array ('type', 'hashid', 'starttime', 'endtime' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->createAddLists ( $type, $starttime, $endtime, $hashid );
	}
	
	function deleteRouter() {
		list ( $type, $starttime, $endtime ) = $this->getRequest ( array ('type', 'starttime', 'endtime' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->markLogs ( $type, $starttime, $endtime );
	}
	
	function fullRouter() {
		list ( $page, $type, $versionid ) = $this->getRequest ( array ('page', 'type', 'versionid' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->createIndex ( $type, $page, $versionid );
	}
	
	function listRouter() {
		list ( $type, $hashid ) = $this->getRequest ( array ('type', 'hashid' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->createLists ( $type, $hashid );
	}
	
	function detectidRouter() {
		list ( $type, $minid, $maxid ) = $this->getRequest ( array ('type', 'minid', 'maxid' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->detectIndex ( $type, $minid, $maxid );
	}
	
	function fullalllistRouter() {
		list ( $hashid ) = $this->getRequest ( array ('hashid' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->createFullAllLists ( $hashid );
	}
	
	function addalllistRouter() {
		list ( $hashid, $starttime, $endtime ) = $this->getRequest ( array ('hashid', 'starttime', 'endtime' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->createAllAddLists ( $starttime, $endtime, $hashid );
	}
	
	function deleteallRouter() {
		list ( $starttime, $endtime ) = $this->getRequest ( array ('starttime', 'endtime' ) );
		$yunIndexService = $this->_getSearchIndexService ();
		return $yunIndexService->markAllLogs ( $starttime, $endtime );
	}
	
	function getDoings() {
		return array ('add', 'addlist', 'delete', 'full', 'list', 'detectid', 'fullalllist', 'addalllist', 'deleteall' );
	}
	
	function _verify() {
		list ( $hashid ) = $this->getRequest ( array ('hashid' ) );
		if (! $hashid) {
			return false;
		}
		$verifyService = $this->getPlatformVerifySettingService ();
		$setting = $verifyService->getVerifySetting ();
		if (! $setting || ! isset ( $setting ['vector'] ) || ! $setting ['cipher']) {
			return false;
		}
		$hashid = html_entity_decode ( $hashid );
		if (! $hashid) {
			return false;
		}
		$aesService = $this->_getCoreAesService ();
		$key = $aesService->encrypt ( $setting ['vector'], $setting ['cipher'], 256 );
		if (! $key) {
			return false;
		}
		$plaintext = $aesService->strcode ( $hashid, $key, 'DECODE' );
		if (! $this->_checkRequestParams ( $plaintext )) {
			return false;
		}
		return true;
	}
	
	function _checkRequestParams($plaintext) {
		if (! $plaintext) {
			return false;
		}
		$prarms = explode ( "&", $plaintext );
		foreach ( $prarms as $param ) {
			list ( $key, $value ) = explode ( "=", $param );
			if (! isset ( $_GET [$key] ) || $_GET [$key] != $value) {
				return false;
			}
		}
		return true;
	}
	
	function _getSearchIndexService() {
		require_once CLOUDWIND . '/client/search/search.index.class.php';
		return new CloudWind_Search_Index ();
	}
	
	function _getCoreAesService() {
		$factory = $this->_getCoreFactory ();
		return $factory->getAesService ();
	}
	
	function _getCoreFactory() {
		static $coreFactory = null;
		if (! $coreFactory) {
			require_once CLOUDWIND . '/client/core/public/core.factory.class.php';
			$coreFactory = new CloudWind_Core_Factory ();
		}
		return $coreFactory;
	}
	
	function _getPlatformFactory() {
		static $platformFactory = null;
		if (! $platformFactory) {
			require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
			$platformFactory = new CloudWind_Platform_Factory ();
		}
		return $platformFactory;
	}

}
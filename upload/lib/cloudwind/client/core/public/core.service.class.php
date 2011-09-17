<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.toolkit.class.php';
class CloudWind_Core_Service {
	
	var $coreFactorys = array ();
	
	function getRequest($array) {
		CLOUDWIND_SECURITY_SERVICE::gp ( $array );
		$tmp = array ();
		foreach ( $array as $key ) {
			$tmp [] = $GLOBALS [$key];
		}
		return $tmp;
	}
	
	function buildQuery($params) {
		return CloudWind_Core_ToolKit::buildQuery ( $params );
	}
	
	function sendPost($host, $data, $timeout = 10) {
		$factory = $this->getCoreFactory ();
		$httpClientService = $factory->getHttpClientService ();
		return $httpClientService->post ( $host, $data, $timeout );
	}
	
	function getYunHost() {
		$settingService = $this->getPlatformSettingService ();
		return $settingService->getYunHost ();
	}
	
	function getYunSearchHost() {
		$settingService = $this->getPlatformSettingService ();
		return $settingService->getSearchHost ();
	}
	
	function getYunDunHost() {
		$settingService = $this->getPlatformSettingService ();
		return $settingService->getDefendHost ();
	}
	
	function getPlatformSettings() {
		$settingService = $this->getPlatformSettingService ();
		return $settingService->getSetting ();
	}
	
	function filterPage($page, $perpage) {
		$page = intval ( $page ) ? intval ( $page ) : 1;
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		return array ($start, $perpage, $page );
	}
	
	function getPlatformSettingService() {
		$factory = $this->getPlatformFactory ();
		return $factory->getSettingService ();
	}
	
	function getPlatformVerifySettingService() {
		$factory = $this->getPlatformFactory ();
		return $factory->getVerifySettingService ();
	}
	
	function getCoreFactory() {
		if (! isset ( $this->coreFactorys ['CoreFactory'] ) || ! $this->coreFactorys ['CoreFactory']) {
			require_once CLOUDWIND . '/client/core/public/core.factory.class.php';
			$this->coreFactorys ['CoreFactory'] = new CloudWind_Core_Factory ();
		}
		return $this->coreFactorys ['CoreFactory'];
	}
	
	function getPlatformFactory() {
		if (! isset ( $this->coreFactorys ['PlatformFactory'] ) || ! $this->coreFactorys ['PlatformFactory']) {
			require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
			$this->coreFactorys ['PlatformFactory'] = new CloudWind_Platform_Factory ();
		}
		return $this->coreFactorys ['PlatformFactory'];
	}
	
	function getDefendFactory() {
		if (! isset ( $this->coreFactorys ['DefendFactory'] ) || ! $this->coreFactorys ['DefendFactory']) {
			require_once CLOUDWIND . '/client/defend/service/defend.factory.class.php';
			$this->coreFactorys ['DefendFactory'] = new CloudWind_Defend_Factory ();
		}
		return $this->coreFactorys ['DefendFactory'];
	}

}
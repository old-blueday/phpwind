<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Search_General {
	
	function alterIndex($type, $conditions) {
		if (! ($generalService = $this->_factory ( $type )) || ! ($searchService = $this->_versionFactory ( $type ))) {
			return array ();
		}
		return $generalService->alterIndex ( $searchService, $conditions );
	}
	
	function createIndex($type, $conditions) {
		if (! ($generalService = $this->_factory ( $type )) || ! ($searchService = $this->_versionFactory ( $type ))) {
			return array ();
		}
		return $generalService->createIndex ( $searchService, $conditions );
	}
	
	function markIndex($type, $conditions) {
		if (! ($generalService = $this->_factory ( $type )) || ! ($searchService = $this->_versionFactory ( $type ))) {
			return array ();
		}
		return $generalService->markIndex ( $searchService, $conditions );
	}
	
	function detectIndex($type, $conditions) {
		if (! ($generalService = $this->_factory ( $type )) || ! ($searchService = $this->_versionFactory ( $type ))) {
			return array ();
		}
		return $generalService->detectIndex ( $searchService, $conditions );
	}
	
	function _versionFactory($type) {
		$factory = $this->_getVersionFactory ();
		if (! $type || ! in_array ( $type, CloudWind_getConfig ( 'search_types' ) )) {
			exit ( 'forbiden for version type' );
		}
		$method = 'getSearch' . ucfirst ( $type ) . 'Service';
		if (! method_exists ( $factory, $method )) {
			exit ( 'forbiden for version type' );
		}
		return $factory->$method ();
	}
	
	function _factory($type) {
		$factory = $this->_getGeneralFactory ();
		if (! $type || ! in_array ( $type, CloudWind_getConfig ( 'search_types' ) )) {
			exit ( 'forbiden for version type' );
		}
		$method = 'getGeneral' . ucfirst ( $type ) . 'Service';
		if (! method_exists ( $factory, $method )) {
			exit ( 'forbiden for version type' );
		}
		return $factory->$method ();
	}
	
	function _getGeneralFactory() {
		static $factory = null;
		if (! $factory) {
			require_once CLOUDWIND . '/client/search/general/service/general.factory.class.php';
			$factory = new CloudWind_General_Factory ();
		}
		return $factory;
	}
	
	function _getVersionFactory() {
		static $factory = null;
		if (! $factory) {
			require_once CLOUDWIND . '/client/core/public/core.toolkit.class.php';
			require_once CLOUDWIND_SECURITY_SERVICE::escapePath ( CLOUDWIND . '/version/' . CLOUDWIND_CLIENT_VERSION . '/service/service.factory.class.php' );
			$factory = new CloudWind_Service_Factory ();
		}
		return $factory;
	}
}

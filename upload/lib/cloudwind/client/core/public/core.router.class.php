<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class Core_Router_Service {
	function router() {
		$action = (isset ( $_GET ['action'] )) ? $_GET ['action'] : ((isset ( $_POST ['action'] )) ? $_POST ['action'] : 'search');
		if (! in_array ( $action, array ('search', 'sync', 'verify', 'entry', 'apply' ) )) {
			return false;
		}
		$action = ($action) ? $action . 'Router' : 'searchRouter';
		if (! method_exists ( $this, $action )) {
			return false;
		}
		return $this->$action ();
	}
	
	function searchRouter() {
		CloudWind_ipControl ();
		require_once CLOUDWIND . '/client/search/search.route.class.php';
		$service = new CloudWind_Search_Route ();
		$service->dispatch ();
	}
	
	function entryRouter() {
		require_once CLOUDWIND . '/client/search/search.entry.php';
		$service = new CloudWind_Search_Entry ();
		$service->dispatch ();
	}
	
	function syncRouter() {
		require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
		$factory = new CloudWind_Platform_Factory ();
		$service = $factory->getSyncService ();
		$service->dispatch ();
	}
	
	function applyRouter() {
		CloudWind_ipControl ();
		require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
		$factory = new CloudWind_Platform_Factory ();
		$service = $factory->getApplyService ();
		$result = $service->checkApply ();
		print_r ( $result );
		exit ();
	}
	
	function verifyRouter() {
		CloudWind_ipControl ();
		require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
		$factory = new CloudWind_Platform_Factory ();
		$service = $factory->getWalkerService ();
		$result = $service->router ();
		print_r ( $result );
		exit ();
	}
}
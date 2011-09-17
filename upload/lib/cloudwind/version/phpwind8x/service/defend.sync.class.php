<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Defend_Sync extends CloudWind_General_Abstract {
	
	function getUserDefends() {
		$userdefendDao = $this->getUserDefendDao ();
		$defends = $userdefendDao->getAll ();
		if (! $defends) {
			return false;
		}
		$data = array ();
		foreach ( $defends as $defend ) {
			$data [] = $defend ['data'];
		}
		$this->deleteUserDefends ();
		return $data;
	}
	
	function deleteUserDefends() {
		$userdefendDao = $this->getUserDefendDao ();
		return $userdefendDao->deleteAll ();
	}
	
	function getUserDefendDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory ();
			$dao = $daoFactory->getDefendUserDefendDao ();
		}
		return $dao;
	}
}
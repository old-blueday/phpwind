<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Defend_Factory {
	var $_service = array ();
	function getDefendGeneralService() {
		if (! $this->_service ['DefendGeneralService']) {
			require_once CLOUDWIND . '/client/defend/service/defend.general.class.php';
			$this->_service ['DefendGeneralService'] = new CloudWind_Defend_General ();
		}
		return $this->_service ['DefendGeneralService'];
	}
	
}
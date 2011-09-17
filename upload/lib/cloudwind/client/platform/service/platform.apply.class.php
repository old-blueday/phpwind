<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Apply extends CloudWind_Core_Service {
	
	function apply($siteurl, $sitename, $bossname, $bossphone, $marksite) {
		if (! $siteurl || ! $sitename || ! $marksite) {
			return false;
		}
		return $this->_sendPost ( array ('siteurl' => $siteurl, 'sitename' => $sitename, 'charset' => CloudWind_getConfig ( 'g_charset' ), 'bossname' => $bossname, 'bossphone' => $bossphone, 'marksite' => $marksite ) );
	}
	
	function checkApply() {
		list ( $marksite, $step ) = $this->getRequest ( array ('marksite', 'step' ) );
		if (! $marksite || ! $step) {
			return false;
		}
		$factory = $this->getPlatformFactory ();
		$checkService = $factory->getCheckServerService ();
		return $checkService->identifySite ( $marksite );
	}
	
	function _sendPost($data) {
		return $this->sendPost ( "http://" . trim ( $this->getYunHost (), "/" ) . "/index.php?c=apply&a=apply", $data, 5 );
	}
}
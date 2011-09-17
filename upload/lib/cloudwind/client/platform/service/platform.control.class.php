<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Control extends CloudWind_Core_Service {
	function ipControl() {
		$setting = $this->getPlatformSettings ();
		if ($setting ['ipcontrol'] == 100) {
			return true;
		}
		if (($this->spiderControl ()) || ! (CloudWind_getConfig ( 'g_onlineip' ))) {
			return false;
		}
		list ( $ip1, $ip2, $ip3 ) = explode ( ".", CloudWind_getConfig ( 'g_onlineip' ) );
		if (! in_array ( $ip1 . "." . $ip2 . "." . $ip3 . ".x", $this->getIpLists ( $setting ['iplist'] ) )) {
			return false;
		}
		return true;
	}
	
	function getIpLists($iplists) {
		$iplists = ($iplists) ? explode ( "|", $iplists ) : CloudWind_getConfig ( 'g_whiteips' );
		return array_merge ( array ('110.75.164.x', '110.75.168.x', '110.75.171.x', '110.75.172.x', '110.75.173.x', '110.75.174.x', '110.75.175.x', '110.75.176.x', '110.75.167.x' ), $iplists );
	}
	
	function spiderControl() {
		$user_agent = strtolower ( $_SERVER ['HTTP_USER_AGENT'] );
		$allow_spiders = array ('Baiduspider', 'Googlebot' );
		foreach ( $allow_spiders as $spider ) {
			$spider = strtolower ( $spider );
			if (strpos ( $user_agent, $spider ) !== false) {
				return true;
			}
		}
		return false;
	}
}
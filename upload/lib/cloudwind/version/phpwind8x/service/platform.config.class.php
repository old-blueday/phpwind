<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Config extends CloudWind_Core_Service {
	
	function setConfig($key, $value) {
		if (! $key) {
			return true;
		}
		require_once (R_P . 'admin/cache.php');
		setConfig ( 'db_' . $key, $value );
		updatecache_c ();
	}
}
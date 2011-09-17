<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Search_UserDefined {
	
	function postUserDefinedData($typeName) {
		CloudWind_ipControl ();
		if (! $typeName) {
			return false;
		}
		$filePath = CLOUDWIND_VERSION_DIR . '/userdefined/' . $typeName . '.userdefined.php';
		if (! is_file ( $filePath )) {
			return false;
		}
		require_once CLOUDWIND . '/client/core/public/core.toolkit.class.php';
		require_once CLOUDWIND . '/client/core/public/core.userdefinedbase.class.php';
		require_once CLOUDWIND_SECURITY_SERVICE::escapePath ( $filePath );
		$className = 'CloudWind_' . ucfirst ( $typeName ) . '_UserDefined';
		if (! class_exists ( $className )) {
			return false;
		}
		$service = new $className ();
		if (! method_exists ( $service, 'sync' )) {
			return false;
		}
		return $service->sync ();
	}

}
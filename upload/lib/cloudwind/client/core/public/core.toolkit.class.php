<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Core_ToolKit {
	function buildQuery($params) {
		if (! $params || ! is_array ( $params )) {
			return '';
		}
		$query = '';
		foreach ( $params as $key => $value ) {
			$query .= "$key=" . urlencode ( trim ( (is_array ( $value ) ? CloudWind_Core_ToolKit::arrayToString ( $value ) : $value) ) ) . '&';
		}
		$query .= '&cloudwindcode=' . CloudWind_Core_ToolKit::getCloudWindCode ();
		return $query;
	}
	
	function getCloudWindCode() {
		return md5 ( CloudWind_getConfig ( 'g_bbsurl' ) . "\t" . CloudWind_getConfig ( 'g_charset' ) . "\t" . CloudWind_getConfig ( 'g_windversion' ) . "\t" . CLOUDWIND_VERSION );
	}
	
	function arrayToString($array) {
		return base64_encode ( serialize ( $array ) );
	}
	
	function stringToArray($array) {
		return unserialize ( base64_decode ( $array ) );
	}
}
<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Core_UserDefinedBase extends CloudWind_Core_Service {
	function _sendSearchPost($data, $timeout = 5) {
		$setting = $this->getPlatformSettings ();
		return $this->sendPost ( "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=userdefined", array ('data' => $data, 'charset' => CloudWind_getConfig('g_charset'), 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), $timeout );
	}
	
	function output($total, $perpage, $data) {
		if ($total < 1 || ! $data) {
			return false;
		}
		$result = $this->_sendSearchPost ( $data );
		print_r ( 'total=' . $total . '&perpage=' . $perpage . '&result=' . $result );
		exit ();
	}
	
	function getDB($dbhost, $dbuser, $dbpass, $dbname, $charset = 'gbk') {
		$conn = mysql_connect ( $dbhost, $dbuser, $dbpass, true );
		(! $conn) ? print_r ( mysql_error () ) : mysql_select_db ( $dbname );
		mysql_query ( "SET NAMES '{$charset}'", $conn );
		return $conn;
	}
}
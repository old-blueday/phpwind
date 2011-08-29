<?php
/**
 * 自定义搜索数据基类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_UserDefinedBase extends PW_YunBase {
	function _sendSearchPost($data, $timeout = 5) {
		$setting = $this->getYunSetting ();
		return $this->sendPost ( "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=userdefined", array ('data' => $data, 'charset' => $GLOBALS ['db_charset'], 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), $timeout );
	}
	function filterPage($page, $perpage) {
		$page = intval ( $page ) ? intval ( $page ) : 1;
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		return array ($start, $perpage, $page );
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
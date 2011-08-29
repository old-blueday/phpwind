<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索统一入口文件
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-04-12
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
if ($GLOBALS ['db_yunsearch_isopen'] && $GLOBALS ['db_yunsearch_domain']) {
	InitGP ( array ("keyword", "type", "fid", "username" ) );
	if ($keyword) {
		$query = '&charset=' . $GLOBALS ['db_charset'];
		$fid && $query .= "&fid=" . intval ( $fid );
		$type && $query .= "&type=" . trim ( $type );
		$username && $query .= "&username=" . urlencode ( trim ( $username ) );
		header ( "Location:http://" . $GLOBALS ['db_yunsearch_domain'] . "/index.php?k=" . urlencode ( $keyword ) . $query );
		exit ();
	}
}
?>
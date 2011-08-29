<?php
define ( 'SCR', 'yi' );
/**
 * 云计算入口文件(开启社区云时代)
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @link http://www.phpwind.net/thread-htm-fid-131.html
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
define ( "COL", 1 );
require_once ('global.php');
@header ( "Content-Type:text/html; charset=utf8" );
InitGP ( array ('action' ) );
if ($action == 'verify') {
	require_once R_P . 'lib/cloudwind/yunverifyroute.class.php';
	$yunRoute = new PW_YunVerifyRoute ();
	$yunRoute->verifyDispatch ();
} else if ($action == 'sync') {
	require_once R_P . 'lib/cloudwind/yunsyncroute.class.php';
	$yunRoute = new PW_SyncRoute ();
	$yunRoute->dispatch ();
} else {
	require_once R_P . 'lib/cloudwind/yunroute.class.php';
	$yunRoute = new PW_YunRoute ();
	$yunRoute->dispatch ();
}
<?php
/**
 * 云盾后台管理
 * @author L.IuHu.I@2011 developer.liuhui@gmail.com
 */
require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
$factory = new PW_YunExtendFactory ();
$_service = $factory->getYunCheckServerService ();
if ($_service->checkCloudWind () < 9) {
	ObHeader ( $admin_file . '?adminjob=yunbasic' );
}
S::gp ( array ('action' ) );
if (empty ( $action )) {
	if ($_POST ['step'] == 2) {
		S::gp ( array ('db_yundefend_shield', 'db_yundefend_shieldpost', 'db_yundefend_shielduser' ), 'P', 2 );
		setConfig ( 'db_yundefend_shield', $db_yundefend_shield );
		setConfig ( 'db_yundefend_shieldpost', $db_yundefend_shieldpost );
		setConfig ( 'db_yundefend_shielduser', $db_yundefend_shielduser );
		updatecache_c ();
		Showmsg ( '云盾设置成功 ' );
	}
	ifcheck ( $db_yundefend_shield, 'yundefend_shield' );
	ifcheck ( $db_yundefend_shieldpost, 'yundefend_shieldpost' );
	${'yundefend_shielduser_' . intval ( $db_yundefend_shielduser )} = 'checked="checked"';
	$dundescribe = $_service->getDunDescribe ();
	$current ['config'] = 'current';
} elseif ($action == 'verify') {
	S::gp ( array ('page' ) );
	$page = ($page > 1) ? intval ( $page ) : 1;
	require_once R_P . 'lib/cloudwind/defend/yunpostverify.class.php';
	$postVerifyService = new PW_YunPostVerify ();
	if ($_POST ['step'] == 2) {
		S::gp ( array ('ids' ) );
		foreach ( $ids as $key => $operate ) {
			list ( $tid, $pid ) = explode ( "_", $key );
			$postVerifyService->verify ( $operate, $tid, $pid );
		}
		Showmsg ( '云盾审核成功 ', $basename . "&action=verify&page=" . $page );
	}
	$total = $postVerifyService->countPostVerify ();
	$lists = ($total) ? $postVerifyService->getPostVerify ( $page ) : array ();
	$pages = numofpage ( $total, $page, ceil ( $total / 20 ), $basename . "&action=verify&" );
	$current ['verify'] = 'current';
}
include PrintEot ( 'yundefend' );


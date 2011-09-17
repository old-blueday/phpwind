<?php
/**
 * 云搜索后台管理
 * @author L.IuHu.I@2011 developer.liuhui@gmail.com
 */
require_once(R_P.'lib/cloudwind/cloudwind.class.php');
$_service = CloudWind::getPlatformCheckServerService ();
if ($_service->checkCloudWind () < 9) {
	ObHeader ( $admin_file . '?adminjob=yunbasic' );
}
if (! $_service->getSiteScale ()) {
	Showmsg ( '亲，您的站点现在没有搜索负载压力，过段时间再开启云搜索吧亲～' );
}
if (! $db_yunsearch_search) {
	if ($_POST ['step'] == 2) {
		CLOUDWIND_SECURITY_SERVICE::gp ( array ('db_yunsearch_search' ), 'P', 2 );
		setConfig ( 'db_yunsearch_search', $db_yunsearch_search );
		updatecache_c ();
		Showmsg ( '云搜索设置成功 ' );
	}
	ifcheck ( $db_yunsearch_search, 'yunsearch_search' );
}
$yunManageUrl = $_service->getYunSearchManageUrl ();
include PrintEot ( 'yunsearch' );
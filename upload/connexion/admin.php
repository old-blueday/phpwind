<?php
/**
*
*  Copyright (c) 2003-09  phpwind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of phpwind.com.
*
*/
error_reporting(E_ERROR | E_PARSE);
set_magic_quotes_runtime(0);

define('C_P',strpos(__FILE__,DIRECTORY_SEPARATOR)!==FALSE ? substr(__FILE__,0,strrpos(__FILE__,DIRECTORY_SEPARATOR)).'/' : './');
define('R_P',realpath(C_P . '../') . '/');
define('D_P',R_P);

// 检查登录
define('P_W','admincp');
$file_content = file_get_contents(D_P.'data/bbscache/config.php');
preg_match('/db_cookiepre\=\'(.*)\'/', $file_content, $match);
$db_cookiepre = $match['1'];
preg_match('/db_sitehash\=\'(.*)\'/', $file_content, $match);
$db_sitehash = $match['1'];
unset($file_content);
require_once(R_P.'require/common.php');
$_AdminUser = GetCookie('AdminUser');
if (!$_AdminUser) {
	$message = '登录超时';
	include R_P.'connexion/template/message.htm';
	exit;
}
// 检查登录

require_once(R_P.'admin/admincp.php');
//$imgpath = $db_bbsurl . '/images/';
$basename = $admin_file.'?adminjob=platformweiboapp&action='.$action;
if ($adminjob == 'msg') {
	showNotice($message);
}

if ($adminjob != 'platformweiboapp' || !adminRight($adminjob,$admintype)) {
	showNotice('非法操作');
}
if ($action == 'customweibotemplate') {
	require_once(R_P.'connexion/customweibotemplate.php');
} else if ($adminjob == 'admin') {
} else {
	//adminmsg('undefine_action');
	showNotice('非法操作');
}

function showNotice($notice) {
	//global $db_bbsurl;
	//$imgpath = $db_bbsurl . '/images/';
	$message = $notice;
	include R_P.'connexion/template/message.htm';
	exit;
}

function PrintTemplate($template, $EXT = 'htm') {
	return R_P.'connexion/template/'.$template.".$EXT";
}


function adminRight($adminjob,$admintype){
	$temp = $admintype ? $admintype : $adminjob;
	return adminRightCheck($temp);
}
function getdirname($path=null){
	if (!empty($path)) {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		}
	}
	return './';
}
?>
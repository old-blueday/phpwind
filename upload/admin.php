<?php
/**
*
*  Copyright (c) 2003-09  phpwind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of phpwind.com.
*
*/
error_reporting(E_ERROR | E_PARSE);
function_exists('set_magic_quotes_runtime') && set_magic_quotes_runtime(0);

define('R_P',strpos(__FILE__,DIRECTORY_SEPARATOR)!==FALSE ? substr(__FILE__,0,strrpos(__FILE__,DIRECTORY_SEPARATOR)).'/' : './');
define('D_P',R_P);
require_once(R_P.'admin/admincp.php');
$basename = $admin_file.'?adminjob='.$adminjob;
if (!$adminjob) {
	require_once(R_P.'admin/index.php');
} elseif($adminjob == 'notice'){//计划任务进程
	require_once(R_P.'admin/notice.php');
} elseif ($adminjob == 'admin') {//提示信息、桌面快捷显示
	require_once(R_P.'admin/admininfo.php');
} elseif ($adminjob == 'adminrecord' && $db_adminrecord == '1') {//操作记录
	require_once(R_P.'admin/adminrecord.php');
} elseif ($adminjob == 'search') {//后台搜索
	require_once(R_P.'admin/search.php');
} elseif ($adminjob == 'hack' && $rightset['hackcenter'] == 1) {//插件管理
	if (!$db_hackdb[$hackset] || !is_dir(R_P.'hack/'.$hackset.'/') || !file_exists(R_P.'hack/'.$hackset.'/admin.php')) {
		adminmsg('hack_error',$admin_file.'?adminjob=hackcenter');
	}
	define('H_P',R_P.'hack/'.$hackset.'/');
	$basename = $admin_file.'?adminjob=hack&hackset='.$hackset;
	require_once S::escapePath(H_P.'admin.php');
} elseif ($adminjob == 'mode' && $admintype && $rightset[$admintype] == 1) {//模式管理
	$m = substr($admintype, 0,strpos($admintype,'_'));
	$adminjob = substr($admintype, strpos($admintype,'_')+1);
	if (!isset($db_modes[$m]) || !is_dir(R_P."mode/$m")) {
		adminmsg('mode_admin_error');
	}
	$db_mode = $m;
	define('M_P',R_P."mode/$m/");
	$pwModeImg = "mode/$m/images";
	$basename = "$admin_file?adminjob=mode&admintype=$admintype";
	if (is_file(M_P.'require/core.php')) {
		include_once(M_P.'require/core.php');
	}
	if (is_file(M_P.'config/admin.php')) {
		include_once(M_P.'config/admin.php');
	}
	/*模式设置是否启用新框架架构*/
	if(defined('FRAMEWORK')){
		if(!is_file(R_P."mode/$m/index.php")){
			adminmsg('mode_admin_error');
		}
		define('FRAMEWORK_ADMIN',1);
		require_once S::escapePath(M_P."index.php");
	}else{
		if(!is_file(R_P."mode/$m/admin/$adminjob.php")){
			adminmsg('mode_admin_error');
		}
		require_once S::escapePath(M_P.'admin/'.$adminjob.'.php');
	}
} elseif ($adminjob == 'apps' && $admintype && $rightset[$admintype] == 1){//基础性app管理
	list($adminname,$subname) = explode('_',$admintype);
	$subname = $subname ? "admin/{$subname}.php" : 'admin.php';
	if (!is_dir(R_P."apps/$adminname") || !file_exists(R_P."apps/$adminname/$subname")) {
		adminmsg('app_admin_error');
	}
	define('A_P',R_P."apps/$adminname/");
	$appdir = $adminname;
	$pwAppImg = "mode/$adminname/images";
	$basename = "$admin_file?adminjob=apps&admintype=$admintype";
	require_once S::escapePath(A_P . $subname);
} elseif ($adminjob == 'content' && (($rightset['tpccheck'] && ($type=='tpc' || $type=='post')) || ((int)$rightset['message'] == 1 && $type == 'message'))) {
	require_once(R_P.'admin/content.php');
} elseif (managerRight($adminjob) || adminRight($adminjob,$admintype)) {
	require_once S::escapePath(R_P.'admin/'.$adminjob.'.php');
} else {
	$basename = "javascript:parent.closeAdminTab(window);";
	adminmsg('undefine_action');
}

function managerRight($adminjob) {
	return If_manager && in_array($adminjob,array('rightset','manager','ystats','diyoption','optimize','modepage','sphinx','ajaxhandler'));
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
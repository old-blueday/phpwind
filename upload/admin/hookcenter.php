<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=hookcenter";
$db_hookset = $db_hookset ? $db_hookset : array();
$hooks = $usedHooks = pwHook::getSystemHooks();
$unUsedHooks = array();

if ($fp = opendir(R_P.'hook')) {
	while (($hookdir = readdir($fp))) {
		if (strpos($hookdir,'.')!==false || in_array($hookdir,pwHook::getSystemHooks())) continue;
		if (isset($db_hookset[$hookdir])) {
			$usedHooks[] = $hookdir;
		} else {
			$unUsedHooks[] = $hookdir;
		}
		$hooks[] = $hookdir;
	}
	closedir($fp);
}

if (!$action) {
	$hookmode = array("","");
	($db_hookmode == 0) ? $hookmode[0] = "checked" : $hookmode[1] = "checked";
	include PrintEot('hookcenter');exit;
} elseif ($action=='install') {
	S::gp(array('hook'),'G');
	if (!$hook || !in_array($hook,$hooks)) adminmsg('扩展不存在');
	if (isset($db_hookset[$hook])) adminmsg('该扩展已安装');
	$db_hookset[$hook] = 1;
	setConfig('db_hookset', $db_hookset);
	updatecache_c();
	updateHookCache($hook);
	adminmsg('operate_success');
} elseif ($action=='uninstall') {
	S::gp(array('hook'),'G');
	if (!$hook || !in_array($hook,$hooks)) adminmsg('扩展不存在');
	if (!isset($db_hookset[$hook])) adminmsg('该扩展未安装');
	unset($db_hookset[$hook]);
	setConfig('db_hookset', $db_hookset);
	updatecache_c();
	
	adminmsg('operate_success');
} elseif ($action=='updatecache') {
	S::gp(array('hook'),'G');
	if (!$hook || !in_array($hook,$hooks)) adminmsg('扩展不存在');
	if (!pwHook::checkHook($hook)) adminmsg('该扩展未安装');
	updateHookCache($hook);
	adminmsg('operate_success');
} elseif ($action=='setmode') {
	S::gp(array('hookmode'),'P');
	$hookmode = $hookmode ? 1 : 0;
	setConfig('db_hookmode', $hookmode);
	updatecache_c();
	adminmsg('operate_success');
}

function updateHookCache($hook) {
	L::loadClass('hook','hook',false);
	$pwHook = new PW_Hook($hook);
	$pwHook->packHookFiles();
}
<?php
define ( 'SCR', 'mode' );
require_once ('global.php');
require_once(R_P.'require/functions.php');
$m = S::getGP('m');
S::gp ( array ('q') );

selectMode($m,$q);

if ($m == 'bbs') ObHeader($_mainUrl);

/*APP 应用跳转*/
if ($m == 'o' && $q) {
	if ($q == 'user') {
		S::gp(array('u'));
		ObHeader ( USER_URL."=$u");
	} elseif ($q == 'app') {
		S::gp ( array ('id' ), 'G', 2 );
		ObHeader ( "apps.php?id=$id" );
	} elseif ($q == 'friend') {
		ObHeader ( "u.php?a=friend" );
	} elseif (!in_array($q ,array('user', 'friend', 'browse','invite','board','myapp','home') )) {
		$QUERY_STRING = substr($pwServer['QUERY_STRING'],4);
		ObHeader ( "apps.php?".$QUERY_STRING);
	}
}

if ($m == 'o') {
	$pwModeImg = "$imgpath/apps";
	$q = 'browse';
}
if (strpos ( $q, '..' ) !== false) {
	Showmsg ( 'undefined_action' );
}

if ($m && $pwServer ['HTTP_HOST'] == $db_modedomain[$m]) {
	$baseUrl = "mode.php";
	$basename = "mode.php?";
} else {
	$baseUrl = "mode.php?m=$m";
	$basename = "mode.php?m=$m&";
}
if ($m == 'cms') {//兼容老版本入口
	//* @include_once pwCache::getPath(S::escapePath(D_P . 'data/bbscache/cms_config.php'));
	pwCache::getData(S::escapePath(D_P . 'data/bbscache/cms_config.php'));
	if (file_exists(M_P . 'require/core.php')) {
		require_once (M_P . 'require/core.php');
	}
	$basename = "index.php?m=$m";
	require_once S::escapePath ( M_P . "index.php" );
	exit;
}
if (file_exists ( M_P . "m_{$q}.php" )) {
	//* @include_once pwCache::getPath(S::escapePath(D_P . 'data/bbscache/' . $db_mode . '_config.php'));
	pwCache::getData(S::escapePath(D_P . 'data/bbscache/' . $db_mode . '_config.php'));
	${$db_mode.'_sitename'} = ${$db_mode.'_sitename'} ? ${$db_mode.'_sitename'} : $db_bbsname;
	 $db_mode == 'cms' && $db_bbsname = ${$db_mode.'_sitename'}; 

	//current user
	$tname = ($q != "user" && isset($winddb['username'])) ? $winddb['username'].' - ' : '';
	isset($o_navinfo['KEY'.$q]) && $webPageTitle = strip_tags($o_navinfo['KEY'.$q]['html']).' - '.$tname.$webPageTitle;
	unset($tname);
	if ($groupid != 3 && $o_share_groups && strpos ( $o_share_groups, ",$groupid," ) === false) {
		$shareGM = 1;
	}
	if (file_exists ( M_P . 'require/core.php' )) {
		require_once (M_P . 'require/core.php');
	}
	require_once S::escapePath ( M_P . "m_{$q}.php" );
} else {
	Showmsg ( 'undefined_action' );
}

?>
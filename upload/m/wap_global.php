<?php
error_reporting(0);
define('WAPWIND', 'v2.3(20110325)');
define('W_P', __FILE__ ? dirname(__FILE__) . '/' : './');
require_once (W_P . '../global.php');

require_once (W_P . '../require/functions.php');
require_once (W_P . '/include/wap_mod.php');
include_once (D_P . '/data/bbscache/forum_cache.php');

@header("content-type:text/html; charset=utf8");
$db_bbsurl = $_mainUrl;
$imgpath = $db_bbsurl .'/'. $imgpath;
$attachpath = $db_bbsurl .'/'. $attachpath;
$wapImages = './images';
$wap_perpage = 10;
if (!$db_wapifopen) {
	wap_msg('wap_closed');
}
if ($db_charset != 'utf8') {
	L::loadClass('Chinese', 'utility/lang', false);
	$chs = new Chinese('UTF8', $db_charset,true);
	foreach ($_POST as $key => $value) {
		$_POST[$key] = addslashes($chs->Convert(stripslashes($value)));
	}
}

$basename = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
$headTitle = $db_bbsname;
$isGM = CkInArray($windid, $manager);
if ($_GET['token']) {
	$token = deWindToken($_GET['token']);
	if ($token) {
		Cookie("winduser", $token);
		//自动获取勋章_start
		require_once(R_P.'require/functions.php');
		doMedalBehavior($winduid,'continue_login');
		//自动获取勋章_end
		wap_msg("欢迎来到$db_bbsname", 'index.php');
	} else {
		wap_msg("链接已失效，请重新登录", 'index.php');
	}
}

$tokenURL = "$db_bbsurl/index.php?token=" . enWindToken(GetCookie('winduser'));
$scrMap = array(
		"realy_all"=>"index.php?a=realy_all",
		"reply"=>"index.php?a=reply",
		"read"=>"index.php?a=read",
		"forum"=>"index.php?a=forum",
		"list"=>"index.php?a=list",
		"bbsinfo"=>"index.php?a=bbsinfo",
		"index"=>"index.php",
		"recommend"=>"index.php?a=recommend",
);
?>
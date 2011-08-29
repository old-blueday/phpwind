<?php
define('SCR','userpay');
require_once('global.php');
require_once(R_P.'require/functions.php');
require_once(R_P.'require/tool.php');
!$windid && Showmsg('not_login');

$USCR = 'set_profile';
//导航
$homenavigation = array();
$navConfigService = L::loadClass('navconfig', 'site');
$homenavigation = $navConfigService->userHomeNavigation(PW_NAV_TYPE_MAIN, 'o');

if (file_exists(R_P . "u/require/userpay/userpay.php")) {
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space =& $newSpace->getInfo();
	//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
	pwCache::getData(D_P . 'data/bbscache/o_config.php');
	require_once S::escapePath(R_P . "u/require/userpay/userpay.php");
} else {
	Showmsg('undefined_action');
}
exit;

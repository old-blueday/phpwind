<?php
define('SCR','u');
require_once('global.php');
require_once(R_P . 'u/require/core.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
$o_sitename = $o_sitename ? $o_sitename :  $db_bbsname;
//导航
$homenavigation = array();
$navConfigService = L::loadClass('navconfig', 'site');
$homenavigation = $navConfigService->userHomeNavigation(PW_NAV_TYPE_MAIN, 'o');

S::gp(array('a', 'uid', 'username', 'contenttype'));
$pwModeImg = "$imgpath/apps";
if ($a && in_array($a, array('set', 'ajax', 'friend', 'myapp', 'info', 'invite', 'board'))) {
	require_once S::escapePath(R_P . 'u/' . $a . '.php');
} elseif ($uid || $username) {
	require_once(R_P . 'u/space.php');
} else {
	require_once(R_P . 'u/home.php');
}
exit;
?>
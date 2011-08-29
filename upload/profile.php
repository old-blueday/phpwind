<?php
define('PRO','1');
define('SCR','profile');
require_once('global.php');

!$winduid && Showmsg('not_login');
S::gp(array('action'));

require_once(R_P . 'require/showimg.php');
//list($faceurl) = showfacedesign($winddb['icon'],1,'s');
//导航
$homenavigation = array();
$navConfigService = L::loadClass('navconfig', 'site');
$homenavigation = $navConfigService->userHomeNavigation(PW_NAV_TYPE_MAIN, 'o');

empty($action) && $action = 'modify';
$pro_tab = $action;
$USCR = 'set_profile';

$db_menuinit .= ",'td_userinfomore' : 'menu_userinfomore'";

if (file_exists(R_P . "u/require/profile/{$action}.php")) {

	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space =& $newSpace->getInfo();
	//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
	pwCache::getData(D_P . 'data/bbscache/o_config.php');
	require_once S::escapePath(R_P . "u/require/profile/{$action}.php");

} else {

	Showmsg('undefined_action');
}
exit;
?>
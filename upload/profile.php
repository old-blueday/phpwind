<?php
define('PRO','1');
define('SCR','profile');
require_once('global.php');

!$winduid && Showmsg('not_login');
InitGP(array('action'));

require_once(R_P . 'require/showimg.php');
//list($faceurl) = showfacedesign($winddb['icon'],1,'s');

empty($action) && $action = 'modify';
$pro_tab = $action;
$USCR = 'set_profile';

$db_menuinit .= ",'td_userinfomore' : 'menu_userinfomore'";

if (file_exists(R_P . "u/require/profile/{$action}.php")) {

	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space =& $newSpace->getInfo();
	include_once(D_P . 'data/bbscache/o_config.php');
	require_once Pcv(R_P . "u/require/profile/{$action}.php");

} else {

	Showmsg('undefined_action');
}
exit;
?>
<?php
define('SCR','u');
require_once('global.php');
require_once(R_P . 'u/require/core.php');
include_once (D_P . 'data/bbscache/o_config.php');
$o_sitename = $o_sitename ? $o_sitename :  $db_bbsname;

InitGP(array('a', 'uid', 'username'));

$pwModeImg = "$imgpath/apps";
if ($a && in_array($a, array('set', 'ajax', 'friend', 'myapp', 'info', 'invite', 'board'))) {
	require_once Pcv(R_P . 'u/' . $a . '.php');
} elseif ($uid || $username) {
	require_once(R_P . 'u/space.php');
} else {
	require_once(R_P . 'u/home.php');
}
exit;
?>
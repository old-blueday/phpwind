<?php
define('SCR','userpay');
require_once('global.php');
require_once(R_P.'require/functions.php');
require_once(R_P.'require/tool.php');
!$windid && Showmsg('not_login');

$USCR = 'set_profile';

if (file_exists(R_P . "u/require/userpay/userpay.php")) {
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space =& $newSpace->getInfo();
	include_once(D_P . 'data/bbscache/o_config.php');
	require_once Pcv(R_P . "u/require/userpay/userpay.php");
} else {
	Showmsg('undefined_action');
}
exit;

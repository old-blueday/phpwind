<?php
define('SCR','jobcenter');
require_once('global.php');
!$winduid && Showmsg('not_login');

if (!$db_job_isopen) {
	Showmsg('抱歉，用户任务系统还没有开启');
}

$USCR = 'set_jobcenter';

if (file_exists(R_P . "u/require/jobcenter/jobcenter.php")) {
	require_once(R_P. 'u/require/core.php');
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space =& $newSpace->getInfo();
	include_once(D_P . 'data/bbscache/o_config.php');
	require_once Pcv(R_P . "u/require/jobcenter/jobcenter.php");
} else {
	Showmsg('undefined_action');
}
exit;
?>
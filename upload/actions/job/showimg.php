<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'verify',
	'pid',
	'aid'
));
if ($verify != md5("showimg{$tid}{$pid}{$fid}{$aid}{$db_hash}")) {
	Showmsg('undefined_action');
}
if (function_exists('file_get_contents')) {
	$rs = $db->get_one('SELECT attachurl FROM pw_attachs WHERE aid=' . S::sqlEscape($aid) . ' AND tid=' . S::sqlEscape($tid) . ' AND fid=' . S::sqlEscape($fid));
	if ($rs) {
		$fgeturl = geturl($rs['attachurl']);
		if ($fgeturl[0]) {
			echo file_get_contents($fgeturl[0]);
			exit();
		}
	}
}
Showmsg('job_attach_error');

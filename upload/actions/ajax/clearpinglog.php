<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'fid',
	'tid',
	'pid'
), null, 2);

$pingdata = $db->get_one("SELECT id FROM pw_pinglog WHERE fid=" . pwEscape($fid) . " AND tid=" . pwEscape($tid) . " AND pid=" . pwEscape($pid) . " AND pinger=" . pwEscape($windid));
$user_has_ping = $pingdata ? true : false;

$pid = $pid ? $pid : "tpc";

require_once PrintEot('ajax');
ajax_footer();

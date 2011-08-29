<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'fid',
	'tid',
	'pid'
), null, 2);

$pingdata = $db->get_one("SELECT id FROM pw_pinglog WHERE fid=" . S::sqlEscape($fid) . " AND tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid) . " AND pinger=" . S::sqlEscape($windid));
$user_has_ping = $pingdata ? true : false;

$pid = $pid ? $pid : "tpc";

require_once PrintEot('ajax');
ajax_footer();

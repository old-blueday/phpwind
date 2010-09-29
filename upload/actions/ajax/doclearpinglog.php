<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'fid',
	'tid',
	'pid'
), null, 2);
$db->update("UPDATE pw_pinglog SET ifhide=1 WHERE fid=" . pwEscape($fid) . " AND tid=" . pwEscape($tid) . " AND pid=" . pwEscape($pid) . " AND pinger=" . pwEscape($windid));
if ($db->affected_rows()) {
	echo "清空评分动态成功!";
	require_once R_P . 'require/pingfunc.php';
	update_markinfo($fid, $tid, $pid);
} else {
	echo "没有需要清空的评分动态";
}
ajax_footer();

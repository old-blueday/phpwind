<?php
!defined('P_W') && exit('Forbidden');

$arr = array();
$query = $db->query("SELECT m.uid,m.username FROM pw_userbinding u1 LEFT JOIN pw_userbinding u2 ON u1.id=u2.id LEFT JOIN pw_members m ON u2.uid=m.uid WHERE u1.uid=" . S::sqlEscape($winduid));
while ($rt = $db->fetch_array($query)) {
	$rt['uid'] && $rt['uid'] != $winduid && $arr[] = $rt;
}
require_once PrintEot('ajax');
ajax_footer();

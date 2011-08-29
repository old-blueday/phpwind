<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('uid'), 'GP', 2);
!$uid && Showmsg('undefined_action');
$i = $j = 0;
$query = $db->query("SELECT t.id,t.name,t.filename,t.descrip,u.nums FROM pw_tools t LEFT JOIN pw_usertool u ON t.id=u.toolid  AND u.uid=" . S::sqlEscape($winduid) . " WHERE state='1' AND type='2' ORDER BY vieworder");
while ($rt = $db->fetch_array($query)) {
	if ($rt['filename'] == 'luck' && $winduid != $uid) continue;
	$rt['nums'] = (int) $rt['nums'];
	$tooldb[$i][$j] = $rt;
	$j++;
	if ($j > 1) {
		$i++;
		$j = 0;
	}
}
require_once PrintEot('ajax');
ajax_footer();

<?php
!function_exists('readover') && exit('Forbidden');

$activity = $db->get_one("SELECT * FROM pw_activity WHERE tid=".S::sqlEscape($tid));

if ($activity) {
	$special = 'read_activity';
	foreach ($activity as $key=>$val) {
		$key == 'endtime' && $endtime = $val;
		if (in_array($key,array('starttime','endtime','deadline'))) {
			$val = get_date($val,'Y-m-d H:i');
		}
		${'active_'.$key}=$val;
	}
	unset($activity);
	$query = $db->query("SELECT a.state,a.winduid,m.username FROM pw_actmember a LEFT JOIN pw_members m ON a.winduid=m.uid WHERE a.actid=".S::sqlEscape($tid));
	$act_total = $act_y = 0;
	$actuserdb = array();
	while ($rt = $db->fetch_array($query)) {
		$act_total++;
		$rt['state'] == 1 && $act_y ++;
		$rt['state'] == 1 && $actuserdb[] = array('winduid'=>$rt['winduid'],'username'=>$rt['username']);
	}
	$actmen = $db->get_one("SELECT state FROM pw_actmember WHERE winduid=".S::sqlEscape($winduid)."AND actid=".S::sqlEscape($tid));
}
?>
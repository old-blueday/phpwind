<?php
!function_exists('readover') && exit('Forbidden');

PwNewDB();
$thisday	= get_date($GLOBALS['tdtime'],'Y-n-j');
$thismonth	= get_date($GLOBALS['tdtime'],'Y-n');
$rt = $GLOBALS['db']->get_one("SELECT day FROM pw_ipstates WHERE day=".pwEscape($thisday));
if ($rt) {
	$GLOBALS['db']->update("UPDATE pw_ipstates SET nums=nums+1 WHERE day=".pwEscape($thisday));
} else {
	$GLOBALS['db']->update("INSERT INTO pw_ipstates SET ".pwSqlSingle(array('day'=>$thisday,'nums'=>1,'month'=>$thismonth)));
}
Cookie('ipstate',$GLOBALS['timestamp']);
?>
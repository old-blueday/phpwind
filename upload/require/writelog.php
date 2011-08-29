<?php
!function_exists('readover') && exit('Forbidden');

function writelog($log){
	global $db,$db_moneyname,$db_rvrcname,$db_bbsurl;
	$log['username1'] = S::escapeChar($log['username1']);
	$log['username2'] = S::escapeChar($log['username2']);
	$log['field1']    = S::escapeChar($log['field1']);
	$log['field2']    = S::escapeChar($log['field2']);
	$log['field3']    = S::escapeChar($log['field3']);
	if (!$log['subject']) {
		$log['subject'] = substrs($db_bbsurl.'/read.php?tid='.$log['tid'],28);
	}
	$log['descrip']	  = S::escapeChar(getLangInfo('log',$log['descrip'],$log));
	$db->update("INSERT INTO pw_adminlog"
		. " SET ".S::sqlSingle(array(
			'type'		=> $log['type'],
			'username1'	=> $log['username1'],
			'username2'	=> $log['username2'],
			'field1'	=> $log['field1'],
			'field2'	=> $log['field2'],
			'field3'	=> $log['field3'],
			'descrip'	=> $log['descrip'],
			'timestamp'	=> $log['timestamp'],
			'ip'		=> $log['ip']
	),false));
}
function writeforumlog($log){
	$log['username1'] = S::escapeChar($log['username1']);
	$log['username2'] = S::escapeChar($log['username2']);
	$log['field1']    = S::escapeChar($log['field1']);
	$log['field2']    = S::escapeChar($log['field2']);
	$log['field3']    = S::escapeChar($log['field3']);
	$log['descrip']   = S::escapeChar(getLangInfo('log',$log['descrip'],$log));
	$GLOBALS['db']->update("INSERT INTO pw_forumlog SET " . S::sqlSingle(array(
		'type'		=> $log['type'],
		'username1'	=> $log['username1'],
		'username2'	=> $log['username2'],
		'field1'	=> $log['field1'],
		'field2'	=> $log['field2'],
		'field3'	=> $log['field3'],
		'descrip'	=> $log['descrip'],
		'timestamp'	=> $log['timestamp'],
		'ip'		=> $log['ip']
	),false));
}
?>
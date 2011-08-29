<?php
!function_exists('readover') && exit('Forbidden');

/*
* 检查道具是否启用和用户是否拥有使用道具的权限
*/
function CheckUserTool($uid,$tooldb) {
	global $db,$groupid,$credit;

	if (!$tooldb['state']) {
		Showmsg('tool_close');
	}
	$condition = unserialize($tooldb['conditions']);
	if ($condition['group'] && strpos($condition['group'],",$groupid,") === false) {
		Showmsg('tool_grouplimit');
	}
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userdb = $userService->get($uid, false, true);
	require_once(R_P.'require/credit.php');
	$creditdb = $credit->get($uid,'CUSTOM');
	foreach ($condition['credit'] as $key => $value) {
		if ($value) {
			if (is_numeric($key)) {
				$creditdb[$key] < $value && Showmsg('tool_creditlimit');
			} elseif ($userdb[$key] < $value) {
				Showmsg('tool_creditlimit');
			}
		}
	}
}

function writetoollog($log) {
	global $db,$db_bbsurl;
	$log['type']    = getLangInfo('toollog',$log['type']);
	$log['filename']= S::escapeChar($log['filename']);
	$log['username']= S::escapeChar($log['username']);
	$log['descrip'] = S::escapeChar(getLangInfo('toollog',$log['descrip'],$log));

	$db->update("INSERT INTO pw_toollog SET " . S::sqlSingle(array(
		'type'		=> $log['type'],
		'filename'	=> $log['filename'],
		'nums'		=> $log['nums'],
		'money'		=> $log['money'],
		'descrip'	=> $log['descrip'],
		'uid'		=> $log['uid'],
		'touid'		=> $log['touid'],
		'username'	=> $log['username'],
		'ip'		=> $log['ip'],
		'time'		=> $log['time']
	)));
}
?>
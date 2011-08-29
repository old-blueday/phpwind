<?php
!function_exists('readover') && exit('Forbidden');

$g = $db->get_one("SELECT p.gid,p.rvalue AS allowbuy,u.grouptitle FROM pw_permission p LEFT JOIN pw_usergroups u ON p.gid=u.gid WHERE p.uid='0' AND p.fid='0' AND p.gid=" . S::sqlEscape($rt['paycredit']) . " AND p.rkey='allowbuy' AND u.gptype='special'");

if ($g && $g['allowbuy']) {

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if ($rt['extra_1'] == 1) {
		if ($rt['groupid'] == '-1') {
			$userService->update($rt['uid'], array('groupid' => $g['gid']));
		} else {
			$groups = $rt['groups'] ? $rt['groups'].$rt['groupid'].',' : ",$rt[groupid],";
			$userService->update($rt['uid'], array('groupid' => $g['gid'], 'groups' => $groups));
		}
	} else {
		$groups = $rt['groups'] ? $rt['groups'].$g['gid'].',' : ",$g[gid],";
		$userService->update($rt['uid'], array('groups' => $groups));
	}
	$db->pw_update(
		"SELECT uid FROM pw_extragroups WHERE uid=" . S::sqlEscape($rt['uid']) . " AND gid=" . S::sqlEscape($g['gid']),
		"UPDATE pw_extragroups SET ". S::sqlSingle(array(
			'togid'		=> $rt['groupid'],
			'startdate'	=> $timestamp,
			'days'		=> $rt['number']
		)) . " WHERE uid=" . S::sqlEscape($rt['uid']) . " AND gid=" . S::sqlEscape($g['gid'])
		,
		"INSERT INTO pw_extragroups SET " . S::sqlSingle(array(
			'uid'		=> $rt['uid'],
			'togid'		=> $rt['groupid'],
			'gid'		=> $g['gid'],
			'startdate'	=> $timestamp,
			'days'		=> $rt['number']
		))
	);
	
	M::sendNotice(
		array($rt['username']),
		array(
			'title' => getLangInfo('writemsg','groupbuy_title'),
			'content' => getLangInfo('writemsg','groupbuy_content',array(
				'fee'		=> $fee,
				'gname'		=> $g['grouptitle'],
				'number'	=> $rt['number']
			)),
		)
	);
	$ret_url = 'profile.php?action=buy';
}
?>
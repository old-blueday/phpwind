<?php
!function_exists('readover') && exit('Forbidden');
if ($inv_linkopen) {
	PwNewDB();
	if (advertRecord($uid)) {	
		if (!$credit) {
			require_once (R_P . 'require/credit.php');
		}
		if (empty($username)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$username = $userService->getUserNameByUserId($uid);
		}
		$credit->addLog('other_propaganda', array($inv_linkcredit => $inv_linkscore), array('uid' => $uid,
		'username' => $username, 'ip' => $onlineip));
		$credit->set($uid, $inv_linkcredit, $inv_linkscore);
	}
	Cookie('userads', '', 0);
}

/**
 * 添加
 * @param int $uid
 * @param string $username
 * @return null
 */
function advertRecord($uid = 0, $username = '') {
	global $onlineip, $timestamp, $db, $winduid, $inv_linktype, $inv_linkscore, $inv_linkcredit;
	if (empty($uid)) {
		return false;
	}
	if (empty($username)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$username = $userService->getUserNameByUserId($uid);
		if (!$username) return false;
	}
	$sql = "SELECT ip FROM pw_inviterecord WHERE uid=" . pwEscape($uid) . " AND ip=" . pwEscape($onlineip) . "";
	$rt = $db->get_one($sql);
	
	if ($rt && $rt['ip'] == $onlineip) {
		return false;
	}
	$visit = array(
		'uid' => $uid, 
		'username' => $username, 
		'typeid' => $inv_linktype, 
		'reward' => $inv_linkscore, 
		'unit' => $inv_linkcredit, 
		'ip' => $onlineip, 
		'create_time' => $timestamp
		);
	$sql = 'INSERT INTO pw_inviterecord SET ' . pwSqlSingle($visit);
	$db->update($sql);
	return true;
}
?>
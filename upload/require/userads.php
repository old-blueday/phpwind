<?php
!function_exists('readover') && exit('Forbidden');

$useradsInfo = GetCookie('userads');
$useradsInfo && (list($u,$a) = explode("\t",$useradsInfo));
if (is_numeric($u) || ($a && strlen($a)<16)) {
	PwNewDB();
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$tmpUser = $u ? $userService->get($u) : $userService->getByUserName($a);
	if ($tmpUser && advertRecord($tmpUser['uid'], $tmpUser['username'])) {
		if (!$credit) {
			require_once (R_P . 'require/credit.php');
		}
		$credit->addLog('other_propaganda', array($inv_linkcredit => $inv_linkscore), array('uid' => $tmpUser['uid'],
		'username' => $tmpUser['username'], 'ip' => $onlineip));
		$credit->set($tmpUser['uid'], $inv_linkcredit, $inv_linkscore);
		$credit->writeLog();
	}
}
Cookie('userads', '', 0);
unset($useradsInfo);

/**
 * 添加
 * @param int $uid
 * @param string $username
 * @return null
 */
function advertRecord($uid = 0, $username = '') {
	global $onlineip, $timestamp, $db, $winduid, $inv_linktype, $inv_linkscore, $inv_linkcredit;
	if (empty($uid) || empty($username)) {
		return false;
	}
	$sql = "SELECT ip FROM pw_inviterecord WHERE uid=" . S::sqlEscape($uid) . " AND ip=" . S::sqlEscape($onlineip) . "";
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
	$sql = 'INSERT INTO pw_inviterecord SET ' . S::sqlSingle($visit);
	$db->update($sql);
	return true;
}
?>
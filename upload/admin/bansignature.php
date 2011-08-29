<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=bansignature";

S::gp(array('action'));
if (empty($action)) {
	S::gp(array('page','banuser','adminban','starttime','endtime'));
	(!is_int($page) || $page < 1) && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$sql = ' WHERE 1';
	$banuser && $sql .= ' AND username = ' . S::sqlEscape($banuser);
	$adminban && $sql .= ' AND admin = ' . S::sqlEscape($adminban);
	if ($starttime) {
		!is_int($starttime) && $starttime = PwStrtoTime($starttime);
		$sql .= ' AND time > ' . S::sqlEscape($starttime);
	}
	if ($endtime) {
		!is_int($endtime) && $endtime = PwStrtoTime($endtime);
		$sql .= ' AND time < ' . S::sqlEscape($endtime);
	} 
	$total = $db->get_value('SELECT COUNT(*) AS total FROM pw_ban' . $sql);
	$result = array();
	if ($total) {
		$query = $db->query('SELECT * FROM pw_ban ' . $sql . ' ORDER BY time DESC' . $limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['datetime'] = get_date($rt['time']);
			$result[] = $rt;
		}
	}
	$url = urlencode($basename . '&banuser=' . $banuser . '&adminban=' . $adminban . '&starttime=' . $starttime . '&endtime=' . $endtime);
	$pages = numofpage($total, $page, ceil($total/$db_perpage), $url.'&');
	$url .= '&page=' . $page;
	include PrintEot('bansignature');exit;
} elseif ($action == 'freeban') {
	S::gp(array('id', 'returnurl', 'selid'));
	$selids = array();
	if (is_array($selid)) {
		foreach ($selid as $value) {
			is_numeric($value) && $selids[] = $value;
		}
	}
	is_numeric($id) && $selids[] = $id;
	empty($selids) && adminmsg('operate_error', urldecode($returnurl));
	$queryUsername = $db->query('SELECT username,uid FROM pw_ban WHERE id IN (' . S::sqlImplode($selids) . ')');
	$username = $uids = array();
	while ($rs = $db->fetch_array($queryUsername)) {
		$username[] = $rs['username'];
		$uids[] = $rs['uid'];
	}
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	foreach ($uids as $key => $value) {
		$userService->setUserStatus($value, PW_USERSTATUS_BANSIGNATURE, false);
		//* $uids[$key] = 'UID_'.$value;
	}
	$db->update('DELETE FROM pw_ban WHERE id IN (' . S::sqlImplode($selids) . ')');
	//* $_cache = getDatastore();
	//* $_cache->delete($uids);
	M::sendNotice(
				$username,
				array(
					'title' 	=> getLangInfo('writemsg','bansignature_title_0'),
					'content' 	=> getLangInfo('writemsg','bansignature_content_0',array(
						'manager'	=> $admin_name,
						'admindate'	=> get_date($timestamp),
					)),
				)
			);
	adminmsg('operate_success', urldecode($returnurl));
} elseif ($action == 'ban') {
	S::gp(array('step'));
	if (empty($step)) {
		include PrintEot('bansignature');exit;
	} else {
		S::gp(array('username', 'ifmsg', 'content'));
		(strpos($username, ',') !== false) && $username = array_unique(explode(',', $username));
		$ifmsg = (int) $ifmsg;
		$content = stripslashes($content);
		!is_array($username) && $username = array($username);
		$result = $resultUsername = array();
		$sql = 'SELECT uid, username FROM pw_members WHERE username IN (' . S::sqlImplode($username) . ')';
		$query = $db->query($sql);
		while ($rs = $db->fetch_array($query)) {
			$result[] = $rs;
			$resultUsername[] = $rs['username'];
		}
		$difference = array();
		if (count($username) != count($resultUsername)) {
			$difference = array_diff($username, $resultUsername);
			if (!empty($difference) && is_array($difference)) {
				$diffStr = implode(',', $difference) . '不存在';
				adminmsg($diffStr, $basename.'&action=ban');
			}
		}
		$insertArray = $uids = array();
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($result as $key => $value) {
			$insertArray[$key] = array($value['uid'], $value['username'], 1, $admin_name, $content, $timestamp);
			$userService->setUserStatus($value['uid'], PW_USERSTATUS_BANSIGNATURE, true);
			//* $uids[] = 'UID_'.$value['uid'];
		}
		$insertSql = 'INSERT INTO pw_ban (uid, username, type, admin, reason, time) VALUES' . S::sqlMulti($insertArray);
		$db->update($insertSql);
		//* $_cache = getDatastore();
		//* $_cache->delete($uids);
		if ($ifmsg) {
			M::sendNotice(
				$resultUsername,
				array(
					'title' 	=> getLangInfo('writemsg','bansignature_title_1'),
					'content' 	=> getLangInfo('writemsg','bansignature_content_1',array(
						'manager'	=> $admin_name,
						'admindate'	=> get_date($timestamp),
						'reason'	=> $content
					)),
				)
			);
		}
		adminmsg('operate_success', $basename.'&action=ban');
	}
}
?>
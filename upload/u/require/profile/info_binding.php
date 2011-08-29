<?php
!function_exists('readover') && exit('Forbidden');
if (!$_POST['step']){
	$ubinding = array();
	$ubindingneedupdatepwd = false;
	$query = $db->query("SELECT u2.uid as uuid, u1.password as oldpassword, m.password, m.uid,m.username,m.groupid,m.memberid,m.regdate,mb.postnum FROM pw_userbinding u1 LEFT JOIN pw_userbinding u2 ON u1.id=u2.id LEFT JOIN pw_members m ON m.uid=u2.uid LEFT JOIN pw_memberdata mb ON m.uid=mb.uid WHERE u1.uid=" . S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		if (empty($rt['uid'])) {
			$db->update("DELETE FROM pw_userbinding WHERE uid=".S::sqlEscape($rt['uuid'],false));
		} elseif ($rt['uid'] != $winduid) {
			$rt['groupid'] == '-1' && $rt['groupid'] = $rt['memberid'];
			$rt['regdate'] = get_date($rt['regdate']);
			$ubinding[] = $rt;
		} else {
			$ubindingneedupdatepwd = ($rt['password'] == $rt['oldpassword']) ? false : true;
		}
		unset($rt['password'], $rt['oldpassword']);
	}
	require_once uTemplate::PrintEot('info_binding');
	pwOutPut();
}else if($_POST['step'] == '2' || $_POST['step'] == '3'){
	PostCheck();
	!$_G['userbinding'] && Showmsg('undefined_action');

	S::gp(array('username','password','question','customquest','answer'));
	require_once(R_P . 'require/checkpass.php');

	if (empty($username) || empty($password)) {
		Showmsg('login_empty');
	}
	if ($username == $windid) {
		Showmsg('userbinding_same');
	}
	$password = md5($password);
	$safecv = $db_ifsafecv ? questcode($question, $customquest, $answer) : '';

	$db_logintype = 1;
	$logininfo = checkpass($username, $password, $safecv, 0);
	if (!is_array($logininfo)) {
		$logininfo == 'login_jihuo' && $logininfo = 'userbinding_not_activated';
		Showmsg($logininfo);
	}
	list($uid) = $logininfo;

	$arr = array();
	$query = $db->query("SELECT id,uid FROM pw_userbinding WHERE uid IN(" . S::sqlImplode(array($winduid, $uid)) . ")");
	while ($rt = $db->fetch_array($query)) {
		$arr[$rt['uid']] = $rt;
	}
	if (empty($arr)) {

		$db->update("INSERT INTO pw_userbinding SET " . S::sqlSingle(array('uid' => $winduid, 'password' => $userdb['password'])));
		$id = $db->insert_id();
		$db->update("INSERT INTO pw_userbinding SET " . S::sqlSingle(array('id' => $id, 'uid' => $uid, 'password' => $password)));

	} elseif (isset($arr[$winduid]) && !isset($arr[$uid])) {

		$db->update("INSERT INTO pw_userbinding SET " . S::sqlSingle(array('id' => $arr[$winduid]['id'], 'uid' => $uid, 'password' => $password)));
		$id = $arr[$winduid]['id'];

	} elseif (!isset($arr[$winduid]) && isset($arr[$uid])) {

		$db->update("INSERT INTO pw_userbinding SET " . S::sqlSingle(array('id' => $arr[$uid]['id'], 'uid' => $winduid, 'password' => $userdb['password'])));
		$id = $arr[$uid]['id'];

	} elseif (isset($arr[$winduid]) && isset($arr[$uid])) {

		if ($arr[$uid]['id'] == $arr[$winduid]['id']) {
			Showmsg('userbinding_has');
		} else {
			$db->update("UPDATE pw_userbinding SET id=" . S::sqlEscape($arr[$winduid]['id']) . ' WHERE id=' . S::sqlEscape($arr[$uid]['id']));
			$id = $arr[$winduid]['id'];
		}
	} else {
		Showmsg('undefined_action');
	}
	$db->update("UPDATE pw_userbinding u LEFT JOIN pw_members m ON u.uid=m.uid SET m.userstatus=m.userstatus|(1<<11) WHERE u.id=" . S::sqlEscape($id));
	_clearMembersCache($id);
// defend start	
	CloudWind::yunUserDefend('bindaccount', $winduid, $windid, $timestamp, 0, 101,'','','',array('uniqueid'=>$uid));
// defend end
	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);
} elseif ($_POST['step'] == '4') {

	PostCheck();
	S::gp(array('selid'));

	if ($selid && is_array($selid)) {
		$arr = array();
		$query = $db->query("SELECT u2.uid FROM pw_userbinding u1 LEFT JOIN pw_userbinding u2 ON u1.id=u2.id WHERE u1.uid=" . S::sqlEscape($winduid));
		while ($rt = $db->fetch_array($query)) {
			$arr[] = $rt['uid'];
		}
		if ($delarr = array_intersect($arr, $selid)) {
			$db->update("DELETE FROM pw_userbinding WHERE uid IN(" . S::sqlImplode($delarr) . ')');
			$tmp = $delarr + array($winduid);
			if (count(array_unique($tmp)) == count($arr)) {
				$delarr = $tmp;
			}

			$delarr = $userService->getByUserIds($delarr);
			foreach($delarr as $del) {
				$userService->setUserStatus($del['uid'], PW_USERSTATUS_USERBINDING, false);
			}
		}
	}

	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);

} elseif ($_POST['step'] == '5') {

	PostCheck();
	S::gp(array('bindpassword'));
	$bindpassword = md5($bindpassword);

	$userinfo = $userService->get($winduid);
	$userpwd = $userinfo['password'];
	if ($userpwd != $bindpassword) Showmsg('password_confirm_fail');

	$db->update("UPDATE pw_userbinding SET password=" . S::sqlEscape($userpwd) . ' WHERE uid=' . S::sqlEscape($winduid));
	unset($userinfo, $userpwd, $bindpassword);

	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);
}

function _clearMembersCache($id){
	global $db;
	$query = $db->query("SELECT uid FROM pw_userbinding WHERE id =" . S::sqlEscape($id));
	$uid = array();
	while ($rt = $db->fetch_array($query)){
		$uid[] = $rt['uid'];
	}
	Perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$uid));
}

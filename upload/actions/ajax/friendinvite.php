<?php
!defined('P_W') && exit('Forbidden');
//* require_once pwCache::getPath(D_P . 'data/bbscache/inv_config.php');
pwCache::getData(D_P . 'data/bbscache/inv_config.php');
require_once (R_P . 'u/lib/invite.class.php');
//* require_once pwCache::getPath(D_P . 'data/bbscache/mail_config.php');
pwCache::getData(D_P . 'data/bbscache/mail_config.php');
S::gp(array('step'), 'GP');
$normalUrl = "pw_ajax.php?action=friendinvite&";
$invite = new PW_Invite();
!empty($winduid) && $userId = $winduid;
if ($step == 'addressList') {
	S::gp(array('username', 'password', 'type'), 'GP');
	$emailList = $friendList = $userList = array();
	$emailCount = $friendCount = $notFriendCount = 0;
	if (!empty($type)) {
		$emailList = $invite->getEmailAddressListByType($type, $username, $password);
		if (!empty($emailList)) {
			$userList = $invite->getUsersFromEmailList(array_keys($emailList));
			$friendList = $invite->getFriendsFromUserList($userId, $userList);
			foreach ($userList as $key => $value) {
				if ($value['email'] && array_key_exists($value['email'], $emailList)) {
					unset($emailList[$value['email']]);
				}
			}
			foreach ($friendList as $key => $value) {
				if ($value['uid'] && array_key_exists($value['uid'], $userList)) {
					unset($userList[$value['uid']]);
				}
			}
		}
		$emailCount = is_array($emailList) ? count($emailList) : 0;
		$friendCount = count($friendList);
		$notFriendCount = count($userList);
	}
	require_once PrintEot('ajax_friendinvite');
	ajax_footer();
} elseif ($step == 'addfriend') {
	S::gp(array('value'), 'GP');
	empty($value) && Showmsg("非法操作");
	L::loadClass('friend', 'friend', false);
	$friendServer = new PW_Friend();
	$value = explode(',', $value);
	$check = $friendServer->getFriendCheck($value);
	$friend = array();
	foreach ($value as $key => $id) {
		if ($id != $winduid) {
			if (getstatus($check[$id], 3, 3) == 0) {
				$friend[] = array($winduid, $id, 0, $timestamp, '');
				$friend[] = array($id, $winduid, 0, $timestamp, '');
			} elseif (getstatus($check[$id], 3, 3) == 1) {
				$friend[] = array($id, $winduid, 1, $timestamp, '');
			}
		}
	}
	$friend && $friendServer->addFriends($friend);
	exit();
} elseif ($step == 'sendmail') {
	S::gp(array('value', 'extranote'), 'GP');
	if (!is_array($value)) {
		$emails = explode(',', str_replace(array("\r", "\n"), array('', ','), $value));
	}
	count($emails) > 20 && ajaxExport('mail_limit');
	strlen($extranote) > 200 && ajaxExport('mode_o_extra_toolang');
	if ($emails) {
		foreach ($emails as $key => $email) {
			$emails[$key] = trim($email);
			!isEmail($email) && ajaxExport('请输入正确的邮箱地址');
			if (!$email || !isEmail($email)) {
				unset($emails[$key]);
			}
		}
	}
	!$emails && ajaxExport('mail_is_empty');
	$hash = appkey($winduid);
	$invite_url = $db_bbsurl . '/u.php?a=invite&uid=' . $winduid . '&hash=' . $hash;
	$invite->sendInviteEmail($emails);
	ajaxExport('success');
} elseif ($step == 'sendinvitecode') {
	S::gp(array('value', 'invcodes', 'extranote'), 'GP');
	if (!is_array($value)) {
		$emails = explode(',', str_replace(array("\r", "\n"), array('', ','), $value));
	}
	count($emails) > 20 && ajaxExport('mail_limit');
	strlen($extranote) > 200 && ajaxExport('mode_o_extra_toolang');
	if ($emails) {
		foreach ($emails as $key => $email) {
			$emails[$key] = trim($email);
			if (!$email || !isEmail($email)) {
				unset($emails[$key]);
			}
		}
	}
	!$emails && ajaxExport('mail_is_empty');
	$invcodes = explode(',', trim($invcodes, ','));
	$invlink = '';
	foreach ($invcodes as $key => $value) {
		$invlink .= '<a href=\"' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $value . '\">' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $value . '</a><br>';
	}
	$invite->sendInviteCode($emails);
	ajaxExport('success');
} elseif ($step == 'simple') {
	//* require_once pwCache::getPath(D_P . 'data/bbscache/dbreg.php');
	extract(pwCache::getData(D_P . 'data/bbscache/dbreg.php', false));
	$email_content = '';
	if ($rg_allowregister == 1) {
		$email_content .= $inv_linkcontent . "\r\n";
	} elseif($rg_allowregister == 2) {
		S::gp(array('invcode'), 'GP');
		$invcode = trim($invcode,',');
		//$invitelink = '<a href="' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $invcode . '">' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $invcode . '</a><br>';
		$inv_email = str_replace(array('$username','$sitename','$invitecode','$uid'), array($windid,$db_sitename,$invcode,$winduid), $inv_email);
		$email_content .= $inv_email."\r\n";
	}
} elseif ($step == 'delInvCode') {
	S::gp(array('invcode'), 'GP');
	empty($invcode) && ajaxExport("请选择要删除的邀请码");
	$invcode = explode(',', trim($invcode, ','));
	$db->update("DELETE FROM pw_invitecode WHERE id IN (" . S::sqlImplode($invcode) . ") AND uid=" . S::sqlEscape($winduid));
	ajaxExport("删除操作成功!");
} elseif ($step == 'addInvCode') {
	require_once (R_P . 'require/credit.php');
	$allowinvite = allowcheck($inv_groups, $groupid, $winddb['groups']) ? 1 : 0;
	$allowinvite == 0 && ajaxExport("抱歉，您没有购买权限");
	$usrecredit = ${'db_' . $inv_credit . 'name'};
	$creditto = array('rvrc' => $userrvrc, 'money' => $winddb['money'], 'credit' => $winddb['credit'],
		'currency' => $winddb['currency']);
	if ($inv_limitdays) {
		$rt = $db->get_one("SELECT createtime FROM pw_invitecode WHERE uid=" . S::sqlEscape($winduid) . "ORDER BY createtime DESC LIMIT 0,1");
		if ($timestamp - $rt['createtime'] < $inv_limitdays * 86400) {
			ajaxExport("邀请码购买时间限制，请稍侯");
		}
	}
	S::gp(array('invnum'), 'GP');
	$invnum = (int) $invnum;
	if ($invnum < 1) ajaxExport("购买的邀请码数量必须大于0");
	//(!is_numeric($invnum) || $invnum < 1) && $invnum = 1;
	if ($creditto[$inv_credit] < $invnum * $inv_costs) {
		ajaxExport("您的积分不足以购买邀请码");
	}
	for ($i = 0; $i < $invnum; $i++) {
		$invcode = randstr(16);
		$db->update("INSERT INTO pw_invitecode" . " SET " . S::sqlSingle(array('invcode' => $invcode, 'uid' => $winduid,
			'createtime' => $timestamp, 'type' => 1)));
	}
	$cutcredit = $invnum * $inv_costs;
	$credit->addLog('hack_invcodebuy', array($inv_credit => -$cutcredit), array('uid' => $winduid,
		'username' => $windid, 'ip' => $onlineip, 'invnum' => stripslashes($invnum)));
	$credit->set($winduid, $inv_credit, -$cutcredit);
	ajaxExport("邀请码购买成功!");
}

require_once PrintEot('ajax_friendinvite');
ajax_footer();

function ajaxExport($output) {
	echo is_array($output) ? pwJsonEncode($output) : $output;
	ajax_footer();
	exit();
}

function isEmail($email){
		return preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i",$email);
}
?>
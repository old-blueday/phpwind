<?php
!defined('R_P') && exit('Forbidden');

//require_once(R_P.'require/pw_func.php');
require_once(R_P.'require/credit.php');
S::gp(array('job','gid'));
$pro_tab = 'permission';

if (empty($job)) {

	$specialdb = $sright = $gids = array();
	$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype='special'");
	while ($rt = $db->fetch_array($query)) {
		$sright[$rt['gid']] = $rt;
		$gids[] = $rt['gid'];
	}
	if ($gids) {
		$query = $db->query("SELECT gid,rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid IN(" . S::sqlImplode($gids) . ") AND rkey IN ('sellinfo','sellprice','rmbprice','selltype', 'selllimit', 'allowbuy')");
		while ($rt = $db->fetch_array($query)) {
			$sright[$rt['gid']][$rt['rkey']] = $rt['rvalue'];
		}
		foreach ($sright as $key => $value) {
			if ($value['allowbuy']) {
				$value['enddate'] = '-';
				$value['selltype'] = $credit->cType[$value['selltype']];
				$specialdb[$key] = $value;
			}
		}
		$query = $db->query("SELECT gid,startdate,days FROM pw_extragroups WHERE uid=".S::sqlEscape($winduid));
		while ($rt = $db->fetch_array($query)) {
			if (array_key_exists($rt['gid'],$specialdb)) {
				$specialdb[$rt['gid']]['days']		= $rt['days'];
				$specialdb[$rt['gid']]['startdate']	= $rt['startdate'];
				$specialdb[$rt['gid']]['enddate']	= get_date($rt['startdate'] + $rt['days']*86400,'Y-m-d');
			}
		}
	}
	require_once uTemplate::PrintEot('profile_buy');
	pwOutPut();

} elseif ($job == 'buy') {

	$rt = $db->get_one("SELECT uid,startdate,days FROM pw_extragroups WHERE uid=".S::sqlEscape($winduid) . " AND gid=" . S::sqlEscape($gid));
	if ($rt && $timestamp <= $rt['startdate'] + $rt['days']*86400) {
		$enddate = get_date($rt['startdate'] + $rt['days']*86400,'Y-m-d');
		Showmsg('specialgroup_exists');
	}
	$rt = $db->get_one("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype='special' AND gid=" . S::sqlEscape($gid));
	if (!$rt) {
		Showmsg('specialgroup_error');
	}
	$query = $db->query("SELECT gid,rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid=" . S::sqlEscape($gid) . " AND rkey IN ('sellinfo','sellprice','rmbprice','selltype','selllimit','allowbuy')");
	while ($permi = $db->fetch_array($query)) {
		$rt['sright'][$permi['rkey']] = $permi['rvalue'];
	}
	if (!$rt['sright']['allowbuy']) {
		Showmsg('special_allowbuy');
	}
	if (empty($_POST['step'])) {

		$rt['sright']['selltype'] = $credit->cType[$rt['sright']['selltype']];
		require_once uTemplate::PrintEot('profile_buy');
		pwOutPut();

	} else {

		PostCheck();
		S::gp(array('pwpwd'), 'P');
		S::gp(array('days','buymethod','options'), null, 2);
		if (!is_numeric($days) || $days <= 0) {
			Showmsg('illegal_nums');
		}
		if ($days < $rt['sright']['selllimit']) {
			Showmsg('special_selllimit');
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$mb = $userService->get($winduid);

		if ($gid == $groupid || strpos($mb['groups'],",$gid,") !== false) {
			Showmsg('specialgroup_noneed');
		}
		if ($buymethod) {
			if ($rt['sright']['rmbprice'] <= 0) {
				Showmsg('undefined_action');
			}
			//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
			pwCache::getData(D_P.'data/bbscache/ol_config.php');
			if (!$ol_onlinepay) {
				Showmsg($ol_whycolse);
			}
			$grouptitle = $rt['grouptitle'];
			$order_no = '1'.str_pad($winduid,10, "0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);
			$db->update("INSERT INTO pw_clientorder SET " . S::sqlSingle(array(
				'order_no'	=> $order_no,
				'type'		=> 3,
				'uid'		=> $winduid,
				'paycredit'	=> $gid,
				'price'		=> $rt['sright']['rmbprice'],
				'number'	=> $days,
				'date'		=> $timestamp,
				'state'		=> 0,
				'extra_1'	=> $options
			)));
			if (!$ol_payto) {
				Showmsg('olpay_alipayerror');
			}
			require_once(R_P.'require/onlinepay.php');
			$olpay = new OnlinePay($ol_payto);
			ObHeader($olpay->alipayurl($order_no, round($rt['sright']['rmbprice'] * $days, 2), 3));
		}
		if (md5($pwpwd) != $mb['password']) {
			Showmsg('password_error');
		}
		if ($rt['sright']['sellprice'] <= 0) {
			Showmsg('undefined_action');
		}
		$needcur = $days * $rt['sright']['sellprice'];
		$cur = $credit->get($winduid,$rt['sright']['selltype']);
		if ($cur === false) {
			Showmsg('numerics_checkfailed');
		}
		if ($cur < $needcur) {
			Showmsg('noenough_currency');
		}
		$credit->addLog('main_buygroup',array($rt['sright']['selltype'] => -$needcur),array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip,
			'gptitle'	=> $rt['grouptitle'],
			'days'		=> $days
		));
		if (!$credit->set($winduid,$rt['sright']['selltype'],-$needcur)) {
			Showmsg('numerics_checkfailed');
		}
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if ($options == 1) {
			if ($winddb['groupid'] == '-1') {
				$userService->update($winduid, array('groupid' => $gid));
			} else {
				$groups = $mb['groups'] ? $mb['groups'].$winddb['groupid'].',' : ",$winddb[groupid],";
				$userService->update($winduid, array('groupid' => $gid, 'groups' => $groups));
			}
		} else {
			$groups = $mb['groups'] ? $mb['groups'].$gid.',' : ",$gid,";
			$userService->update($winduid, array('groups' => $groups));
		}
		
		$db->pw_update(
			"SELECT uid FROM pw_extragroups WHERE uid=" . S::sqlEscape($winduid) . " AND gid=" . S::sqlEscape($gid),
			"UPDATE pw_extragroups SET ". S::sqlSingle(array(
				'togid'		=> $winddb['groupid'],
				'startdate'	=> $timestamp,
				'days'		=> $days
			)) . " WHERE uid=".S::sqlEscape($winduid)."AND gid=".S::sqlEscape($gid)
			,
			"INSERT INTO pw_extragroups SET " . S::sqlSingle(array(
				'uid'		=> $winduid,
				'togid'		=> $winddb['groupid'],
				'gid'		=> $gid,
				'startdate'	=> $timestamp,
				'days'		=> $days
			))
		);
		refreshto("profile.php?action=buy",'group_buy_success');
	}
}
?>
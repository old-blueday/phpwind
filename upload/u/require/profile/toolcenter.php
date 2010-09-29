<?php
!function_exists('readover') && exit('Forbidden');
$wind_in = 'toolcenter';
$USCR = 'set_toolcenter';

require_once(D_P.'data/bbscache/level.php');

if(!$db_hackdb[$wind_in] || !file_exists(R_P."u/require/profile/toolcenter.php")){
	Showmsg('hack_error');
}

!$db_toolifopen && Showmsg('toolcenter_close');

InitGP(array('job'));
if(isset($job) && $job == 'ajax'){
	define('AJAX','1');
}

require_once(R_P.'require/credit.php');
$userdb = array(
	'money'		=> $winddb['money'],
	'rvrc'		=> $userrvrc,
	'credit'	=> $winddb['credit'],
	'currency'	=> $winddb['currency']
);
foreach ($credit->get($winduid,'CUSTOM') as $key => $value) {
	$userdb[$key] = $value;
}
$usercreditdb = $userdb;

$total_tool_nums = array('valid_nums'=>0, 'sell_nums'=>0);
$sell_status = 0;
$query = $db->query("SELECT nums, sellnums, sellstatus  FROM pw_usertool WHERE uid=".pwEscape($winduid,false));
while ($rt = $db->fetch_array($query)) {
	$total_tool_nums['valid_nums'] += $rt['nums'];
	$total_tool_nums['sell_nums'] += $rt['sellnums'];
	$sell_status = $rt['sellstatus'];
}

if (empty($job)) {

	$query = $db->query("SELECT * FROM pw_tools WHERE state=1 AND stock != 0 ORDER BY vieworder");
	while ($rt = $db->fetch_array($query)) {
		$rt['descrip'] = substrs($rt['descrip'],30);
		!$rt['creditype'] && $rt['creditype'] = 'currency';
		$tooldb[] = $rt;
	}
	require_once uTemplate::PrintEot('profile_toolcenter');
	pwOutPut();

} elseif ($job == 'mytool') {
	$query = $db->query("SELECT u.*,t.name,t.price,t.creditype,t.stock,t.descrip,t.type,t.logo FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid=".pwEscape($winduid)." AND (u.nums + u.sellnums)>0");
	while ($rt = $db->fetch_array($query)) {
		!$rt['creditype'] && $rt['creditype'] = 'currency';
		$tooldb[] = $rt;
	}
	require_once uTemplate::PrintEot('profile_toolcenter');
	pwOutPut();

} elseif ($job == 'user') {

	!$db_allowtrade && Showmsg('trade_close');
	InitGP(array('uid'));
	$sqladd = $owner = '';
	if (is_numeric($uid)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$owner = $userService->getUserNameByUserId($uid);
		if (!$owner) {
			$errorname = '';
			Showmsg('user_not_exists');
		}
		$sqladd = "AND u.uid=".pwEscape($uid);
	}
	$query = $db->query("SELECT u.*,t.name,t.descrip,t.logo,t.creditype,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE sellnums!=0 $sqladd");
	while ($rt = $db->fetch_array($query)) {
		$rt['descrip'] = substrs($rt['descrip'],45);
		!$rt['creditype'] && $rt['creditype'] = 'currency';
		$tooldb[] = $rt;
	}
	
	require_once uTemplate::PrintEot('profile_toolcenter');
	pwOutPut();

} elseif ($job == 'sell') {

	!$db_allowtrade && Showmsg('trade_close');
	InitGP(array('id'));

	if (empty($_POST['step'])) {

		$rt = $db->get_one("SELECT u.*,t.name,t.price,t.creditype,t.logo FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE uid=".pwEscape($winduid,false)." AND toolid=".pwEscape($id));
		!$rt && Showmsg('undefined_action');
		$rt['nums'] == 0 && Showmsg('unenough_toolnum');
		!$rt['creditype'] && $rt['creditype'] = 'currency';
		require_once uTemplate::PrintEot('profile_toolcenter');
		pwOutPut();

	} else {

		$rt = $db->get_one("SELECT u.*,t.name FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE uid=".pwEscape($winduid)."AND toolid=".pwEscape($id));
		if ($rt) {
			InitGP(array('nums','price'),'P');
			$nums   = (int)$nums;
			$price  = (int)$price;
			$price <= 0 && Showmsg('illegal_nums');
			$nums  <= 0 && Showmsg('illegal_nums');
			$rt['nums'] < $nums && Showmsg('unenough_nums');

			$db->update("UPDATE pw_usertool SET nums=nums-".pwEscape($nums).",sellnums=sellnums+".pwEscape($nums).",sellprice=".pwEscape($price)."WHERE uid=".pwEscape($winduid)."AND toolid=".pwEscape($id));

			require_once(R_P.'require/tool.php');
			$logdata = array(
				'type'		=>	'sell',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'sell_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$rt['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);
			refreshto("profile.php?action=toolcenter&job=mytool",'operate_success');
		} else {
			Showmsg('undefined_action');
		}
	}
} elseif ($job == 'unsell') {
	InitGP(array('id'));

	$rt = $db->get_one("SELECT u.*,t.name,t.price,t.creditype,t.logo FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE uid=".pwEscape($winduid,false)." AND toolid=".pwEscape($id));
	!$rt && Showmsg('undefined_action');
	$rt['sellnums'] == 0 && Showmsg('neednt_unsell_tool');

	$db->update("UPDATE pw_usertool SET nums=".($rt['nums'] + $rt['sellnums']).", sellnums=0 WHERE uid=".pwEscape($winduid,false)." AND toolid=".pwEscape($id)." LIMIT 1");
	refreshto("profile.php?action=toolcenter&job=user&uid=".$winduid,'operate_success');
} elseif ($job == 'unsellall') {
	$db->update("UPDATE pw_usertool SET nums=nums + sellnums, sellnums=0 WHERE uid=".pwEscape($winduid,false)." AND sellnums>0");
	refreshto("profile.php?action=toolcenter&job=user&uid=".$winduid,'operate_success');
} elseif ($job == 'close') {
	!$sell_status && Showmsg('neednt_close_toolsell');

	//TODO modify action == 'buy/buyuser', add sellstatus
	//TODO modify action == 'user', where condition
	$db->update("UPDATE pw_usertool SET sellstatus=0 WHERE uid=".pwEscape($winduid,false));
	refreshto("profile.php?action=toolcenter&job=user&uid=".$winduid,'operate_success');
} elseif ($job == 'open') {
	$sell_status && Showmsg('neednt_open_toolsell');
	(!$total_tool_nums['valid_nums'] && !$total_tool_nums['sell_nums']) && Showmsg('nonetool_cannt_open');

	$db->update("UPDATE pw_usertool SET sellstatus=1 WHERE uid=".pwEscape($winduid,false));
	refreshto("profile.php?action=toolcenter&job=user&uid=".$winduid,'operate_success');
} elseif ($job == 'buyuser') {

	InitGP(array('id','uid'));

	if (empty($_POST['step'])) {

		$rt = $db->get_one("SELECT * FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.toolid=".pwEscape($id)."AND u.uid=".pwEscape($uid));
		if ($rt) {
			$condition = unserialize($rt['conditions']);
			$groupids  = $condition['group'];
			$fids      = $condition['forum'];

			foreach ($condition['credit'] as $key => $value) {
				$key == 'rvrc' && $value /= 10;
				$condition['credit'][$key] = (int)$value;
			}
			$usergroup = "";
			$num = 0;
			foreach ($ltitle as $key => $value) {
				if ($key != 1 && $key != 2) {
					if (strpos($groupids,','.$key.',') !== false) {
						$num++;
						$htm_tr = $num%5 == 0 ?  '' : '';
						$usergroup .=" <li>$value</li>$htm_tr";
					}
				}
			}
			$num        = 0;
			$forumcheck = "";
			$sqladd     = " AND f_type!='hidden' AND cms='0'";
			$query      = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND cms='0'");
			while ($fm = $db->fetch_array($query)) {
				if (strpos($fids,','.$fm['fid'].',') !== false) {
					$num ++;
					$htm_tr = $num % 5 == 0 ? '' : '';
					$forumcheck .= "<li>$fm[name]</li>$htm_tr";
				}
			}
			!$rt['creditype'] && $rt['creditype'] = 'currency';
			require_once uTemplate::PrintEot('profile_toolcenter');
			pwOutPut();
		} else {
			Showmsg('undefined_action');
		}
	} else{

		$toolinfo = $db->get_one("SELECT u.*,t.name,t.creditype,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.toolid=".pwEscape($id)."AND u.uid=".pwEscape($uid));

		$nums  = (int)GetGP('nums');
		$nums <= 0 && Showmsg('illegal_nums');
		$price = $toolinfo['sellprice'] * $nums;
		$toolinfo['sellnums'] < $nums && Showmsg('unenough_sellnum');

		if ($winduid == $toolinfo['uid']) {
			require_once(R_P.'require/tool.php');
			$logdata = array(
				'type'		=>	'buy',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'buyself_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$toolinfo['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);
			$db->update("UPDATE pw_usertool SET nums=nums+".pwEscape($nums).",sellnums=sellnums-".pwEscape($nums)."WHERE uid=".pwEscape($toolinfo['uid'],false)."AND toolid=".pwEscape($id));
		} else {
			if (procLock('tool_buyuser',$winduid)) {

				!$toolinfo['creditype'] && $toolinfo['creditype'] = 'currency';
				if ($credit->get($winduid, $toolinfo['creditype']) < $price) {
					$creditname = $credit->cType[$toolinfo['creditype']];
					procUnLock('tool_buyuser',$winduid);
					Showmsg('unenough_money');
				}
				$credit->addLog('hack_toolubuy',array($toolinfo['creditype'] => -$price),array(
					'uid'		=> $winduid,
					'username'	=> $windid,
					'ip'		=> $onlineip,
					'seller'	=> $toolinfo['username'],
					'nums'		=> $nums,
					'toolname'	=> $toolinfo['name']
				));
				$credit->addLog('hack_toolsell',array($toolinfo['creditype'] => $price),array(
					'uid'		=> $toolinfo['uid'],
					'username'	=> $toolinfo['username'],
					'ip'		=> $onlineip,
					'buyer'		=> $windid,
					'toolname'	=> $toolinfo['name']
				));
				$credit->set($winduid,$toolinfo['creditype'],-$price,false);
				$credit->set($toolinfo['uid'],$toolinfo['creditype'],$price,false);
				$credit->runsql();

				$db->pw_update(
					"SELECT uid FROM pw_usertool WHERE uid=" . pwEscape($winduid) . " AND toolid=" . pwEscape($id),
					"UPDATE pw_usertool SET nums=nums+" .pwEscape($nums) . " WHERE uid=" . pwEscape($winduid) . " AND toolid=" . pwEscape($id),
					"INSERT INTO pw_usertool SET " . pwSqlSingle(array('nums' => $nums, 'uid' => $winduid, 'toolid' => $id, 'sellstatus' => $sell_status))
				);
				$db->update("UPDATE pw_usertool SET sellnums=sellnums-".pwEscape($nums)."WHERE uid=".pwEscape($toolinfo['uid'],false)."AND toolid=".pwEscape($id));

				require_once(R_P.'require/tool.php');
				$logdata = array(
					'type'		=> 'buy',
					'nums'		=> $nums,
					'money'		=> $price,
					'descrip'	=> 'buyuser_descrip',
					'uid'		=> $winduid,
					'username'	=> $windid,
					'ip'		=> $onlineip,
					'time'		=> $timestamp,
					'toolname'	=> $toolinfo['name'],
					'from'		=> $toolinfo['username'],
				);
				writetoollog($logdata);

				procUnLock('tool_buyuser',$winduid);
			}
		}
		refreshto("profile.php?action=toolcenter&job=user",'operate_success');
	}
} elseif ($job == 'buy') {
	!$db_allowtrade && Showmsg('trade_close');
	InitGP(array('id'));

	if (empty($_POST['step'])) {

		$rt = $db->get_one("SELECT * FROM pw_tools WHERE id=" . pwEscape($id));
		if (empty($rt)) {
			Showmsg('data_error');
		}
		if ($rt['state'] == 0) {
			Showmsg('tool_buyclose');
		}
		$rt['stock'] == 0 && Showmsg('no_stock');
		$condition = unserialize($rt['conditions']);
		$groupids  = $condition['group'];
		$fids      = $condition['forum'];

		foreach ($condition['credit'] as $key => $value) {
			$key == 'rvrc' && $value /= 10;
			$condition['credit'][$key] = (int)$value;
		}
		$usergroup = $forumcheck = '';
		$num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2) {
				if (strpos($groupids,','.$key.',') !== false) {
					$num ++;
					$htm_tr = $num%5 == 0 ?  '</tr><tr>' : '';
					$usergroup .= "<td width='20%'>$value</td>$htm_tr";
				}
			}
		}
		if ($fids) {
			$num = 0;
			$query = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND cms='0'");
			while ($fm = $db->fetch_array($query)) {
				if (strpos($fids,','.$fm['fid'].',') !== false) {
					$num ++;
					$htm_tr = $num % 5 == 0 ? '</tr><tr>' : '';
					$forumcheck .= "<td width='20%'>$fm[name]</td>$htm_tr";
				}
			}
		}
		!$rt['creditype'] && $rt['creditype'] = 'currency';

		require_once uTemplate::PrintEot('profile_toolcenter');
		pwOutPut();

	} else {
		if (procLock('tool_buy',$winduid)) {
			InitGP(array('buymethod','nums'), null, 2);
			$toolinfo = $db->get_one("SELECT * FROM pw_tools WHERE id=" . pwEscape($id));
			if($nums <= 0){
				procUnLock('tool_buy',$winduid);
				Showmsg('illegal_nums');
			}
			if ($toolinfo['stock'] < $nums) {
				procUnLock('tool_buy',$winduid);
				Showmsg('unenough_stock');
			}

			if ($buymethod) {
				if ($toolinfo['rmb'] <= 0) {
					procUnLock('tool_buy',$winduid);
					Showmsg('undefined_action');
				}
				include_once(D_P.'data/bbscache/ol_config.php');
				if (!$ol_onlinepay) {
					procUnLock('tool_buy',$winduid);
					Showmsg($ol_whycolse);
				}
				$order_no = '1'.str_pad($winduid,10, "0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);
				$db->update("INSERT INTO pw_clientorder SET " . pwSqlSingle(array(
					'order_no'	=> $order_no,
					'type'		=> 1,
					'uid'		=> $winduid,
					'paycredit'	=> $id,
					'price'		=> $toolinfo['rmb'],
					'number'	=> $nums,
					'date'		=> $timestamp,
					'state'		=> 0
				)));
				if (!$ol_payto) {
					procUnLock('tool_buy',$winduid);
					Showmsg('olpay_alipayerror');
				}
				require_once(R_P.'require/onlinepay.php');
				$olpay = new OnlinePay($ol_payto);
				procUnLock('tool_buy',$winduid);
				ObHeader($olpay->alipayurl($order_no, $toolinfo['rmb'] * $nums, 1));
			}
			if ($toolinfo['price'] <= 0) {
				procUnLock('tool_buy',$winduid);
				Showmsg('undefined_action');
			}
			$price = $toolinfo['price'] * $nums;
			!$toolinfo['creditype'] && $toolinfo['creditype'] = 'currency';
			if ($credit->get($winduid, $toolinfo['creditype']) < $price) {
				$creditname = $credit->cType[$toolinfo['creditype']];
				if (array_key_exists($toolinfo['creditype'],$db_creditpay)) {
					procUnLock('tool_buy',$winduid);
					Showmsg('unenough_currency');
				} else {
					procUnLock('tool_buy',$winduid);
					Showmsg('unenough_money');
				}
			}
			$credit->addLog('hack_toolbuy',array($toolinfo['creditype'] => -$price),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip,
				'nums'		=> $nums,
				'toolname'	=> $toolinfo['name']
			));
			$credit->set($winduid,$toolinfo['creditype'],-$price);

			$db->update("UPDATE pw_tools SET stock=stock-" . pwEscape($nums) . " WHERE id=" . pwEscape($id));
			$db->pw_update(
				"SELECT uid FROM pw_usertool WHERE uid=" . pwEscape($winduid) . " AND toolid=".pwEscape($id),
				"UPDATE pw_usertool SET nums=nums+" . pwEscape($nums) . " WHERE uid=" . pwEscape($winduid) . " AND toolid=" . pwEscape($id),
				"INSERT INTO pw_usertool SET " . pwSqlSingle(array('nums' => $nums, 'uid' => $winduid, 'toolid' => $id, 'sellstatus' => $sell_status))
			);
			require_once(R_P.'require/tool.php');
			$logdata = array(
				'type'		=>	'buy',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'buy_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$toolinfo['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);

			procUnLock('tool_buy',$winduid);
		}
		refreshto("profile.php?action=toolcenter",'operate_success');
	}
} elseif ($job == 'use' || $job == 'ajax') {
	$toolid = (int)GetGP('toolid');
	if (!$toolid) {
		$tooldb = array();
		$query  = $db->query("SELECT * FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid=".pwEscape($winduid)."ORDER BY vieworder");
		while ($rt = $db->fetch_array($query)) {
			$rt['descrip'] = substrs($rt['descrip'],45);
			$tooldb[] = $rt;
		}
		if (!$tooldb) {
			Showmsg('no_tool');
		}
		require_once uTemplate::PrintEot('profile_toolcenter');
		pwOutPut();
	}
	$tooldb = $db->get_one("SELECT u.nums,t.name,t.filename,t.state,t.type,t.conditions FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid=".pwEscape($winduid)."AND u.toolid=".pwEscape($toolid));
	
	!$db_toolifopen && Showmsg('toolcenter_close');
	if (!$tooldb || $tooldb['nums'] <= 0) {
		Showmsg('nothistool');
	}
	if ($tooldb['type'] == 1) {
		!$tid && Showmsg('illegal_tid');
		$condition = unserialize($tooldb['conditions']);
		$tpcdb = $db->get_one("SELECT fid,subject,authorid,topped,toolfield FROM pw_threads WHERE tid=" . pwEscape($tid));
		if (!$tpcdb) {
			Showmsg('illegal_tid');
		}
		if ($condition['forum'] && strpos($condition['forum'],",$tpcdb[fid],") === false) {
			Showmsg('tool_forumlimit');
		}
	}
	require_once(R_P.'require/tool.php');
	CheckUserTool($winduid,$tooldb);
	if (file_exists(R_P. 'u/require/profile/toolcenter/'.$tooldb['filename'].'.php')) {
		require_once Pcv(R_P. 'u/require/profile/toolcenter/'.$tooldb['filename'].'.php');
	} else {
		Showmsg('tooluse_not_finished');
	}
}
?>
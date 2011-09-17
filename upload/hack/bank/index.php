<?php
!function_exists('readover') && exit('Forbidden');
$wind_in='bank';
//* require_once pwCache::getPath(D_P.'data/bbscache/bk_config.php');
pwCache::getData(D_P.'data/bbscache/bk_config.php');

$groupid == 'guest' && Showmsg('not_login');
$bk_open == '0'     && Showmsg('bk_close');

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$bankdb = $userService->get($winduid, false, false, true);//deposit,startdate,ddeposit,dstartdate

require_once(R_P.'require/credit.php');
$creditdb = $credit->get($winduid,'CUSTOM');

S::gp(array('action'));

if (empty($action)) {

	$showdb = array();
	foreach ($creditdb as $key => $value) {
		$_CREDITDB[$key] && $showdb[$key] = array($_CREDITDB[$key][0],$value);
	}
	if (!$bankdb) {
		$bankdb['deposit'] = $bankdb['ddeposit'] = $bankdb['startdate'] = $bankdb['dstartdate'] = 0;
	}
	if ($bankdb['startdate'] && $timestamp>$bankdb['startdate']) {
		$accrual = round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);
	} else {
		$accrual = 0;
	}
	$ddates = floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
	if ($bankdb['dstartdate'] && $ddates) {
		$daccrual = round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
	} else {
		$daccrual = 0;
	}

	$allmoney = $winddb['money'] + $bankdb['deposit'] + $bankdb['ddeposit'];
	if (!$bankdb['deposit'] || !$bankdb['startdate']) {
		$bankdb['savetime'] = "--";
	} else {
		$bankdb['savetime'] = get_date($bankdb['startdate']);
	}
	if (!$bankdb['ddeposit'] || !$bankdb['dstartdate']) {
		$bankdb['dsavetime'] = "--";
	} else {
		$bankdb['dsavetime'] = get_date($bankdb['dstartdate'],'Y-m-d');
		$endtime = get_date($bankdb['dstartdate']+$bk_ddate*30*86400,'Y-m-d');
	}
	foreach ($_CREDITDB as $key => $value) {
		if (!$showdb[$key]) {
			$showdb[$key][0] = $value[0];
			$showdb[$key][1] = 0;
		}
	}
	!$bk_num && $bk_num=10;
	if (!$bk_per || $timestamp - pwFilemtime(D_P."data/bbscache/bank_sort.php") > $bk_per*3600) {
		$_DESPOSTDB = array();
		$query = $db->query("SELECT i.uid,m.username,i.deposit,i.startdate FROM pw_memberinfo i LEFT JOIN pw_members m ON m.uid=i.uid ORDER BY i.deposit DESC ".S::sqlLimit($bk_num));
		while ($deposit = $db->fetch_array($query)) {
			if ($deposit['deposit']) {
				$deposit['startdate'] = $deposit['startdate'] ? get_date($deposit['startdate']) : '';
				$_DESPOSTDB[] = array($deposit['uid'],$deposit['username'],$deposit['deposit'], $deposit['startdate']);
			}
		}
		$_DDESPOSTDB = array();
		$query = $db->query("SELECT i.uid,username,ddeposit,dstartdate FROM pw_memberinfo i LEFT JOIN pw_members m ON m.uid=i.uid ORDER BY ddeposit DESC ".S::sqlLimit($bk_num));
		while ($deposit = $db->fetch_array($query)) {
			if ($deposit['ddeposit']) {
				$deposit['dstartdate'] = $deposit['dstartdate'] ? get_date($deposit['dstartdate']) : '';
				$_DDESPOSTDB[] = array($deposit['uid'],$deposit['username'],$deposit['ddeposit'], $deposit['dstartdate']);
			}
		}
		$wirtedb = savearray('_DESPOSTDB',$_DESPOSTDB);
		$wirtedb.= "\n".savearray('_DDESPOSTDB',$_DDESPOSTDB);
		pwCache::writeover(D_P.'data/bbscache/bank_sort.php',"<?php\r\n".$wirtedb.'?>');
	}
	include (D_P."data/bbscache/bank_sort.php");
	require_once PrintHack('index');footer();
}

if ($_POST['action'] && $bk_timelimit && ($timestamp-$bankdb['startdate']<$bk_timelimit || $timestamp-$bankdb['dstartdate']<$bk_timelimit)) {
	Showmsg('bk_time_limit');
}

if ($_POST['action'] == 'save') {

	S::gp(array('savemoney','btype'),'P',2);
	if (!is_numeric($savemoney) || $savemoney <= 0) {
		Showmsg('bk_save_fillin_error');
	}
	/*
	$db->query("LOCK TABLES pw_memberdata WRITE,pw_memberinfo WRITE");//表锁
	$lockfile = D_P.'data/bbscache/lock_bank.txt';
	$fp = fopen($lockfile,'wb+');
	flock($fp,LOCK_EX);//文件锁
	*/
	if (procLock('bank_save',$winduid)) {

		if($savemoney > $credit->get($winduid,'money')) {
			procUnLock('bank_save',$winduid);
			Showmsg('bk_save_error');
		}
		if($btype != 1 && $btype != 2){
			procUnLock('bank_save',$winduid);
			Showmsg('undefined_action');
		}

		$credit->addLog('hack_banksave'.$btype,array('money' => -$savemoney),array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip
		));
		$credit->set($winduid,'money',-$savemoney);

		banksave($winduid,$savemoney,$bankdb,$btype);

		//fclose($fp);
		//$db->query("UNLOCK TABLES");

		require_once(R_P.'require/writelog.php');
		$log = array(
			'type'      => 'bk_save',
			'username1' => $windid,
			'username2' => '',
			'field1'    => $savemoney,
			'field2'    => $winduid,
			'field3'    => '',
			'descrip'   => 'bk_save_descrip_'.$btype,
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
		);
		writeforumlog($log);

		procUnLock('bank_save',$winduid);
		refreshto($basename, 'bank_savesuccess');

	} else {
		Showmsg('proclock');
	}

} elseif ($_POST['action'] == 'draw') {

	S::gp(array('drawmoney','btype'),'P',2);
	if (!is_numeric($drawmoney) || $drawmoney <= 0) {
		Showmsg('bk_draw_fillin_error');
	}
	$btype != 1 && $btype != 2 && Showmsg('undefined_action');

	/*
	$db->query("LOCK TABLES pw_memberdata WRITE,pw_memberinfo WRITE");
	$lockfile = D_P.'data/bbscache/lock_bank.txt';
	$fp = fopen($lockfile,'wb+');
	flock($fp,LOCK_EX);
	*/
	if (procLock('bank_draw',$winduid)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$bankdb = $userService->get($winduid, false, false, true);//deposit,startdate,ddeposit,dstartdate
		if ($btype == 1) {
			if ($drawmoney > $bankdb['deposit']){
				procUnLock('bank_draw',$winduid);
				Showmsg('bk_draw_error');
			}
		} else {
			if ($drawmoney > $bankdb['ddeposit']){
				procUnLock('bank_draw',$winduid);
				Showmsg('bk_draw_error');
			}
		}
		bankdraw($winduid,$drawmoney,$bankdb,$btype);

		$credit->addLog('hack_bankdraw'.$btype,array('money' => $drawmoney),array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip
		));
		$credit->set($winduid,'money',$drawmoney);

		//fclose($fp);
		//$db->query("UNLOCK TABLES");

		require_once(R_P.'require/writelog.php');
		$log = array(
			'type'      => 'bk_draw',
			'username1' => $windid,
			'username2' => '',
			'field1'    => $drawmoney,
			'field2'    => $winduid,
			'field3'    => '',
			'descrip'   => 'bk_draw_descrip_'.$btype,
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
		);
		writeforumlog($log);

		procUnLock('bank_draw',$winduid);
		refreshto($basename,'bank_drawsuccess');

	} else {
		Showmsg('proclock');
	}

} elseif ($_POST['action'] == 'virement') {

	if ($bk_virement != 1) {
		Showmsg('bk_virement_close');
	}
	S::gp(array('to_money','pwuser','memo'));
	$to_money = (int)$to_money;
	if (!is_numeric($to_money) || $to_money <= 0 || $to_money < $bk_virelimit) {
		Showmsg('bk_virement_count_error');
	}
	$memo = S::escapeChar($memo);
	strlen($memo) > 255 && $memo = substrs($memo,255);
	$pwuser = trim($pwuser);
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userdb = $userService->getByUserName($pwuser);//uid,username
	if (!$pwuser || !$userdb) {
		$errorname = S::escapeChar($pwuser);
		Showmsg('user_not_exists');
	}
	if ($userdb['uid'] == $winduid) {
		Showmsg('bk_virement_error');
	}
	$to_money	= floor($to_money);
	$to_shouxu	= round($bk_virerate*$to_money/100);
	$needmoney	= $to_money+$to_shouxu;

	/*
	$db->query("LOCK TABLES pw_memberdata WRITE,pw_memberinfo WRITE");
	$lockfile = D_P.'data/bbscache/lock_bank.txt';
	$fp = fopen($lockfile,'wb+');
	flock($fp,LOCK_EX);
	*/

	//if (procLock('bank_virement',$winduid)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$bankdb = $userService->get($winduid, false, false, true);//deposit,startdate,ddeposit,dstartdate
		
		if ($needmoney > $bankdb['deposit'] + $bankdb['ddeposit']) {
			Showmsg('bk_no_enough_deposit');
		}
		$to_bankdb = $userService->get($userdb['uid'], false, false, true);//deposit,startdate
		if ($needmoney <= $bankdb['deposit']) {
			bankdraw($winduid,$needmoney,$bankdb,1);
		} else {
			bankdraw($winduid,$bankdb['deposit'],$bankdb,1);
			bankdraw($winduid,$needmoney-$bankdb['deposit'],$bankdb,2);
		}
		banksave($userdb['uid'],$to_money,$to_bankdb,1);

		//fclose($fp);
		//$db->query("UNLOCK TABLES");

		M::sendNotice(
			array($pwuser),
			array(
				'title' => getLangInfo('writemsg','virement_title'),
				'content' => getLangInfo('writemsg','virement_content',array(
					'windid'	=> $windid,
					'to_money'	=> $to_money,
					'memo'		=> stripslashes($memo)
				)),
			)
		);

		require_once(R_P.'require/writelog.php');
		$log = array(
			'type'      => 'bk_vire',
			'username1' => $windid,
			'username2' => $pwuser,
			'field1'    => $to_money,
			'field2'    => $winduid,
			'field3'    => $userdb['uid'],
			'descrip'   => 'bk_vire_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
		);
		writeforumlog($log);

		procUnLock('bank_virement',$winduid);
		refreshto($basename,'bank_viresuccess');

	//} else {
	//	Showmsg('proclock');
	//}

} elseif ($action == 'log') {

	require_once GetLang('logtype');
	S::gp(array('type','page','to'));
	$sqladd = '';
	$select = array();
	if ($type && in_array($type,array('bk_save','bk_draw','bk_vire'))) {
		$sqladd = " AND type=".S::sqlEscape($type);
		$select[$type] = "selected";
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$sqlfiled = $to ? 'username2' : 'username1';

	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE type LIKE 'bk\_%' AND $sqlfiled=".S::sqlEscape($windid).$sqladd);
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&type=$type&to=$to&");
	$query = $db->query("SELECT * FROM pw_forumlog WHERE type LIKE 'bk\_%' AND $sqlfiled=".S::sqlEscape($windid).$sqladd." ORDER BY id DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['date']   = get_date($rt['timestamp']);
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$to && $rt['ip'] = $_G['viewipfrom'] ? $rt['ip'] : '保密';
		$logdb[] = $rt;
	}
	require_once PrintHack('index');footer();
}

function banksave($uid,$money,$bankdb,$type) {
	global $db,$timestamp,$bk_rate,$bk_ddate,$bk_drate,$credit;
	$money = intval($money);
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

	if ($type == 1) {
		if ($bankdb['startdate'] && $timestamp>$bankdb['startdate']) {
			$accrual = round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);
			//银行利息
		} else {
			$accrual = 0;
		}
		if ($bankdb) {
			$userService->update($uid, array(), array(), array('startdate'=>$timestamp));
			$userService->updateByIncrement($uid, array(), array(), array('deposit'=>($money+$accrual)));
		} else {
			$userService->update($uid, array(), array(), array('deposit'=>$money,'startdate'=>$timestamp));
		}
	} else {
		$ddates = floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
		if ($bankdb['dstartdate'] && $ddates) {
			$daccrual = round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
		} elseif ($bankdb['dstartdate'] && !$ddates) {
			$daccrual = round((floor(($timestamp-$bankdb['dstartdate'])/86400))*$bankdb['ddeposit']*$bk_rate/100);
		} else {
			$daccrual = 0;
		}
		
		if ($bankdb) {
			$userService->update($uid, array(), array(), array('dstartdate'=>$timestamp));
			$userService->updateByIncrement($uid, array(), array(), array('ddeposit'=>($money+$daccrual)));
		} else {
			$userService->update($uid, array(), array(), array('ddeposit'=>$money,'dstartdate'=>$timestamp));
		}
	}
}

function bankdraw($uid,$money,$bankdb,$type) {
	global $db,$timestamp,$bk_rate,$bk_ddate,$bk_drate;
	$money = intval($money);
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	
	if ($type == 1) {
		if ($bankdb['startdate'] && $timestamp>$bankdb['startdate']) {
			$accrual = round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);
		} else {
			$accrual = 0;
		}
		
		$userService->update($uid, array(), array(), array('startdate'=>$timestamp));
		$userService->updateByIncrement($uid, array(), array(), array('deposit'=>($accrual-$money)));
	} else {
		$ddates = floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
		if ($bankdb['dstartdate'] && $ddates) {
			$daccrual = round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
		} else {
			$daccrual = 0;
		}
		
		$userService->update($uid, array(), array(), array('dstartdate'=>$timestamp));
		$userService->updateByIncrement($uid, array(), array(), array('ddeposit'=>($daccrual-$money)));
	}
}

function savearray($name,$array) {
	$arraydb="\$$name=array(\r\n\t\t";
	foreach ($array as $value1) {
		$arraydb .= 'array(';
		foreach ($value1 as $value2) {
			$arraydb .= '"'.addslashes($value2).'",';
		}
		$arraydb .= "),\r\n\t\t";
	}
	$arraydb .= ");\r\n";
	return $arraydb;
}
?>
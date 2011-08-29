<?php
!function_exists('readover') && exit('Forbidden');

if (!$_POST['step']){

	include_once pwCache::getPath(D_P.'data/bbscache/dbreg.php');
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/credit.php');

	$customdata = $custominfo = $sexselect = $yearslect = $monthslect = $dayslect = array();
	$ifpublic = $httpurl = $email_Y = $email_N = $prosign_Y = $prosign_N = '';
	$ifsign = false;
	getstatus($userdb['userstatus'], PW_USERSTATUS_PUBLICMAIL) && $ifpublic = 'checked';
	${'email_' . (getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) ? 'Y' : 'N')} = 'checked';
	${'prosign_' . (getstatus($userdb['userstatus'], PW_USERSTATUS_SHOWSIGN) ? 'Y' : 'N')} = 'checked';
	$db_union[7] && list($customdata,$custominfo) = Getcustom($userdb['customdata']);

	$sexselect[(int)$userdb['gender']] = 'checked';
	$tradeinfo = unserialize($userdb['tradeinfo']);

	if ($userdb['timedf']) {
		$temptimedf = str_replace('.','_',abs($userdb['timedf']));
		$userdb['timedf'] < 0 ? ${'zone_0'.$temptimedf} = 'selected' : ${'zone_'.$temptimedf} = 'selected';
	}

	!$rg_timestart && $rg_timestart = 1960;
	!$rg_timeend && $rg_timeend = 2010;
	$getbirthday = explode('-',$userdb['bday']);
	$yearslect[(int)$getbirthday[0]] = $monthslect[(int)$getbirthday[1]] = $dayslect[(int)$getbirthday[2]] = 'selected';

	if ($userdb['signature'] || $userdb['introduce']) {
		$SCR = 'post';
	}
	require_once uTemplate::PrintEot('info_base');
	pwOutPut();

} elseif ($_POST['step'] == '2') {

	PostCheck();
	S::slashes($userdb);
	$upmembers = $upmemdata = $upmeminfo = array();
	//自我简介长度
	(strlen($_POST['prointroduce']) > 500) && Showmsg('introduce_limit');
	//签名难
	($_G['signnum'] && (strlen($_POST['prosign']) > $_G['signnum'])) && Showmsg('sign_limit');
	S::gp(array('profrom', 'prohomepage', 'prohonor', 'prointroduce', 'prosign', 'timedf', 'customdata', 'alipay'),'P');
	S::gp(array('newgroupid','progender', 'proyear', 'promonth', 'proday', 'showsign', 'proreceivemail'), 'P', 2);

//	strlen($prointroduce)>500 && Showmsg('introduce_limit');
	if ($_G['allowhonor']) {
		$prohonor = trim(substrs($prohonor,90));
		$upmembers['honor'] = $prohonor;
	}

	//支付宝帐号验证
	$trade['alipay'] = '';
	if ($alipay) {
		if (preg_match('/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$alipay) || preg_match('/^[\d]{11}/',$alipay)) {
			$trade['alipay'] = stripslashes($alipay);
		} else {
			Showmsg('alipay_error');
		}
	}

	require_once(R_P . 'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	foreach (array($prosign, $prointroduce, $prohonor) as $key => $value) {
		if (($banword = $wordsfb->comprise($value)) !== false) {
			Showmsg('sign_wordsfb');
		}
	}

	if ($db_union[7]) {
		list($customdata) = Getcustom($customdata,false,true);
		!empty($customdata) && $upmeminfo['customdata'] = addslashes(serialize($customdata));
	}
	//支付宝帐号
	$tradeinfo = $trade ? addslashes(serialize($trade)) : '';
	if ($tradeinfo <> $userdb['tradeinfo']) {
		$upmeminfo['tradeinfo'] = $tradeinfo;
	}
	//set signature state
	$showsign = $showsign ? 1 : 0;
	if ($db_signmoney && ($singstatus = getstatus($userdb['userstatus'], PW_USERSTATUS_SHOWSIGN)) != $showsign) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_SHOWSIGN, $showsign);
		if ($singstatus && $showsign == 0) {
			$upmemdata['starttime'] = 0;
		} else {
			require_once(R_P.'require/credit.php');
			if (($cur = $credit->get($winduid,$db_signcurtype)) === false) {
				Showmsg('numerics_checkfailed');
			}
			if ($cur < $db_signmoney) {
				Showmsg('noenough_currency');
			}
			$credit->addLog('main_showsign',array($db_signcurtype => -$db_signmoney),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip
			));
			if (!$credit->set($winduid,$db_signcurtype,-$db_signmoney,true)) {
				Showmsg('numerics_checkfailed');
			}
			$upmemdata['starttime'] = $tdtime;
		}
	}

	//update memdata
	if ($upmemdata) {
		$userService->update($winduid, array(), $upmemdata);
	}
	//update meminfo
	if ($upmeminfo) {
		$userService->update($winduid, array(), array(), $upmeminfo);
	}
	
	unset($upmemdata,$upmeminfo, $facetype, $customfield, $singstatus);

	$probday = '';
	if ($proyear || $promonth || $proday) {
		$probday = $proyear.'-'.$promonth.'-'.$proday;
	}
	$cksign = convert($prosign,$db_windpic,2);
	$signstatus = $cksign != $prosign ? 1 : 0;

	if ($signstatus != getstatus($userdb['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_SIGNCHANGE, $signstatus);
	}

	if ($proreceivemail != getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL)) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_RECEIVEMAIL, $proreceivemail);
	}
	$pwSQL = array_merge($upmembers, array(
		'gender' => $progender, 'signature' => $prosign, 'introduce' => $prointroduce, 
		'site' => $prohomepage, 'location' => $profrom, 'bday' => $probday, 'timedf' => $timedf
	));
	$userService->update($winduid, $pwSQL);

	/* phpwind数据统计 */
	if ($progender != $userdb['gender'] || $probday != $userdb['bday']) {
		$statistics = L::loadClass('Statistics', 'datanalyse');
		$statistics->alertSexDistribution($userdb['gender'], $progender);
		$statistics->alertAgeDistribution(intval($userdb['bday']), intval($probday));
	}
	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	initJob($winduid,"doUpdatedata");
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}

function Getcustom($data,$unserialize=true,$strips=null){
	global $db_union;
	$customdata = array();
	if (!$data || ($unserialize ? !is_array($data=unserialize($data)) : !is_array($data))) {
		$data = array();
	} elseif (!is_array($custominfo = unserialize($db_union[7]))) {
		$custominfo = array();
	}
	if (!empty($data) && !empty($custominfo)) {
		foreach ($data as $key => $value) {
			if (!empty($strips)) {
				$customdata[stripslashes(S::escapeChar($key))] = stripslashes(S::escapeChar($value));
			} elseif ($custominfo[$key] && $value) {
				$customdata[$key] = $value;
			}
		}
	}
	return array($customdata,$custominfo);
}
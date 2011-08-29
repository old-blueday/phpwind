<?php
!defined('R_P') && exit('Forbidden');
$db_menuinit .= ",'td_userinfomore' : 'menu_userinfomore'";
require_once(R_P.'require/credit.php');
$creditdb = array(
	'money'		=> $winddb['money'],
	'rvrc'		=> $userrvrc,
	'credit'	=> $winddb['credit'],
	'currency'	=> $winddb['currency']
);
$creditdb += $credit->get($winduid,'CUSTOM');
/*SEO*/
bbsSeoSettings();
S::gp(array('action'));
require_once(R_P . 'require/showimg.php');
list($faceurl) = showfacedesign($winddb['icon'], 1, 's');
$pro_tab = 'userpay';

if (empty($action)) {
	$orderdb = array();
	$query = $db->query("SELECT * FROM pw_clientorder WHERE uid=" . S::sqlEscape($winduid) . " ORDER BY date DESC LIMIT 5");
	while ($rt = $db->fetch_array($query)) {
		$rt['date'] = get_date($rt['date']);
		$orderdb[] = $rt;
	}

	require_once GetLang('logtype');
	$query = $db->query("SELECT * FROM pw_creditlog WHERE uid=". S::sqlEscape($winduid) . " ORDER BY id DESC LIMIT 5");
	while ($rt = $db->fetch_array($query)) {
		$rt['adddate'] = get_date($rt['adddate']);
		$rt['descrip'] = descriplog($rt['descrip']);
		$logdb[] = $rt;
	}

	!$db_creditpay && $db_creditpay = array();
	$paycredit = key($db_creditpay);
	$pay_link = "<span class=\"btn\"><span><button onClick=\"location.href='userpay.php?action=buy';\">马上充值</button></span></span>";
	//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
	pwCache::getData(D_P.'data/bbscache/ol_config.php');
	if (!$ol_onlinepay || empty($db_creditpay)) {
		$pay_link = "<div class=\"blockquote3\">支付功能尚未开启</div>";
	}
	if (!$ol_payto && (!$ol_paypal || !$ol_paypalcode) && (!$ol_99bill || !$ol_99billcode) && (!$ol_tenpay || !$ol_tenpaycode)) {
		$pay_link = "<div class=\"blockquote3\">支付功能尚未开启</div>";
	}
	require_once uTemplate::PrintEot('userpay');
	pwOutPut();

} elseif ($action == 'buy') {

	S::gp(array('paycredit'));
	//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
	pwCache::getData(D_P.'data/bbscache/ol_config.php');
	if (!$ol_onlinepay) {
		Showmsg($ol_whycolse);
	}
	if (!$ol_payto && (!$ol_paypal || !$ol_paypalcode) && (!$ol_99bill || !$ol_99billcode) && (!$ol_tenpay || !$ol_tenpaycode)) {
		Showmsg('olpay_seterror');
	}
	if (empty($db_creditpay)) {
		Showmsg('creditpay_empty');
	}
	$creditinfo = '';
	foreach ($db_creditpay as $key => $value) {
		$creditinfo .= "'$key' : ['$value[rmbrate]','$value[rmblest]','{$credit->cType[$key]}'],";
	}
	$creditinfo = '{'.rtrim($creditinfo,',').'}';

	require_once uTemplate::PrintEot('userpay');
	pwOutPut();

} elseif ($action == 'pay') {

	//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
	pwCache::getData(D_P.'data/bbscache/ol_config.php');
	if (!$ol_onlinepay) {
		Showmsg($ol_whycolse);
	}
	S::gp(array('number','method','paycredit'));

	if (!isset($db_creditpay[$paycredit])) {
		Showmsg('olpay_errortype');
	}
	$number = round($number,2);
	$paynum	= max(0, $db_creditpay[$paycredit]['rmblest']);
	if ($number < $paynum) {
		Showmsg('olpay_numerror');
	}
	$creditName = $credit->cType[$paycredit];

	$order_no = ($method-1).str_pad($winduid,10, "0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);
	$db->update("INSERT INTO pw_clientorder SET " . S::sqlSingle(array(
		'order_no'	=> $order_no,
		'type'		=> 0,
		'uid'		=> $winduid,
		'paycredit'	=> $paycredit,
		'price'		=> $number,
		'number'	=> 1,
		'date'		=> $timestamp,
		'state'		=> 0
	)));

	if ($method == 1) {

		if (!$ol_paypal || !$ol_paypalcode) {
			Showmsg('olpay_paypalerror');
		}
		$url  = "https://www.paypal.com/cgi-bin/webscr?";
		$para = array(
			'cmd'			=> '_xclick',
			'invoice'		=> $order_no,
			'business'		=> $ol_paypal,
			'item_name'		=> getLangInfo('olpay', "olpay_0_title", array('order_no' => $order_no)),
			'item_number'	=> 'phpw*',
			'amount'		=> $number,
			'no_shipping'	=> 0,
			'no_note'		=> 1,
			'currency_code'	=> 'CNY',
			'bn'			=> 'phpwind',
			'charset'		=> $db_charset
		);
		foreach ($para as $key => $value) {
			$url .= $key."=".urlencode($value)."&";
		}
		ObHeader($url);

	} elseif ($method == 2) {

		if (!$ol_payto) {
			Showmsg('olpay_alipayerror');
		}
		require_once(R_P.'require/onlinepay.php');
		$olpay = new OnlinePay($ol_payto);
		ObHeader($olpay->alipayurl($order_no, $number, 0));

	} elseif ($method == 3) {//fix by noizy

		if (!$ol_99bill || !$ol_99billcode) {
			Showmsg('olpay_pay99error');
		}
		strlen($ol_99bill) == 11 && $ol_99bill .= '01';
		//require_once(R_P.'require/header.php');
		!$db_rmbrate && $db_rmbrate=10;
		$para = array(
			'inputCharset'		=> ($db_charset == 'gbk' ? 2 : 1),
			'pageUrl'			=> "{$db_bbsurl}/pay99bill.php",
			'version'			=> 'v2.0',
			'language'			=> 1,
			'signType'			=> 1,
			'merchantAcctId'	=> $ol_99bill,
			'payerName'			=> $windid,
			'orderId'			=> $order_no,
			'orderAmount'		=> ($number*100),
			'orderTime'			=> get_date($timestamp,'YmdHis'),
			'productName'		=> getLangInfo('other','userpay_content'),
			'productNum'		=> ($number*$db_rmbrate),
			'payType'			=> '00',
			'redoFlag'			=> 1
		);
		$signMsg = $inputMsg = '';
		foreach ($para as $key => $value) {
			$value = trim($value);
			if (strlen($value) > 0) {
				$signMsg .= "$key=$value&";
				$inputMsg .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />";
			}
		}
		$signMsg = strtoupper(md5($signMsg."key=$ol_99billcode"));
		require_once uTemplate::PrintEot('userpay');
		pwOutPut();

	} elseif ($method == 4) {

		if (!$ol_tenpay || !$ol_tenpaycode) {
			Showmsg('olpay_tenpayerror');
		}
		$strBillDate = get_date($timestamp,'Ymd');
		$strSpBillNo = substr($order_no,-10);
		$strTransactionId = $ol_tenpay . $strBillDate . $strSpBillNo;
		$db->update("UPDATE pw_clientorder SET order_no=".S::sqlEscape($strTransactionId)."WHERE order_no=".S::sqlEscape($order_no));

//		$url  = "https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi?";
		$url  = "http://pay.phpwind.net/pay/create_payurl.php?";
		$para = array(
			'cmdno'				=> '1',
			'date'				=> $strBillDate,
			'bargainor_id'		=> $ol_tenpay,
			'transaction_id'	=> $strTransactionId,
			'sp_billno'			=> $strSpBillNo,
			'total_fee'			=> $number*100,
			'bank_type'			=> 0,
			'fee_type'			=> 1,
			'return_url'		=> "{$db_bbsurl}/tenpay.php",
			'attach'			=> 'my_magic_string',
		);
		$arg = '';
		foreach ($para as $key => $value) {
			if ($value) {
				$url .= "$key=".urlencode($value)."&";
				$arg .= "$key=$value&";
			}
		}
		$strSign = strtoupper(md5($arg."key=$ol_tenpaycode"));
		$url .= "desc=".urlencode(getLangInfo('olpay', "olpay_0_title", array('order_no' => $strTransactionId)))."&sign=$strSign";
		ObHeader($url);
	}
} elseif ($action == 'list') {

	S::gp(array('state'));
	$sqladd = "WHERE uid=" . S::sqlEscape($winduid) . ' AND type=0';
	if ($state == 1) {
		$sqladd .= " AND state<2";
	} elseif ($state == 2) {
		$sqladd .= " AND state=2";
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_clientorder $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"userpay.php?action=list&state=$state&");

	$query = $db->query("SELECT * FROM pw_clientorder $sqladd ORDER BY date DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		!$rt['paycredit'] && $rt['paycredit'] = 'currency';
		$rt['date'] = get_date($rt['date']);
		$orderdb[] = $rt;
	}
	//require_once(R_P.'require/header.php');
	require_once uTemplate::PrintEot('userpay');
	pwOutPut();

} elseif ($action == 'log') {

	S::gp(array('ctype','stime','etime','logtype','page'));
	$page = (int)$page;
	$sqladd = " uid=".S::sqlEscape($winduid);
	$urladd = '';
	if ($ctype) {
		$sqladd .= " AND ctype=".S::sqlEscape($ctype);
		$urladd .= "ctype=$ctype&";
	}
	if ($stime) {
		$stimeView = $stime;
		!is_numeric($stime) && $stime = PwStrtoTime($stime);
		$sqladd .= " AND adddate>".S::sqlEscape($stime);
		$urladd .= "stime=$stime&";
	}
	if ($etime) {
		$etimeView = $etime;
		!is_numeric($etime) && $etime = PwStrtoTime($etime);
		if ($etime == $stime) $etime = $etime + 86400;
		$sqladd .= " AND adddate<".S::sqlEscape($etime);
		$urladd .= "etime=$etime&";
	}
	if ($logtype) {
		$sqladd .= " AND logtype".(strpos($logtype,'_') !== false ? "=".S::sqlEscape($logtype) : " LIKE ".S::sqlEscape("$logtype%"));
		$urladd .= "logtype=$logtype&";
	}
	require_once(R_P.'require/forum.php');
	require_once GetLang('logtype');

	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt	= $db->get_one("SELECT COUNT(*) AS sum FROM pw_creditlog WHERE $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"userpay.php?action=log&$urladd");

	$query = $db->query("SELECT * FROM pw_creditlog WHERE $sqladd ORDER BY id DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['adddate'] = get_date($rt['adddate']);
		$rt['descrip'] = descriplog($rt['descrip']);
		$logdb[] = $rt;
	}
	//require_once(R_P.'require/header.php');
	require_once uTemplate::PrintEot('userpay');
	pwOutPut();

} elseif ($action == 'virement') {

	$vm_credit = array();
	foreach ($db_creditpay as $key => $value) {
		if ($value['virement']) {
			$vm_credit[] = $key;
		}
	}
	empty($vm_credit) && Showmsg('virement_closed');

	if (empty($_POST['step'])) {
			$db_virelimit = (int) $db_virelimit;
		//require_once(R_P.'require/header.php');
			require_once uTemplate::PrintEot('userpay');
			pwOutPut();

	} else {

		PostCheck();
		S::gp(array('pwuser','pwpwd','vmcredit','paynum'),'P');
		if (!in_array($vmcredit,$vm_credit)) {
			Showmsg('undefined_action');
		}

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$touid	= $userService->getUserIdByUserName($pwuser);
		if (!$touid) {
			$errorname = $pwuser;
			Showmsg('user_not_exists');
		}
		$paynum = (int)$paynum;
		if ($paynum <= 0) {
			Showmsg('illegal_nums');
		}
		if (!$pwpwd) {
			Showmsg('empty_password');
		}
		if ($db_virelimit && $paynum < $db_virelimit) {
			Showmsg('currency_limit');
		}
		/*
		$lockfile = D_P.'data/bbscache/lock_userpay.txt';
		$fp = fopen($lockfile,'wb+');
		flock($fp,LOCK_EX);
		*/
		$rt = $userService->get($winduid);
		if (md5($pwpwd) != $rt['password']) {
			Showmsg('password_error');
		}
		if (procLock('userpay',$winduid)) {

			$tax = round($paynum * $db_virerate/100);
			$needpay = $paynum + $tax;
			if ($credit->get($winduid,$vmcredit) < $needpay) {
				procUnLock('userpay',$winduid);
				Showmsg('noenough_currency');
			}
			$credit->addLog('main_virefrom',array($vmcredit => -$needpay),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip,
				'toname'	=> stripslashes($pwuser)
			));
			$credit->addLog('main_vireto',array($vmcredit => $paynum),array(
				'uid'		=> $touid,
				'username'	=> stripslashes($pwuser),
				'ip'		=> $onlineip,
				'fromname'	=> $windid
			));
			$credit->set($winduid,$vmcredit,-$needpay,false);
			$credit->set($touid,$vmcredit,$paynum,false);
			$credit->runsql();

			//fclose($fp);

			M::sendNotice(
				array($pwuser),
				array(
					'title' => getLangInfo('writemsg','vire_title'),
					'content' => getLangInfo('writemsg','vire_content',array(
						'windid'	=> $windid,
						'paynum'	=> $paynum,
						'cname'		=> $credit->cType[$vmcredit]
					)),
				)
			);

			procUnLock('userpay',$winduid);
			refreshto('userpay.php?action=virement',getLangInfo('msg','virement_success'));
		} else {
			Showmsg('virement_lock');
		}
	}
} elseif ($action == 'change') {

	require_once(R_P.'require/credit.php');
	$rt = $db->get_one("SELECT db_value FROM pw_config WHERE db_name='jf_A'");
	$jf_A = $rt['db_value'] ? unserialize($rt['db_value']) : array();

	if (empty($_POST['step'])) {

		$creditdb = $credit->get($winduid,'CUSTOM');
		$jf = array();
		foreach ($jf_A as $key => $value) {
			if ($value[2]) {
				list($j_1,$j_2) = explode('_',$key);
				$jf[$key] = array($credit->cType[$j_1],$credit->cType[$j_2],$value[0],$value[1]);
			}
		}
		!$jf && Showmsg('jfchange_empty');

		require_once uTemplate::PrintEot('userpay');
		pwOutPut();

	} else {
		PostCheck();
		S::gp(array('type','change'));
		if (!$jf_A[$type] || !$jf_A[$type][2]) {
			Showmsg('bk_credit_type_error');
		}
		$change = (int)$change;
		if (!is_numeric($change) || $change <= 0) Showmsg('bk_credit_fillin_error');
		$change%$jf_A[$type][0] != 0 && Showmsg('change_error');

		list($sell,$buy) = explode('_',$type);
		$credit1 = $change;
		$credit2 = intval($change/$jf_A[$type][0]*$jf_A[$type][1]);
		/*
		$db->query("LOCK TABLES pw_memberdata WRITE,pw_membercredit WRITE");
		$lockfile = D_P.'data/bbscache/lock_profile.txt';
		$fp = fopen($lockfile,'wb+');
		flock($fp,LOCK_EX);
		*/
		if (procLock('credit_change',$winduid)) {

			if ($credit1 > $credit->get($winduid,$sell)) {
				procUnLock('credit_change',$winduid);
				Showmsg('bk_credit_change_error');
			}
			$credit->addLog('main_changereduce',array($sell => -$credit1),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip,
				'tocname'	=> $credit->cType[$buy]
			));
			$credit->addLog('main_changeadd',array($buy => $credit2),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip,
				'fromcname'	=> $credit->cType[$sell]
			));
			$credit->sets($winduid,array($sell => -$credit1, $buy => $credit2));

			procUnLock('credit_change',$winduid);
			//fclose($fp);
			//$db->query("UNLOCK TABLES");
		}
		refreshto('userpay.php?action=change','bank_creditsuccess',1,true);
	}
}
?>
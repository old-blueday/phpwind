<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('adminitem'));
$basename = "$admin_file?adminjob=userpay&adminitem=$adminitem";
empty($adminitem) && $adminitem = 'userpay';
if ($adminitem == 'userpay'){
	if (!$_POST['action']) {
		//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
		pwCache::getData(D_P.'data/bbscache/ol_config.php');
		!$ol_paypalcode && $ol_paypalcode=RandString('40');
		ifcheck($ol_onlinepay,'onlinepay');
		include PrintEot('userpay');exit;
	} else {
		S::gp(array('userpay'),'P');
		!$userpay['ol_paypalcode'] && $userpay['ol_paypalcode'] = RandString('40');
		foreach ($userpay as $key => $value) {
			setConfig($key, $value);
		}
		updatecache_ol();
		adminmsg('operate_success');
	}
}elseif ($adminitem =='orderlist'){
	if (!$action) {
		S::gp(array('state','page'),'GP',2);
		if ($state == 1) {
			$sqladd = "WHERE state<2";
		} elseif ($state == 2) {
			$sqladd = "WHERE state=2";
		} else {
			$sqladd = '';
		}
		require_once(R_P.'require/credit.php');
		$page < 1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_clientorder $sqladd");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&state=$state&");
		$orderdb = array();
		$query = $db->query("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) $sqladd ORDER BY id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['fee']  = $rt['price'] * $rt['number'];
			$rt['date'] = get_date($rt['date']);
			$orderdb[]  = $rt;
		}
		include PrintEot('userpay');exit;
	} elseif ($action == 'pay') {
		S::gp(array('id','order'));
		$sql = '';
		if ($id) {
			$sql = 'WHERE id=' . S::sqlEscape($id);
		} elseif ($order) {
			$sql = 'WHERE order_no=' . S::sqlEscape($order);
		}
		!$sql && adminmsg('orderlist_none');
		$rt = $db->get_one("SELECT c.*,m.username,m.groupid,m.groups FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) $sql");
		empty($rt) && adminmsg('orderlist_none');
		if (empty($_POST['step'])) {
			$rt['fee']	= $rt['price'] * $rt['number'];
			$rt['date'] = get_date($rt['date']);
			$detail = '';
			switch ($rt['type']) {
				case 0:
					$rmbrate = $db_creditpay[$rt['paycredit']]['rmbrate'];
					!$rmbrate && $rmbrate = 10;
					$currency = round($rt['price'] * $rmbrate);
					$detail = getLangInfo('other','onlinepay_credit',array(
						'cname'		=> pwCreditNames($rt['paycredit']),
						'number'	=> $currency
					));
					break;
				case 1:
					$tool = $db->get_one("SELECT name FROM pw_tools WHERE id=" . S::sqlEscape($rt['paycredit']));
					$detail = getLangInfo('other','onlinepay_tool',array(
						'toolname'	=> $tool['name'],
						'number'	=> $rt['number']
					));
					break;
				case 2:
					$detail = getLangInfo('other','onlinepay_forum',array(
						'fname'		=> $forum[$rt['paycredit']]['name'],
						'number'	=> $rt['extra_1']
					));
					break;
				case 3:
					$detail = getLangInfo('other','onlinepay_group',array(
						'gname'		=> $ltitle[$rt['paycredit']],
						'number'	=> $rt['number']
					));
					break;
				case 4:
					$detail = getLangInfo('other','onlinepay_invite',array(
						'number'	=> $rt['number']
					));
			}
			include PrintEot('userpay');exit;
		} else {
			S::gp(array('payemail'));
			if (!$payemail) {
				$basename = "javascript:history.go(-1);";
				adminmsg('no_payemail');
			}
			if ($rt['state'] == 2) {
				adminmsg('undefined_action');
			}
			if (file_exists(R_P."require/olpay/pay_{$rt[type]}.php")) {
				require_once S::escapePath(R_P."require/olpay/pay_{$rt[type]}.php");
			}
			$db->update("UPDATE pw_clientorder SET payemail=" . S::sqlEscape($payemail) . ',state=2 WHERE id=' . S::sqlEscape($rt['id']));
	
			adminmsg('operate_success');
		}
	} elseif ($_POST['action'] == 'del') {
		S::gp(array('selid'),'P');
		if (!$selid = checkselid($selid)) {
			$basename = "javascript:history.go(-1);";
			adminmsg('operate_error');
		}
		$db->update("DELETE FROM pw_clientorder WHERE id IN($selid)");
		adminmsg('operate_success');
	}
}

function RandString($len){
	$rand='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$len;$i++){
		$code.=$rand[mt_rand(0,strlen($rand))];
	}
	return $code;
}
?>
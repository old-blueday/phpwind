<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=orderlist";

if (!$action) {

	InitGP(array('state','page'),'GP',2);
	if ($state == 1) {
		$sqladd = "WHERE state<2";
	} elseif ($state == 2) {
		$sqladd = "WHERE state=2";
	} else {
		$sqladd = '';
	}
	require_once(R_P.'require/credit.php');

	$page < 1 && $page = 1;
	$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_clientorder $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&state=$state&");

	$orderdb = array();
	$query = $db->query("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) $sqladd ORDER BY id DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['fee']  = $rt['price'] * $rt['number'];
		$rt['date'] = get_date($rt['date']);
		$orderdb[]  = $rt;
	}
	include PrintEot('orderlist');exit;

} elseif ($action == 'pay') {

	InitGP(array('id','order'));
	$sql = '';
	if ($id) {
		$sql = 'WHERE id=' . pwEscape($id);
	} elseif ($order) {
		$sql = 'WHERE order_no=' . pwEscape($order);
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
				$tool = $db->get_one("SELECT name FROM pw_tools WHERE id=" . pwEscape($rt['paycredit']));
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
		include PrintEot('orderlist');exit;

	} else {
		
		InitGP(array('payemail'));
		if (!$payemail) {
			$basename = "javascript:history.go(-1);";
			adminmsg('no_payemail');
		}
		if ($rt['state'] == 2) {
			adminmsg('undefined_action');
		}
		if (file_exists(R_P."require/olpay/pay_{$rt[type]}.php")) {
			require_once Pcv(R_P."require/olpay/pay_{$rt[type]}.php");
		}
		$db->update("UPDATE pw_clientorder SET payemail=" . pwEscape($payemail) . ',state=2 WHERE id=' . pwEscape($rt['id']));

		adminmsg('operate_success');
	}
} elseif ($_POST['action'] == 'del') {

	InitGP(array('selid'),'P');
	if (!$selid = checkselid($selid)) {
		$basename = "javascript:history.go(-1);";
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_clientorder WHERE id IN($selid)");
	adminmsg('operate_success');
}
?>
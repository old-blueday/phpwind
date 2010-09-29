<?php
require_once('global.php');
include_once(D_P.'data/bbscache/ol_config.php');
if(!$ol_onlinepay){
	Showmsg($ol_whycolse);
}
if (!$ol_paypal || !$ol_paypalcode) {
	Showmsg('olpay_seterror');
}
if ($_GET['verifycode'] != $ol_paypalcode) {

	Showmsg('undefined_action');

} elseif (GetGP('payment_status') == 'Completed') {

	InitGP(array('invoice','mc_gross'));
	$rt = $db->get_one("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE order_no=".pwEscape($invoice));
	if ($rt['state'] == '0') {
		if ($rt['number'] != $mc_gross) {
			Showmsg('gross_error');
		}
		$rmbrate = $db_creditpay[$rt['paycredit']]['rmbrate'];
		!$rmbrate && $rmbrate = 10;
		$currency = $rt['number'] * $rmbrate;

		require_once(R_P.'require/credit.php');
		$credit->addLog('main_olpay',array($rt['paycredit'] => $currency),array(
			'uid'		=> $rt['uid'],
			'username'	=> $rt['username'],
			'ip'		=> $onlineip,
			'number'	=> $rt['number']
		));
		$credit->set($rt['uid'],$rt['paycredit'],$currency);

		$descrip = getLangInfo('other','paypal_orders');
		$db->update("UPDATE pw_clientorder SET state=2,descrip=".pwEscape($descrip,false)."WHERE order_no=".pwEscape($invoice));
		
		M::sendNotice(
			array($rt['username']),
			array(
				'title' => getLangInfo('writemsg','olpay_title'),
				'content' => getLangInfo('writemsg','olpay_content_2',array(
					'currency'	=> $currency,
					'cname'		=> $credit->cType[$rt['paycredit']],
					'number'	=> $rt['number']
				)),
			)
		);

		require_once(R_P.'require/posthost.php');
		$getdb = '';
		foreach ($_POST as $key => $value) {
			$getdb .= $key."=".urlencode($value)."&";
		}
		$getdb .= 'date='.get_date($timestamp,'Y-m-d-H:i:s');
		$getdb .= '&site='.$pwServer['HTTP_HOST'];
		PostHost("http://pay.phpwind.net/pay/stats.php",$getdb,'POST');exit;
	} else {
		Showmsg('undefined_action');
	}
}
?>
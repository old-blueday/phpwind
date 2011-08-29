<?PHP
/**
 * Copyright (c) 2003-2103  phpwind.net. All rights reserved.
 *
 * @filename: pay99bill.php
 * @author: Noizy
 * @modify: Thu Jan 31 16:42:58 CST 2008
 */
require_once('global.php');

//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
pwCache::getData(D_P.'data/bbscache/ol_config.php');
!$ol_onlinepay && Showmsg($ol_whycolse);
if (!$ol_99bill || !$ol_99billcode) {
	Showmsg('olpay_seterror');
}
strlen($ol_99bill)==11 && $ol_99bill .= '01';
if (trim(S::getGP('merchantAcctId'))!=$ol_99bill) {
	Showmsg('olpay_seterror');
}
$para = array('payType','bankId','orderId','orderTime','orderAmount','dealId','bankDealId','dealTime', 'payAmount','fee','payResult','errCode');

S::gp($para);
$cksignMsg = "merchantAcctId=$ol_99bill&version=v2.0&language=1&signType=1";
foreach ($para as $value) {
	$postvalue = trim(${$value});
	if (strlen($postvalue)>0) {
		$cksignMsg .= "&$value=$postvalue";
	}
}
if (strtoupper(md5($cksignMsg."&key=$ol_99billcode"))!=strtoupper(trim(S::getGP('signMsg')))) {
	Showmsg('olpay_seterror');
}
require_once(R_P.'require/header.php');

if ($payResult == '10') {
	$rt = $db->get_one("SELECT c.uid,c.paycredit,c.number,c.state,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE c.order_no=".S::sqlEscape($orderId));
	$rt['state'] && refreshto('userpay.php','complete_list');
	$number = $payAmount/100;
	$rt['number'] != $number && Showmsg('gross_error');
	$rmbrate = $db_creditpay[$rt['paycredit']]['rmbrate'];
	!$rmbrate && $rmbrate = 10;
	$currency = $number * $rmbrate;

	require_once(R_P.'require/credit.php');
	$credit->addLog('main_olpay',array($rt['paycredit'] => $currency),array(
		'uid'		=> $rt['uid'],
		'username'	=> $rt['username'],
		'ip'		=> $onlineip,
		'number'	=> $rt['number']
	));
	$credit->set($rt['uid'],$rt['paycredit'],$currency);

	$descrip = getLangInfo('other','succeed_order');
	$db->update("UPDATE pw_clientorder SET state=2,descrip=".S::sqlEscape($descrip,false)." WHERE order_no=".S::sqlEscape($orderId));
	
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
	$cksignMsg = explode('&',$cksignMsg);
	foreach ($cksignMsg as $key => $value) {
		$cksignMsg[$key] = urlencode($value);
	}
	$cksignMsg['date'] = get_date($timestamp,'Y-m-d-H:i:s');
	$cksignMsg['site'] = $pwServer['HTTP_HOST'];
	$cksignMsg = implode('&',$cksignMsg);
	PostHost("http://pay.phpwind.net/pay/stats.php",$cksignMsg,'POST');
}
require_once PrintEot('pay99bill');footer();
?>
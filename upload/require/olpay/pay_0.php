<?php
!function_exists('readover') && exit('Forbidden');

$rmbrate = $db_creditpay[$rt['paycredit']]['rmbrate'];
!$rmbrate && $rmbrate = 10;
$currency = round($rt['price'] * $rmbrate);

require_once(R_P.'require/credit.php');
$credit->addLog('main_olpay',array($rt['paycredit'] => $currency),array(
	'uid'		=> $rt['uid'],
	'username'	=> $rt['username'],
	'ip'		=> $onlineip,
	'number'	=> $rt['price']
));
$credit->set($rt['uid'],$rt['paycredit'],$currency);

M::sendNotice(
	array($rt['username']),
	array(
		'title' => getLangInfo('writemsg','olpay_title'),
		'content' => getLangInfo('writemsg','olpay_content_2',array(
			'currency'	=> $currency,
			'cname'		=> $credit->cType[$rt['paycredit']],
			'number'	=> $rt['price']
		)),
	)
);
?>
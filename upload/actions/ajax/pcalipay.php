<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'pcmid',
	'tid',
	'pcid'
), GP, 2);
if (empty($_POST['step'])) {
	$pcvaluetable = GetPcatetable($pcid);
	
	$fieldvalue = $db->get_one("SELECT pv.endtime,pv.price,pv.deposit,pv.payway,pm.nums,pm.phone,pm.mobile,pm.address,pm.extra,pm.ifpay,pm.totalcash FROM $pcvaluetable pv LEFT JOIN pw_pcmember pm ON pv.tid=pm.tid WHERE pm.tid=" . pwEscape($tid) . " AND pm.pcmid=" . pwEscape($pcmid) . " AND pm.uid=" . pwEscape($winduid));
	
	!$fieldvalue && Showmsg('undefined_action');
	$fieldvalue['ifpay'] && Showmsg('pcjoin_payed');
	$fieldvalue['endtime'] < $timestamp && Showmsg('pcjoin_end');
	$nums = $fieldvalue['nums'];
	$deposit = !$fieldvalue['deposit'] ? $fieldvalue['price'] : $fieldvalue['deposit'];
	$totalcash = $fieldvalue['totalcash'];
	$alipayurl = "trade.php?action=pcalipay&tid=$tid&pcmid=$pcmid&pcid=$pcid";
}

require_once PrintEot('ajax');
ajax_footer();

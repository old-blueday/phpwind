<?php
define('SCR','act_alipay_push');
require_once('global.php');

S::gp(array('action'));
require_once(R_P.'lib/activity/alipay.php');

$service = $action;
$AlipayInterface = new AlipayInterface($service);

if ($action == 'user_authentication') {//身份验证
	$param = array(
		/* 业务参数 */
		'return_url'		=> "{$db_bbsurl}/act_alipay_receive.php?action=$action",
	);
	ObHeader($AlipayInterface->alipayurl($param));
} elseif ($action == 'confirm_aa_detail_payment') {//订单支付

	S::gp(array('actuid','tid','fromuid','actmid'),GP,2);
	
	$memberdb = $db->get_one("SELECT am.uid,am.username,am.ifpay,am.isrefund,am.out_trade_no,am.totalcash,am.ifanonymous,t.authorid FROM pw_activitymembers am LEFT JOIN pw_threads t USING(tid) WHERE am.actuid=".S::sqlEscape($actuid));

	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();

	$memberdb['authorid'] == $winduid && Showmsg('act_toalipay_authorid');//发起人无法替别人支付
	$isAdminright = $postActForBbs->getAdminRight($memberdb['authorid']);
	if ($memberdb['isrefund'] || $memberdb['ifanonymous'] && !$isAdminright && $memberdb['uid'] != $winduid) {//退款的无法支付、匿名但没有权限的无法支付
		Showmsg('act_toalipay_error');
	}

	$memberdb['ifpay'] != 0 && Showmsg('act_toalipay_payed');//只有未支付状态才可以支付
	if (!$memberdb['totalcash'] || !preg_match("/^(([1-9]\d*)|0)(\.\d{0,2})?$/", $memberdb['totalcash'])) {//费用错误
		Showmsg('act_toalipay_cash_error');
	}
	$memberdb['totalcash'] = number_format($memberdb['totalcash'], 2, '.', '');//支付金额
	$out_trade_no = $memberdb['out_trade_no'] ? $memberdb['out_trade_no'] : $db_sitehash.'_'.$tid.'_'.$actuid.'_'.generatestr(6);

	$defaultValueTableName = getActivityValueTableNameByActmid();
	$defaultValue = $db->get_one("SELECT out_biz_no,paymethod,iscancel,endtime FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
	$defaultValue['paymethod'] != 1 && Showmsg('act_toalipay_paymethod');//只有支付方式为支付宝才可以支付
	$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作
	$defaultValue['iscancel'] == 1 && Showmsg('act_iscancelled_y');//活动被取消无法支付

	$param = array(
		/* 业务参数 */
		'buyer_name'	=> $memberdb['username'],
		'out_biz_no'	=> $defaultValue['out_biz_no'],
		'out_trade_no'	=> $out_trade_no,
		'amount'		=> $memberdb['totalcash'],
		'notify_url'	=> "{$db_bbsurl}/act_alipay_receive.php",
		'return_url'	=> "{$db_bbsurl}/read.php?tid=$tid",
	);
	
	if ($fromuid != '-1') {//是否代付
		$fromusername = $db->get_value("SELECT username FROM pw_members WHERE uid=".S::sqlEscape($fromuid));
		$issubstitute = 1;
	} else {
		$fromuid = $issubstitute = 0;
		$fromusername = '';
	}
	$sqlarray = array(
		'out_trade_no'	=> $out_trade_no,//外部订单交易号
		'issubstitute'	=> $issubstitute,//是否代付
		'fromuid'		=> $fromuid,//代付id
		'fromusername'	=> $fromusername,//代付用户名
	);

	$db->update("UPDATE pw_activitymembers SET " . S::sqlSingle($sqlarray)." WHERE actuid=".S::sqlEscape($actuid));
	ObHeader($AlipayInterface->alipayurl($param));
} elseif ($action == 'refund_aa_payment') {//退款
	S::gp(array('tid','actuid','actmid'),GP,2);

	$memberdb = $db->get_one("SELECT am.ifpay,am.isrefund,am.username,am.totalcash,am.out_trade_no,am.refundcost,t.authorid FROM pw_activitymembers am LEFT JOIN pw_threads t USING(tid) WHERE am.actuid=".S::sqlEscape($actuid));
	$tempcost = $db->get_value("SELECT SUM(totalcash) as sum FROM pw_activitymembers WHERE isrefund=1 AND fupid=".S::sqlEscape($actuid));//已退费用

	if ($memberdb['isrefund'] || $memberdb['authorid'] != $winduid) {//退款交易无法操作、不是发起人无法操作
		Showmsg('act_refund_noright');
	}

	$memberdb['ifpay'] != 1 && Showmsg('act_refund_error');//支付宝支付成功才能退款
	if (!$memberdb['refundcost'] || !preg_match("/^(([1-9]\d*)|0)(\.\d{0,2})?$/", $memberdb['refundcost']) || $memberdb['refundcost'] > number_format(($memberdb['totalcash'] - $tempcost), 2, '.', '')) {//费用错误、超出剩余费用
		Showmsg('act_refund_cash_error');
	}
	$refundcost = number_format($memberdb['refundcost'], 2, '.', '');//退款金额

	$defaultValueTableName = getActivityValueTableNameByActmid();
	$defaultValue = $db->get_one("SELECT user_id,paymethod,endtime FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
	$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作
	$defaultValue['paymethod'] != 1 && Showmsg('act_toalipay_paymethod');//支付宝支付才能退款

	$param = array(
		/* 业务参数 */
		'out_trade_no'	=> $memberdb['out_trade_no'],
		'operator_id'	=> $defaultValue['user_id'],
		'refund_fee'	=> $refundcost,
		'notify_url'	=> "{$db_bbsurl}/act_alipay_receive.php",
		'return_url'	=> "{$db_bbsurl}/read.php?tid=$tid",
	);
	ObHeader($AlipayInterface->alipayurl($param));
}

/**
 * 生成随机码
 * @param int $len 位数
 * @param string 随机串
 */
function generatestr($len) {
	mt_srand((double)microtime()*1000000);
	$keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str = '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.microtime().$GLOBALS['HTTP_HOST'].$GLOBALS['pwServer']["HTTP_USER_AGENT"].$GLOBALS['db_hash']),0,$len);
}
?>
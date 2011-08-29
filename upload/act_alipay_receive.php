<?php
require_once('global.php');
require_once(R_P.'require/posthost.php');

S::gp(array('action','sign'));

!empty($_POST) && $_GET = $_POST;
$url = '';
foreach ($_GET as $key => $value) {
	if ($value) {
		$url .= "$key=".urlencode($value)."&";
	}
}
$url .= "partner=".$db_sitehash."&bbsurl=".$db_bbsurl;
$veryfy_result = PostHost("http://pay.phpwind.net/pay/aa_notify.php",$url, 'POST');

if (!eregi("true$",$veryfy_result)) {
	paymsg('index.php','act_alipay_sign_failure','fail');
}

if ($action == 'user_authentication') {//用户验证
	S::gp(array('email','is_certified','is_success','user_id'));

	if ($is_success == 'T' && $email && $user_id) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$tradeinfo = $userService->get($winduid, false, false, true); //tradeinfo
		$tradeinfo = $tradeinfo['tradeinfo'] ? unserialize($tradeinfo['tradeinfo']) : array();
		$tradeinfo['alipay'] = $email;
		$tradeinfo['iscertified'] = $is_certified;
		$tradeinfo['isbinded'] = $is_success;
		$tradeinfo['user_id'] = $user_id;
		$tradeinfo = addslashes(serialize($tradeinfo));

		$userService->update($winduid, array(), array(), array('tradeinfo' => $tradeinfo));

		paymsg("profile.php?action=modify&info_type=base",'act_authentication_success');
	} else {
		paymsg("profile.php?action=modify&info_type=base",'act_authentication_fail');
	}
} elseif ($action == 'confirm_aa_detail_payment') {//订单支付
	S::gp(array('is_success','out_trade_no','batch_detail_no','trade_status'));

	$is_success != 'T' && paymsg("read.php?tid=$tid",'act_toalipay_failure');
	list(,$tid,$actuid) = explode("_",$out_trade_no);

	$memberdb = $db->get_one("SELECT am.ifpay,am.uid,am.username,am.isadditional,am.totalcash,am.fromuid,am.fromusername,am.isrefund,am.ifanonymous,t.subject,t.authorid,t.author FROM pw_activitymembers am LEFT JOIN pw_threads t ON am.tid=t.tid WHERE actuid=".S::sqlEscape($actuid));
	$memberdb['ifpay'] != 0 && paymsg("read.php?tid=$tid",'act_toalipay_payed');

	if ($memberdb['isrefund']) {//退款的无法支付、匿名但没有权限的无法支付
		paymsg("read.php?tid=$tid",'act_undefined_operate');
	}

	$defaultValueTableName = getActivityValueTableNameByActmid();
	$defaultValue = $db->get_one("SELECT paymethod FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
	$defaultValue['paymethod'] != 1 && paymsg("read.php?tid=$tid",'act_undefined_operate');//只有支付方式为支付宝才可以支付

	$payStatus = array(
		'I' => '0',//待付款
		'S' => '1',//已支付
		'C' => '3',//交易关闭
		'E' => '1',//交易成功
	);
	$db->update("UPDATE pw_activitymembers SET batch_detail_no=".S::sqlEscape($batch_detail_no).",ifpay=".S::sqlEscape($payStatus[$trade_status])." WHERE actuid=".S::sqlEscape($actuid));
	$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间

	/*支付成功费用流通日志*/
	$data = array();
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$statusValue = $postActForBbs->getActivityStatusValue($tid);
	$postActForBbs->UpdatePayLog($tid,$actuid,$statusValue);
	/*支付成功费用流通日志*/

	/*短消息通知 支付成功 发起人*/
	$msgContentText = $memberdb['isadditional'] ? 'activity_payed2_content' : 'activity_payed_content';	
	M::sendNotice(
		array($memberdb['author']),
		array(
			'title' => getLangInfo('writemsg', 'activity_payed_title', array(
						'uid'		=> $memberdb['uid'],
						'username'	=> $memberdb['username'],
						'tid'		=> $tid,
						'subject'	=> $memberdb['subject'],
						'totalcash'	=> $memberdb['totalcash']
					)
				),
			'content' => getLangInfo('writemsg', $msgContentText, array(
						'uid'		=> $memberdb['uid'],
						'username'	=> $memberdb['username'],
						'tid'		=> $tid,
						'subject'	=> $memberdb['subject'],
						'totalcash'	=> $memberdb['totalcash']
					)
				)
		),'notice_active', 'notice_active'
	);	

	if ($memberdb['fromuid']) {
		/*短消息通知 支付成功 代付人*/
		$frommsg = $memberdb['isadditional'] ? 'activity_payed2_from_content' : 'activity_payed_from_content';
		M::sendNotice(
			array($memberdb['fromusername']),
			array(
				'title' => getLangInfo('writemsg', 'activity_payed_from_title', array(
							'uid'		=> $memberdb['uid'],
							'username'	=> $memberdb['username'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					),
				'content' => getLangInfo('writemsg', $frommsg, array(
							'uid'		=> $memberdb['uid'],
							'username'	=> $memberdb['username'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					)
			),'notice_active', 'notice_active'
		);	
	
		
		/*短消息通知 支付成功 被代付人 参与人*/
		$signupermsg = $memberdb['isadditional'] ? 'activity_payed2_signuper_from_content' : 'activity_payed_signuper_from_content';
		M::sendNotice(
			array($memberdb['username']),
			array(
				'title' => getLangInfo('writemsg', 'activity_payed_signuper_from_title', array(
							'uid'		=> $memberdb['fromuid'],
							'username'	=> $memberdb['fromusername'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					),
				'content' => getLangInfo('writemsg', $signupermsg, array(
							'uid'		=> $memberdb['fromuid'],
							'username'	=> $memberdb['fromusername'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					)
			),'notice_active', 'notice_active'
		);
		
	} else {
		/*短消息通知 支付成功 自己支付 参与人*/
		$signupermsg = $memberdb['isadditional'] ? 'activity_payed2_signuper_content' : 'activity_payed_signuper_content';
		M::sendNotice(
			array($memberdb['username']),
			array(
				'title' => getLangInfo('writemsg', 'activity_payed_signuper_title', array(
							'uid'		=> $memberdb['authorid'],
							'username'	=> $memberdb['author'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					),
				'content' => getLangInfo('writemsg', $signupermsg, array(
							'uid'		=> $memberdb['fromuid'],
							'username'	=> $memberdb['fromusername'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $memberdb['totalcash']
						)
					)
			),'notice_active', 'notice_active'
		);
	}
	paymsg("read.php?tid=$tid",'act_aa_detail_success');
} elseif ($action == 'refund_aa_payment') {//退款
	S::gp(array('is_success','out_trade_no','refund_fee'));//创建订单有问题。
	
	$is_success != 'T' && paymsg("read.php?tid=$tid",'act_refund_alipay_fail');
	list(,$tid,$actuid) = explode("_",$out_trade_no);

	$ifpay = $db->get_value("SELECT ifpay FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
	if ($ifpay != 1) {//未收到支付通知，但退款通知过来
		/*查询订单状态*/
		require_once(R_P . 'lib/activity/alipay_push.php');
		$alipayPush = new AlipayPush();
		$alipayPush->query_aa_detail_payment($tid,$actuid);
		/*查询订单状态*/
	}

	$memberdb = $db->get_one("SELECT am.ifpay,am.actmid,am.uid,am.username,am.totalcash,am.refundreason,am.refundcost,am.totalcash,am.isadditional,am.isrefund,t.subject,t.authorid,t.author FROM pw_activitymembers am LEFT JOIN pw_threads t ON am.tid=t.tid WHERE actuid=".S::sqlEscape($actuid));
	$memberdb['ifpay'] != 1 && paymsg("read.php?tid=$tid",'act_refund_fail');

	if ($memberdb['isrefund']) {//退款交易无法操作
		paymsg("read.php?tid=$tid",'act_undefined_operate');
	}

	$defaultValueTableName = getActivityValueTableNameByActmid();
	$defaultValue = $db->get_one("SELECT paymethod FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
	$defaultValue['paymethod'] != 1 && paymsg("read.php?tid=$tid",'act_undefined_operate');//只有支付方式为支付宝才可以退款

	$tempcost = $db->get_value("SELECT SUM(totalcash) as sum FROM pw_activitymembers WHERE isrefund=1 AND fupid=".S::sqlEscape($actuid));
	if ($refund_fee > number_format(($memberdb['totalcash'] - $tempcost), 2, '.', '')) {
		paymsg("read.php?tid=$tid",'act_refund_cost_error');
	}

	$sqlarray = array(
		'fupid'				=> $actuid,
		'tid'				=> $tid,
		'uid'				=> $memberdb['uid'],
		'actmid'			=> $memberdb['actmid'],
		'username'			=> $memberdb['username'],
		'totalcash'			=> $refund_fee,
		'signuptime'		=> $timestamp,
		'isrefund'			=> 1,
		'refundreason'		=> $memberdb['refundreason'],
	);
	$db->update("INSERT INTO pw_activitymembers SET " . S::sqlSingle($sqlarray));
	$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间
	$newactuid = $db->insert_id();
	/*支付成功费用流通日志
	退款成功
	*/

	$data = array();
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$statusValue = $postActForBbs->getActivityStatusValue($tid);
	$postActForBbs->UpdatePayLog($tid,$newactuid,$statusValue);
	/*支付成功费用流通日志*/

	/*短消息通知 退款 发起人*/
	$msgContentText = $memberdb['isadditional'] ? 'activity_refund2_content' : 'activity_refund_content';
	M::sendNotice(
		array($memberdb['author']),
		array(
			'title' => getLangInfo('writemsg', 'activity_refund_title', array(
						'uid'		=> $memberdb['uid'],
						'username'	=> $memberdb['username'],
						'tid'		=> $tid,
						'subject'	=> $memberdb['subject'],
						'totalcash'	=> $refund_fee
					)
				),
			'content' => getLangInfo('writemsg', $msgContentText, array(
						'uid'		=> $memberdb['uid'],
						'username'	=> $memberdb['username'],
						'tid'		=> $tid,
						'subject'	=> $memberdb['subject'],
						'totalcash'	=> $refund_fee
					)
				)
		),'notice_active', 'notice_active'
	);
		
	/*短消息通知 退款 参与人*/
	$msgContentText = $memberdb['isadditional'] ? 'activity_refund2_signuper_content' : 'activity_refund_signuper_content';
	M::sendNotice(
		array($memberdb['username']),
		array(
			'title' => getLangInfo('writemsg', 'activity_refund_signuper_title', array(
							'uid'		=> $memberdb['authorid'],
							'username'	=> $memberdb['author'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $refund_fee
					)
				),
			'content' => getLangInfo('writemsg', $msgContentText, array(
							'uid'		=> $memberdb['authorid'],
							'username'	=> $memberdb['author'],
							'tid'		=> $tid,
							'subject'	=> $memberdb['subject'],
							'totalcash'	=> $refund_fee
					)
				)
		),'notice_active', 'notice_active'
	);
	paymsg("read.php?tid=$tid",'act_refund_success');
} else {
	paymsg("index.php",'undefined_action');
}

function paymsg($url,$msg,$notify = 'success') {
	if (empty($_POST)) {
		refreshto($url,$msg);
	}
	exit($notify);
}
?>
<?php
!defined('A_P') && exit('Forbidden');

!$db_kmd_ifkmd  && Showmsg('kmd_close');
S::gp(array('a', 'action'));
S::gp(array('page'), 'GP', 2);
!S::inArray($a, array('info', 'records', 'buy', 'addthread', 'getthread', 'ajax', 'help')) && $a = 'info';
!$winduid && $a != 'help' && Showmsg('not_login');

$kmdService = L::loadClass('KmdService', 'forum');
$basename = "apps.php?q=$q&";
$current[$a] = 'class="current"'; 

if ($a == 'info') {
	$unPayedInfos = $kmdInfos = array();
	!$page && $page = 1;
	$kmdService->setPayLogsInvalidUsingTimestamp($winduid);
	$unPayedInfos = $kmdService->getUnPayedLogsByUid($winduid);
	$kmdInfos = $kmdService->getKmdInfoDetailByUid($winduid, ($page - 1) * $db_perpage, $db_perpage);
	$countKmdNum = $kmdService->countKmdInfosWithCondition(array('uid' => $winduid));
	$numofpages = numofpage($countKmdNum, $page, ceil($countKmdNum / $db_perpage), "{$basename}a=info&");

	if (!S::isArray($unPayedInfos) && !S::isArray($kmdInfos)) ObHeader($basename . 'a=buy');
	require_once PrintEot('m_kmd');
	pwOutPut();
} elseif ($a == 'records') {
	$records = array();
	!$page && $page = 1;
	$records = $kmdService->searchPayLogs(array('uid' => $winduid), ($page - 1) * $db_perpage, $db_perpage);
	$recordsNum = $kmdService->countPayLogs(array('uid' => $winduid));
	$numofpages = numofpage($recordsNum, $page, ceil($recordsNum / $db_perpage), "{$basename}a=records&");

	require_once PrintEot('m_kmd');
	pwOutPut();
} elseif ($a == 'buy') {
	!$_G['allowbuykmd'] && Showmsg('抱歉，您所属的用户组没有权限购买孔明灯');
	S::gp(array('kid', 'pid'), 'GP', 2);
	if (!$action || S::inArray($action, array('renew', 'pay'))) {
		pwCache::getData(D_P . 'data/bbscache/forumcache.php');
		pwCache::getData(D_P . 'data/bbscache/ol_config.php');
		
		$spreads = $kmdService->getSpreads();
		$jsonSpreadsArray = array();
		foreach ($spreads as $value) {
			$jsonSpreadsArray[] = $value;
		}
		$jsonSpreads = pwJsonEncode($jsonSpreadsArray);
		$userBuyInfo = $kmdService->getUserInfoByUid($winduid);
		$alipayChecked = $bankChecked = $cashChecked = '';
		$ol_onlinepay && $ol_payto && $alipayChecked = 'checked';
		!$alipayChecked && $db_kmd_account && $db_kmd_bank && $bankChecked = 'checked';
		!$alipayChecked && !$bankChecked && $db_kmd_address && $cashChecked = 'checked';
		
		if ($action == 'renew') {
			!$kid && Showmsg('请选择要续费的孔明灯');
			$kmdInfo = $kmdService->getKmdInfoByKid($kid);
			!$kmdInfo && Showmsg('您选择的孔明灯不存在');
			$kmdInfo['endtime'] <= $timestamp && Showmsg('该孔明灯已过期');
			$forumcache = str_replace("value=\"$kmdInfo[fid]\"", "value=\"$kmdInfo[fid]\" selected", $forumcache);
			$disabled = 'disabled';
		} elseif ($action == 'pay') {
			!$pid && Showmsg('请选择要支付的孔明灯');
			$payLog = $kmdService->getPayLogById($pid);
			!$payLog && Showmsg('支付记录不存在');
			L::loadClass('forum', 'forum', false);
			$forumInfo = new PwForum($payLog['fid']);
			$tmpForumInfo = '<a href="thread.php?fid=' . $forumInfo->fid . '" target="_blank">' . $forumInfo->name . '</a>';
			$tmpSpread = $spreads[$payLog['sid']];
			$tmpSpreadDiscount = (0 < $tmpSpread['discount'] && $tmpSpread['discount'] < 10) ? $tmpSpread['discount'] . '折' : '无折扣' ;
			$tmpSpreadMoney = (0 < $tmpSpread['discount'] && $tmpSpread['discount'] < 10) ? ($tmpSpread['price'] * $tmpSpread['discount'] / 10) : $tmpSpread['price'];
			$tmpSpreadMoney = round($tmpSpreadMoney, 2);
			$tmpSpreadInfo = '原价 ' . $tmpSpread['price'] . '元，<span class="s2">' . $tmpSpreadDiscount . '</span>，应付 <span class="s2">' . $tmpSpreadMoney . '元</span>';
		}
		
		require_once PrintEot('m_kmd');
		pwOutPut();
	} elseif ($action == 'save') {
		S::gp(array('realname', 'invoice', 'address', 'phone'));
		S::gp(array('fid', 'spread', 'paytype'), 'GP', 2);

		if (!$pid) {
			!$spread && Showmsg('请选择推广套餐');
			!$realname && Showmsg('请填写真实姓名');
			!$phone && Showmsg('请填写手机号码');
			!preg_match('/^1\d{10}$/is', $phone) && Showmsg('手机号码格式不正确');
			if ($kid) {
				$kmdInfo = $kmdService->getKmdInfoByKid($kid);
				!$kmdInfo && Showmsg('您选择的孔明灯不存在');
				$kmdInfo['endtime'] <= $timestamp && Showmsg('该孔明灯已过期');
				$fid = $kmdInfo['fid'];
			}
			$fid < 1 && Showmsg('请选择要推广的版块');
		} else {
			$payLog = $kmdService->getPayLogById($pid);
			!$payLog && Showmsg('支付记录不存在');
			list($fid, $spread) = array($payLog['fid'], $payLog['sid']);
		}
		
		!$paytype && Showmsg('请选择支付方式');
		L::loadClass('forum', 'forum', false);
		$forumInfo = new PwForum($fid);
		!$forumInfo->forumset['ifkmd'] && Showmsg('该板块未开启孔明灯');
		$leftKmdNum = $kmdService->getLeftKmdNumsByFid($fid);
		(!$leftKmdNum && !$kid && !$pid) && Showmsg('您选择推广的版块，孔明灯位置已满，请选择其他版块');
		$spreadInfo = $kmdService->getSpreadById($spread);
		!$spreadInfo && Showmsg('您选择的推广套餐不存在');
		pwCache::getData(D_P . 'data/bbscache/ol_config.php');
		((!$ol_onlinepay || !$ol_payto) && (!$db_kmd_account || !$db_kmd_bank) && !$db_kmd_address) && Showmsg('站点未设置支付方式，不能购买');
		!S::inArray($paytype, array(KMD_PAY_TYPE_ALIPAY, KMD_PAY_TYPE_BANK, KMD_PAY_TYPE_CASH)) && Showmsg('选择的支付方式不正确');
		($paytype == KMD_PAY_TYPE_ALIPAY && (!$ol_onlinepay || !$ol_payto)) && Showmsg('站点未开启支付宝支付');
		($paytype == KMD_PAY_TYPE_BANK && (!$db_kmd_account || !$db_kmd_bank)) && Showmsg('站点未设置银行转账信息');
		($paytype == KMD_PAY_TYPE_CASH && !$db_kmd_address) && Showmsg('站点未设置办理地址');
		
		if (!$pid) {
			$money = (0 < $spreadInfo['discount'] && $spreadInfo['discount'] < 10) ? ($spreadInfo['price'] * $spreadInfo['discount'] / 10) : floatval($spreadInfo['price']);
			$money = round($money, 2);
			$userInfo = array('uid' => $winduid, 'phone' => $phone, 'realname' => $realname, 'invoice' => $invoice, 'address' => $address);
			$tmpKid = $kid ? $kid : 0;
			$payLog = array('fid' => $fid, 'uid' => $winduid, 'sid' => $spread, 'kid' => $tmpKid, 'type' => $paytype, 'money' => $money, 'status' => KMD_PAY_STATUS_NOTPAY, 'createtime' => $timestamp);
		
			$kmdService->setUserInfoByUid($userInfo);
			$payLogId = $kmdService->addPayLog($payLog);
			
			$tmpMessageContent = array('username' => $windid, 'fid' => $fid, 'forumname' => $forumInfo->name, 'money' => $money);
			if ($db_kmd_reviewperson) {
				$kmdReviewPerson = explode(',', $db_kmd_reviewperson);
				$kmdReviewPerson = array_unique(array_merge($kmdReviewPerson, $manager));
				sendKmdMessages($kmdReviewPerson, array('kmd_review_title', array('username' => $windid)), array('kmd_review_content', $tmpMessageContent));
			}
			sendKmdMessages(array($windid), array('kmd_review_user_title'), array('kmd_review_user_content', $tmpMessageContent));
			
			if (!$money) { //支付的钱为0时，直接支付成功
				$updatePayLog = array('status' => KMD_PAY_STATUS_PAYED);
				if (!$kid) { //新购买
					$endtime = $timestamp + $spreadInfo['day'] * 86400;
					$newKmdInfo = array('fid' => $fid, 'uid' => $winduid, 'tid' => 0, 'status' => KMD_THREAD_STATUS_EMPTY, 'starttime' => $timestamp, 'endtime' => $endtime);
					$kmdService->addKmdInfo($newKmdInfo);
				} else { //续费
					$endtime = $kmdInfo['endtime'] + $spreadInfo['day'] * 86400;
					$updateKmdInfo = array('endtime' => $endtime);
					$kmdService->updateKmdInfo($updateKmdInfo, $kid);
				}
				$kmdService->updatePayLog($updatePayLog, $payLogId);
				refreshto("{$basename}a=info", '购买成功!');
			}
			
			$successMessage = $kid ? '您的孔明灯续费申请已提交，请等待管理员确认支付！' : '您的孔明灯购买申请已提交，请等待管理员确认支付！';
			$paytype != KMD_PAY_TYPE_ALIPAY && refreshto("{$basename}a=info", $successMessage);
		} else {
			$updatePayLog = array('type' => $paytype, 'status' => KMD_PAY_STATUS_NOTPAY, 'createtime' => $timestamp);
			$kmdService->updatePayLog($updatePayLog, $payLog['id']);
			
			$tmpMessageContent = array('username' => $windid, 'fid' => $fid, 'forumname' => $forumInfo->name, 'money' => $payLog['money']);
			if ($db_kmd_reviewperson) {
				$kmdReviewPerson = explode(',', $db_kmd_reviewperson);
				$kmdReviewPerson = array_unique(array_merge($kmdReviewPerson, $manager));
				sendKmdMessages($kmdReviewPerson, array('kmd_review_title', array('username' => $windid)), array('kmd_review_content', $tmpMessageContent));
			}
			sendKmdMessages(array($windid), array('kmd_review_user_title'), array('kmd_review_user_content', $tmpMessageContent));
			
			$paytype != KMD_PAY_TYPE_ALIPAY && refreshto("{$basename}a=info", '您的支付信息已提交，请等待管理员确认支付！');
			list($money, $payLogId) = array($payLog['money'], $payLog['id']);
		}
		
		$order_no = str_pad('0', 10, "0", STR_PAD_LEFT) . get_date($timestamp, 'YmdHis') . num_rand(5);
		$email = $winddb ? $winddb['email'] : '';
		$db->update("REPLACE INTO pw_clientorder SET " . S::sqlSingle(array(
			'order_no'	=> $order_no,
			'type'		=> 5,
			'uid'		=> $winduid,
			'price'		=> $money,
			'payemail'	=> $email,				
			'number'	=> 1,
			'date'		=> $timestamp,
			'state'		=> 0,
			'extra_1'   => $payLogId,
		)));
					
		require_once(R_P . 'require/onlinepay.php');
		$olpay = new OnlinePay($ol_payto);			
		ObHeader($olpay->alipayurl($order_no, $money, 5, "{$basename}a=info"));
	}
} elseif ($a == 'addthread') {
	S::gp(array('originalaction', 'tpcurl'));
	S::gp(array('kid', 'threadid', 'originaltid'), 'GP', 2);
	$kid < 1 && kmdAjaxMessage('孔明灯不存在');

	$kmdInfo = $kmdService->getKmdInfoByKid($kid);
	!$kmdInfo && kmdAjaxMessage('孔明灯不存在');
	$kmdInfo['uid'] != $winduid && kmdAjaxMessage('您无权操作别人的孔明灯');
	$kmdInfo['endtime'] <= $timestamp && kmdAjaxMessage('该孔明灯已过期');
	
	if (!$action || ($action == 'changethread' && !$originaltid)) {
		$title = $content = $tid = '';
		$getThreadUrl = $basename . 'a=getthread';
		if ($action == 'changethread') {
			$threadCacheService = Perf::gatherCache('pw_threads');
			$threadInfo = $threadCacheService->getThreadAndTmsgByThreadId($kmdInfo['tid']);
			$tid = $threadInfo['tid'];
			$threadUrl = $db_bbsurl . '/read.php?tid=' . $tid;
			$title = $threadInfo['subject'];
			$content = substrs(stripWindCode($threadInfo['content']), 100);
		}
		require_once PrintEot('m_kmd_ajax');
		ajax_footer();
	} elseif ($action == 'save') {
		if (!$threadid && $tpcurl) {
			$tpcurl = html_entity_decode($tpcurl);
			$urlInfo = parse_url($tpcurl);
			$urlInfo['host'] != $pwServer['HTTP_HOST'] && kmdAjaxMessage('链接不正确');
			preg_match("/tid=(\d+)/i", $tpcurl, $data) || preg_match("/tid-(\d+)/i", $tpcurl, $data) || preg_match("/\/(\d+)\.(htm|html)/i", $tpcurl, $data);
			$threadid = $data[1];
		}
		!$threadid && kmdAjaxMessage('请输入帖子链接');
		$originaltid == $threadid && kmdAjaxMessage('替换的帖子不能跟原来的相同');
		($originalaction == 'changethread' && (!$originaltid || $originaltid != $kmdInfo['tid'])) && kmdAjaxMessage('错误操作');
		$threadInfo = checkKmdThread($threadid);
		$threadInfo['fid'] != $kmdInfo['fid'] && kmdAjaxMessage('该帖子不属于当前孔明灯所在版块');
		$threadInfo['topped'] && kmdAjaxMessage('该帖子已经是置顶帖，不能添加为孔明灯');
		if ($originalaction == 'changethread') {
			(!$originaltid || $originaltid != $kmdInfo['tid']) && kmdAjaxMessage('错误操作');
			($db_kmd_deducttime && (($timestamp + $db_kmd_deducttime * 3600) >= $kmdInfo['endtime'])) && kmdAjaxMessage('推广时间不足，无法更换！');
		}
		
		$kmdUpdateInfo = array('tid' => $threadid, 'status' => KMD_THREAD_STATUS_CHECK);
		$originalaction == 'changethread' && ($kmdUpdateInfo['endtime'] = $kmdInfo['endtime'] - $db_kmd_deducttime * 3600);
		$kmdService->updateKmdInfo($kmdUpdateInfo, $kid);
		$originaltid && $kmdService->updateKmdThreadByTid($originaltid, 0);
		
		$tmpMessageContent = array('username' => $windid, 'tid' => $threadid, 'threadtitle' => $threadInfo['subject']);
		if ($db_kmd_reviewperson) {
			$kmdReviewPerson = explode(',', $db_kmd_reviewperson);
			$kmdReviewPerson = array_unique(array_merge($kmdReviewPerson, $manager));
			$messageTitle = $originalaction == 'changethread' ? 'kmd_review_thread_change_title' : 'kmd_review_thread_add_title';
			sendKmdMessages($kmdReviewPerson, array($messageTitle, array('username' => $windid)), array('kmd_review_thread_content', $tmpMessageContent));
		}
		sendKmdMessages(array($windid), array('kmd_review_user_thread_title'), array('kmd_review_user_thread_content', $tmpMessageContent));
		
		require_once(R_P . 'require/updateforum.php');
		updatetop();
		kmdAjaxMessage('操作成功！', 'success');
	}
} elseif ($a == 'getthread') {
	S::gp(array('tpcurl'));
	$tpcurl = html_entity_decode(urldecode($tpcurl));
	!$tpcurl && kmdAjaxMessage('请输入帖子链接');
	
	$urlInfo = parse_url($tpcurl);
	$urlInfo['host'] != $pwServer['HTTP_HOST'] && kmdAjaxMessage('链接不正确');
	preg_match("/tid=(\d+)/i", $tpcurl, $data) || preg_match("/tid-(\d+)/i", $tpcurl, $data) || preg_match("/\/(\d+)\.(htm|html)/i", $tpcurl, $data);
	(!$data || $data[1] < 1) && kmdAjaxMessage('该帖子不存在，请确认URL是否正确');
	$threadInfo = checkKmdThread($data[1]);
	$threadInfo['topped'] && kmdAjaxMessage('该帖子已经是置顶帖，不能添加为孔明灯');
	
	$content = substrs(stripWindCode($threadInfo['content']), 100);
	$info = array('tid' => $threadInfo['tid'], 'title' => $threadInfo['subject'], 'content' => $content);
	kmdAjaxMessage(pwJsonEncode($info), 'success'); 
} elseif ($a == 'ajax') {
	S::gp(array('action'));
	S::gp(array('fid'), 'GP', 2);
	if ($fid < 1) kmdAjaxMessage('请选择要推广的版块');
	
	L::loadClass('forum', 'forum', false);
	$forumInfo = new PwForum($fid);
	if (!$forumInfo->forumset['ifkmd']) kmdAjaxMessage('该版块未开启孔明灯');
	$leftKmdNum = $kmdService->getLeftKmdNumsByFid($fid);
	$value = $leftKmdNum > 0 ? $forumInfo->name : 0;
	$value = S::inArray($action, array('renew', 'pay')) ? 1 : $value;
	kmdAjaxMessage($value, 'success');
} elseif ($a == 'help') {
	require_once(R_P.'require/header.php');
	
	$openforum = $spreads = array();
	foreach ($forum as $value){
		$foruminfo = array();
		pwCache::getData(S::escapePath(D_P . "data/forums/fid_{$value['fid']}.php"));
		$foruminfo['forumset']['ifkmd'] && $openforum[]= $value ;
	}
	$spreads = $kmdService->getSpreads();
	
	//判断是否绑定支付宝
	$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
	$isBindAlipay =0;
	if($siteBindService->isBind('alipay')){
		$isBindAlipay =1;
	}
	
	//获取alipay登录弹窗js代码
	L::loadClass('WeiboLoginViewHelper', 'sns/weibotoplatform/viewhelper', false);
	$windowOpenScript = WeiboLoginViewHelper_WindowOpenScript('alipay'); 

	require_once PrintEot('m_kmd_help'); //购买记录模板
	footer();
}

function sendKmdMessages($user, $title, $content) {
	M::sendNotice($user, array(
		'title' => getLangInfo('writemsg', $title[0], $title[1]),
		'content' => getLangInfo('writemsg', $content[0], $content[1]))
	);
}

function checkKmdThread($tid) {
	global $winduid, $kmdService;
	$threadCacheService = Perf::gatherCache('pw_threads');
	$threadInfo = $threadCacheService->getThreadAndTmsgByThreadId($tid);
	!$threadInfo && kmdAjaxMessage('该帖子不存在，请确认URL是否正确');
	$threadInfo['authorid'] != $winduid && kmdAjaxMessage('孔明灯只能对自己的帖子使用，请确认该帖子归属');
	$threadExists = $kmdService->getKmdInfoByTid($tid);
	$threadExists && kmdAjaxMessage('该帖子已经是孔明灯帖或正在审核中');
	return $threadInfo;
}

function kmdAjaxMessage($message, $type = 'error') {
	$message = getLangInfo('msg', $message);
	echo $type . "\t" . $message;
	ajax_footer();
}
?>
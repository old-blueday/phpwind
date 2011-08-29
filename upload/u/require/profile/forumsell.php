<?php
!function_exists('readover') && exit('Forbidden');

$pro_tab = 'permission';
$f = $db->get_one("SELECT f.forumsell,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=" . S::sqlEscape($fid));
empty($f) && Showmsg('data_error');
if (!$f['forumsell']) {
	Showmsg('forumsell_error');
}
$forumset = unserialize($f['forumset']);
require_once(R_P.'require/credit.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');
$fname = $forum[$fid]['name'];

if (empty($_POST['step'])) {

	S::gp(array('page','date'));
	$page = (int)$page;
	if ($date && isset($forumset['sellprice'][$date])) {

	} else {
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*10, 10);
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumsell WHERE uid=" . S::sqlEscape($winduid));
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/10), "profile.php?action=forumsell&fid=$fid&");
		$query = $db->query("SELECT * FROM pw_forumsell WHERE uid=" . S::sqlEscape($winduid) . " ORDER BY overdate DESC $limit");
		$buydb = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['buydate']	= get_date($rt['buydate']);
			$rt['overdate']	= get_date($rt['overdate']);
			$buydb[] = $rt;
		}
	}
	require_once uTemplate::PrintEot('profile_forumsell');
	pwOutPut();
} else {

	PostCheck();
	S::gp(array('date','buymethod'));
	$rt = $db->get_one("SELECT MAX(overdate) AS u FROM pw_forumsell WHERE uid=" . S::sqlEscape($winduid) . " AND fid=" . S::sqlEscape($fid));
	if ($rt['u'] > $timestamp) {
		Showmsg('forumsell_already');
	}
	if (!isset($forumset['sellprice'][$date])) {
		Showmsg('forumsell_date');
	}
	if ($buymethod) {
		if ($forumset['sellprice'][$date]['rprice'] <= 0) {
			Showmsg('undefined_action');
		}
		//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
		pwCache::getData(D_P.'data/bbscache/ol_config.php');
		if (!$ol_onlinepay) {
			Showmsg($ol_whycolse);
		}
		$order_no = '1'.str_pad($winduid,10, "0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);
		$db->update("INSERT INTO pw_clientorder SET " . S::sqlSingle(array(
			'order_no'	=> $order_no,
			'type'		=> 2,
			'uid'		=> $winduid,
			'paycredit'	=> $fid,
			'price'		=> $forumset['sellprice'][$date]['rprice'],
			'number'	=> 1,
			'date'		=> $timestamp,
			'state'		=> 0,
			'extra_1'	=> $date
		)));
		if (!$ol_payto) {
			Showmsg('olpay_alipayerror');
		}
		require_once(R_P.'require/onlinepay.php');
		$olpay = new OnlinePay($ol_payto);
		ObHeader($olpay->alipayurl($order_no, $forumset['sellprice'][$date]['rprice'], 2));
	}
	if ($forumset['sellprice'][$date]['cprice'] <= 0) {
		Showmsg('undefined_action');
	}
	if ($credit->get($winduid,$f['forumsell']) < $forumset['sellprice'][$date]['cprice']) {
		$creditname = pwCreditNames($f['forumsell']);
		Showmsg('forumsell_price');
	}
	$credit->addLog('main_forumsell',array($f['forumsell'] => -$forumset['sellprice'][$date]['cprice']),array(
		'uid'		=> $winduid,
		'username'	=> $windid,
		'ip'		=> $onlineip,
		'fname'		=> $forum[$fid]['name'],
		'days'		=> $date
	));
	$credit->set($winduid,$f['forumsell'],-$forumset['sellprice'][$date]['cprice']);

	$overdate = $timestamp + $date * 86400;
	$db->update("INSERT INTO pw_forumsell SET " . S::sqlSingle(array(
		'fid'		=> $fid,
		'uid'		=> $winduid,
		'buydate'	=> $timestamp,
		'overdate'	=> $overdate,
		'credit'	=> $f['forumsell'],
		'cost'		=> $forumset['sellprice'][$date]['cprice']
	),false));
	refreshto("thread.php?fid=$fid",'operate_success');
}
?>
<?php
!defined('P_W') && exit('Forbidden');
define('AJAX','1');
PostCheck();
S::gp(array(
	'pid',
	'page',
	'type'
));

$template = 'ajax_job';

if (empty($windid)) {
	Showmsg('not_login');
}

$tpcs = array();
if ($pid == 'tpc') {
	$table = GetTtable($tid);
	$tpcs = $db->get_one("SELECT t.author,t.authorid,t.subject,tm.userip,tm.content,tm.buy FROM pw_threads t LEFT JOIN $table tm ON tm.tid=t.tid WHERE t.tid=" . S::sqlEscape($tid));
	$where = '';
} elseif (is_numeric($pid)) {
	$table = GetPtable('N', $tid);
	$tpcs = $db->get_one("SELECT author,authorid,subject,userip,content,buy FROM $table WHERE pid=" . S::sqlEscape($pid) . ' AND tid=' . S::sqlEscape($tid));
	$where = ' AND pid=' . S::sqlEscape($pid);
}

!$tpcs && Showmsg('illegal_tid');

!$tpcs['subject'] && $tpcs['subject'] = preg_replace('/\[.*?\]/i', '', substrs($tpcs['content'], 25));
$tpcs['content'] = substr($tpcs['content'], strpos($tpcs['content'], '[sell=') + 6);
$cost = substr($tpcs['content'], 0, strpos($tpcs['content'], ']'));
list($creditvalue, $credittype) = explode(',', $cost);

$tpcsBuyDate = $tpcs['buy'] ? unserialize($tpcs['buy']) : array();
require_once (R_P . 'require/credit.php');

if ($type == 'record') {
	S::gp(array('page'));
	$page = (int) $page;
	$page < 1 && $page = 1;
	$db_perpage = 10;
	$total = count($tpcsBuyDate);
	$tpcsBuyDate = array_slice($tpcsBuyDate, ($page - 1) * $db_perpage, $db_perpage);
	$pages = numofpage($total, $page, ceil($total/$db_perpage), "job.php?action=buytopic&type=record&tid=$tid&pid=$pid&", null, 'ajaxurl');
	$buyers = array();
	foreach ($tpcsBuyDate as $key => $buy) {
		$buy['createdtime'] = get_date($buy['createdtime'],'Y-m-d H:i');
		$buy['cost'] = $buy['creditvalue'];
		$buy['ctype'] = $credit->cType[$buy['credittype']];
		$buyers[$buy['uid']] = $buy;
	}
	require_once PrintEot($template);
	ajax_footer();
} elseif ($type == 'buy') {
	S::gp(array('step'));
	if ($winduid == $tpcs['authorid']) {
		Showmsg('自己的帖子不需要购买');
	}
	if ($tpcsBuyDate && array_key_exists($winduid,$tpcsBuyDate) !== false) {
		Showmsg('job_havebuy');
	}
	
	$count = count($tpcsBuyDate);
	$creditvalue = (int) $creditvalue;
	if ($creditvalue < 0) {
		$creditvalue = 0;
	} elseif ($db_sellset['price'] && $creditvalue > $db_sellset['price']) {
		$creditvalue = $db_sellset['price'];
	}
	
	!isset($credit->cType[$credittype]) && $credittype = 'money';
	$userCredit = $credit->get($winduid, $credittype);
	$creditname = $credit->cType[$credittype];
	if ($userCredit < $creditvalue) {
		Showmsg('job_buy_noenough');
	}
	if ($step == 2) {
		
		$credit->addLog('topic_buy', array(
			$credittype => -$creditvalue
		), array(
			'uid' => $winduid,
			'username' => $windid,
			'ip' => $onlineip,
			'tid' => $tid,
			'subject' => $tpcs['subject']
		));
		$credit->set($winduid, $credittype, -$creditvalue, false);
		
//		$creditvalue > 10 && $creditvalue = $creditvalue * 0.9;
		$income = $creditvalue * $count;
		if (!$db_sellset['income'] || $income < $db_sellset['income']) {
			$credit->addLog('topic_sell', array(
				$credittype => $creditvalue
			), array(
				'uid' => $tpcs['authorid'],
				'username' => $tpcs['author'],
				'ip' => $tpcs['userip'],
				'tid' => $tid,
				'subject' => $tpcs['subject'],
				'buyer' => $windid
			));
			$credit->set($tpcs['authorid'], $credittype, $creditvalue, false);
		}
		$credit->runsql();
		
		
		$tpcsBuy = array();
		$tpcsBuy[$winduid] = array(
					'uid' 			=> $winduid,
					'username' 		=> $windid,
					'ip' 			=> $tpcs['userip'],
					'tid' 			=> $tid,
					'subject' 		=> $tpcs['subject'],
					'credittype'	=>$credittype,
					'creditvalue'	=>$creditvalue,
					'author' 		=> $tpcs['author'],
					'authorid'		=> $tpcs['authorid'],
					'createdtime' 	=> $timestamp
				);
		
		$tpcs['buy'] = $tpcsBuyDate ? serialize($tpcsBuy+$tpcsBuyDate) : serialize($tpcsBuy);
		
		$db->update("UPDATE $table SET buy=" . S::sqlEscape($tpcs['buy'], false) . " WHERE tid=" . S::sqlEscape($tid) . $where);
		
		//* $threadObj = L::loadClass("threads", 'forum');
		//* $threadObj->clearThreadByThreadId($tid);
		//* $threadObj->clearTmsgsByThreadId($tid);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$tid));
		Showmsg('ajaxma_success');
	} else {
		$basename = "job.php?action=buytopic&type=buy&tid=$tid&pid=$pid&page=$page";
		require_once PrintEot($template);
		ajax_footer();
	}
}
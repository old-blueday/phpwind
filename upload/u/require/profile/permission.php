<?php
!defined('R_P') && exit('Forbidden');

InitGP(array('job'),'G');
InitGP(array('gid','fid'),'G',2);
require_once(R_P.'require/functions.php');
require_once(R_P.'require/credit.php');

if (!$fid) {

	InitGP(array('gid'),'G',2);
	include_once(D_P . 'data/bbscache/level.php');

	$usercredit = array(
		'postnum'	=> $winddb['postnum'],
		'digests'	=> $winddb['digests'],
		'rvrc'		=> $winddb['rvrc'],
		'money'		=> $winddb['money'],
		'credit'	=> $winddb['credit'],
		'currency'	=> $winddb['currency'],
		'onlinetime'=> $winddb['onlinetime']
	);
	$creditdb = $credit->get($winduid,'CUSTOM');
	foreach ($creditdb as $key => $value) {
		$usercredit[$key] = $value;
	}
	$upgradeset  = unserialize($db_upgrade);
	$totalcredit = CalculateCredit($usercredit,$upgradeset);
	$cNames = $credit->cType;

	$per = $gRight = $mygpdb = $_gmember = $_gspecial = $_gsystem = array();
	$winddb['groupid'] != '-1' && $mygpdb[$winddb['groupid']] = $ltitle[$winddb['groupid']];
	$mygpdb[$winddb['memberid']] = $ltitle[$winddb['memberid']];
	if ($winddb['groups']) {
		$groups = explode(',',$winddb['groups']);
		foreach ($groups as $value) {
			$value = (int)$value;
			$value && array_key_exists($value,$ltitle) && $mygpdb[$value] = $ltitle[$value];
		}
	}
	!$gid && $gid = $groupid;
	$query = $db->query("SELECT gid,gptype FROM pw_usergroups WHERE gptype!='default' ORDER BY grouppost,gid");
	while ($rt = $db->fetch_array($query)) {
		if (strpos(','.$_G['pergroup'].',',','.$rt['gptype'].',') !== false) {
			if ($rt['gptype'] == 'member') {
				$_gmember[$rt['gid']] = array('title' => $ltitle[$rt['gid']], 'post' => $lneed[$rt['gid']]);
			} else {
				${'_g'.$rt['gptype']}[$rt['gid']] = array('title' => $ltitle[$rt['gid']]);
			}
		} elseif ($gid == $rt['gid'] && !$mygpdb[$gid]) {
			Showmsg('per_error');
		}
	}
	$query = $db->query("SELECT rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid=".pwEscape($gid)."AND type='basic'");
	while ($rt = $db->fetch_array($query)) {
		$gRight[$rt['rkey']] = $rt['rvalue'];
	}
	$db->free_result($query);
	$per['allowpcid1'] = strpos(','.$gRight['allowpcid'].',',',1,') !== false ? 1 : 0;  //团购帖
	$per['allowpcid2'] = strpos(','.$gRight['allowpcid'].',',',2,') !== false ? 1 : 0;
	$per['hide']	= $gRight['allowhide']		? 1 : 0;
	$per['read']	= $gRight['allowread']		? 1 : 0;
	$per['search']	= $gRight['allowsearch']	? 1 : 0;
	$per['member']	= $gRight['allowmember']	? 1 : 0;
	$per['profile']	= $gRight['allowprofile']	? 1 : 0;
	$per['show']	= $gRight['show']			? 1 : 0;
	$per['report']	= $gRight['allowreport']	? 1 : 0;
	$per['upload']	= $gRight['upload']			? 1 : 0;
	$per['portait']	= $gRight['allowportait']	? 1 : 0;
	$per['honor']	= $gRight['allowhonor']		? 1 : 0;
	$per['post']	= $gRight['allowpost']		? 1 : 0;
	$per['rp']		= $gRight['allowrp']		? 1 : 0;
	$per['newvote']	= $gRight['allownewvote']	? 1 : 0;
	$per['vote']	= $gRight['allowvote']		? 1 : 0;
	$per['vwvt']	= $gRight['viewvote']		? 1 : 0;
	$per['html']	= $gRight['htmlcode']		? 1 : 0;
	$per['hidden']	= $gRight['allowhidden']	? 1 : 0;
	$per['sell']	= $gRight['allowsell']		? 1 : 0;
	$per['mark']	= $gRight['markable']		? 1 : 0;
	$per['attach']	= $gRight['allowupload']	? 1 : 0;
	$per['down']	= $gRight['allowdownload']	? 1 : 0;
	$per['sort']	= $gRight['allowsort']		? 1 : 0;
	$per['messege']	= $gRight['allowmessege']	? 1 : 0;
//	$per['active']	= $gRight['allowactive']	? 1 : 0;
	$per['reward']	= $gRight['allowreward']	? 1 : 0;
	$per['anonymous'] = $gRight['anonymous']	? 1 : 0;
	$per['leaveword'] = $gRight['leaveword']	? 1 : 0;
	$per['maxmsg'] = (int)$gRight['maxmsg'];
	$per['signnum'] = (int)$gRight['signnum'];
	$per['allownum'] = (int)$gRight['allownum'];
	$per['maxfavor'] = (int)$gRight['maxfavor'];
	$per['maxgraft'] = !$gRight['maxgraft'] ? 0 : $gRight['maxgraft'];
	$per['uploadmaxsize'] = ceil(($gRight['uploadmaxsize'] ? $gRight['uploadmaxsize'] : $db_uploadmaxsize)/1024);
	!$gRight['uploadtype'] && $gRight['uploadtype'] = $db_uploadfiletype;
	$gRight['uploadtype'] = unserialize($gRight['uploadtype']);
	$per['uptype'] = '';
	foreach ($gRight['uploadtype'] as $key => $value) {
		$per['uptype'] .= ($per['uptype'] ? ', ' : '')."$key:$value";
	}
	unset($creditdb,$groups,$value,$ltitle,$gRight);

} else {

	require_once(R_P.'require/forum.php');
	if (!($rt = L::forum($fid))) {
		Showmsg('data_error');
	}
	(!$rt || $rt['type'] == 'category') && Showmsg('data_error');
	wind_forumcheck($rt);
	$forumset = $rt['forumset'];
	$forumset['link'] && Showmsg('data_error');

	$per = $forumright = array();
	$creditset = $credit->creditset($rt['creditset'],$db_creditset);
	foreach ($creditset as $key => $value) {
		foreach ($value as $k => $v) {
			$forumright[$k][$key] = (int)$v;
		}
	}
	$per['upload'] = $per['down'] = $per['rp'] = $per['post'] = $per['visit'] = 1;
	$per['name'] = strip_tags($rt['name']);
	if ($rt['allowvisit'] && strpos($rt['allowvisit'],','.$groupid.',')===false) {
		$per['visit'] = 0;
	}
	if (($rt['allowpost'] && strpos($rt['allowpost'],','.$groupid.',')===false) || (!$rt['allowpost'] && $_G['allowpost']==0)) {
		$per['post'] = 0;
	}
	if (($rt['allowrp'] && strpos($rt['allowrp'],','.$groupid.',')===false) || (!$rt['allowrp'] && $_G['allowpost']==0)) {
		$per['rp'] = 0;
	}
	if ($rt['allowdownload'] && strpos($rt['allowdownload'],','.$groupid.',')===false) {
		$per['down'] = 0;
	} elseif (!$rt['allowdownload'] && $_G['allowpost'] == 0) {
		$per['down'] = 0;
	}
	if ($rt['allowupload'] && strpos($rt['allowupload'],','.$groupid.',')===false) {
		$per['upload'] = 0;
	} elseif (!$rt['allowupload'] && $_G['allowpost']==0) {
		$per['upload'] = 0;
	}
	unset($forumset,$rt);
}
require_once uTemplate::PrintEot('profile_permission');
pwOutPut();
?>
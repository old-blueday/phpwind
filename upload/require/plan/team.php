<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P.'require/credit.php');
//* @include_once pwCache::getPath(D_P.'data/bbscache/tm_config.php');
//pwCache::getData(D_P.'data/bbscache/tm_config.php');
pwCache::getData(D_P.'data/bbscache/config.php');

$gids = 0;
$_tmconf = unserialize($db_team);
if (!empty($_tmconf['group'])) {
	$gids = implode(',',$_tmconf['group']);
}

$admindb = array();
$query = $db->query("SELECT m.uid,m.username,m.groupid,md.monthpost,md.monoltime,md.lastvisit,md.lastpost FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE groupid IN($gids) ORDER BY groupid");

while ($rs = $db->fetch_array($query)) {
	$rs['lastvisit'] < $montime && $rs['monoltime'] = 0;
	$rs['lastpost']  < $montime && $rs['monthpost'] = 0;
	$admindb[$rs['username']] = array(
		'uid'		=> $rs['uid'],
		'groupid'	=> $rs['groupid'],
		'monoltime'	=> round($rs['monoltime']/3600),
		'monthpost'	=> $rs['monthpost'],
		'total'		=> 0,
		'arouse'	=> 0,
	);
}
$query = $db->query("SELECT COUNT(*) AS count,username2 AS manager FROM pw_adminlog WHERE timestamp>'$montime' GROUP BY username2");

while ($rs = $db->fetch_array($query)) {
	if (isset($admindb[$rs['manager']])) {
		$admindb[$rs['manager']]['total'] = $rs['count'];
	}
}
foreach ($admindb as $key=>$value) {
	$gid = $value['groupid'];
	$admindb[$key]['assess'] = $value['total'] * $_tmconf['param']['opr'] + $value['monoltime'] * $_tmconf['param']['oltime'] + $value['monthpost'] * $_tmconf['param']['post'];
	$admindb[$key]['wages'] = $_tmconf['wages'][$gid];
	foreach ($admindb[$key]['wages'] as $k=>$v) {
		$admindb[$key]['wages'][$k] += round($admindb[$key]['assess'] * $_tmconf['bonus'][$k]);
	}
	$admindb[$key]['assess'] < $_tmconf['eligibility'] && $admindb[$key]['arouse'] = 1;
}

$datef	 = get_date($timestamp,'Y - m');
$msgdata = S::escapeChar($_tmconf['msgdata']);
$arousemsg = S::escapeChar($_tmconf['arousemsg']);

foreach ($admindb as $username => $value) {
	
	$uid = $value['uid'];
	$addcredit = '';
	foreach ($value['wages'] as $k => $v) {
		if (empty($v) || !is_numeric($v)) continue;
		$addcredit .= ($addcredit ? ',' : '')."[color=#0000ff]{$v}[/color]".$credit->cType[$k];
	}
	if ($value['assess'] >= $_tmconf['eligibility']) {
		$credit->addLog('hack_teampay',$value['wages'],array(
			'uid'		=> $uid,
			'username'	=> $username,
			'ip'		=> $onlineip,
			'datef'		=> $datef
		));
		$credit->sets($uid,$value['wages'],false);
	}
	if ($addcredit) {
		if ($_tmconf['arouse'] && $value['arouse'] || $_tmconf['ifmsg']) {
			M::sendNotice(array($username),array('title' => $_tmconf['msgtitle'],'content' => str_replace(array('$username','$db_bbsname','$credit','$time'),array($username,$db_bbsname,$addcredit, get_date($timestamp)),($_tmconf['arouse'] && $value['arouse']) ? $arousemsg : $msgdata)));
		}
	}
}
$credit->runsql();
?>
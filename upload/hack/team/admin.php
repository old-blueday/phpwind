<?php
!function_exists('adminmsg') && exit('Forbidden');

$rt = $db->get_one("SELECT ifopen,nexttime FROM pw_plan WHERE filename='team'");

if (empty($action)) {

	require_once(R_P.'require/credit.php');
	@include_once(D_P.'data/bbscache/tm_config.php');
	ifcheck($rt['ifopen'],'ifopen');
	ifcheck($_tmconf['ifmsg'],'ifmsg');
	ifcheck($_tmconf['arouse'],'arouse');
	$nexttime = get_date($rt['nexttime']);

	$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype='system'");
	$group = array();
	while ($rs = $db->fetch_array($query)) {
		$group[] = $rs;
	}

	include PrintHack('admin');exit;

} elseif ($action == 'sort') {

	require_once(R_P.'require/credit.php');
	@include_once(D_P.'data/bbscache/tm_config.php');

	$hours	 = gmdate('G',$timestamp+$db_timedf*3600);
	$tdtime	 = PwStrtoTime(get_date($timestamp,'Y-m-d'));
	$montime = PwStrtoTime(get_date($timestamp,'Y-m')."-1");

	$gids = 0;
	if (!empty($_tmconf['group'])) {
		$gids = pwImplode($_tmconf['group']);
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
			'total'		=> 0
		);
	}
	$query = $db->query("SELECT COUNT(*) AS count,username2 AS manager FROM pw_adminlog WHERE timestamp>".pwEscape($montime)."GROUP BY username2");

	while ($rs = $db->fetch_array($query)) {
		if (isset($admindb[$rs['manager']])) {
			$admindb[$rs['manager']]['total'] = $rs['count'];
		}
	}
	foreach ($admindb as $key => $value) {
		$gid = $value['groupid'];
		$admindb[$key]['assess'] = $value['total'] * $_tmconf['param']['opr'] + $value['monoltime'] * $_tmconf['param']['oltime'] + $value['monthpost'] * $_tmconf['param']['post'];
		$admindb[$key]['wages'] = $_tmconf['wages'][$gid];
		foreach ($admindb[$key]['wages'] as $k=>$v) {
			$admindb[$key]['wages'][$k] += round($admindb[$key]['assess'] * $_tmconf['bonus'][$k]);
		}
	}

	include PrintHack('admin');exit;

} elseif ($_POST['action'] == 'set') {

	InitGP(array('set','ifopen'));

	$set['msgtitle'] = stripslashes($set['msgtitle']);
	foreach ($set['wages'] as $k=>$value) {
		if (!in_array($k,$set['group'])) {
			unset($set['wages'][$k]);
		}
	}
	$set = serialize($set);
	$db->Update("REPLACE INTO pw_hack SET hk_name='tm_setting',hk_value=".pwEscape($set,false));

	updatecache_tm();

	if ($rt['ifopen'] != $ifopen) {
		if ($ifopen && $rt['nexttime'] < $timestamp) {
			adminmsg('请先到计划任务中开启“管理团队工资发放”');
		}
		$db->Update("UPDATE pw_plan SET ifopen=".pwEscape($ifopen)."WHERE filename='team'");
		updatecache_plan();
	}

	adminmsg('operate_success');

} elseif ($_POST['action'] == 'payoff') {

	InitGP(array('paycredit','arouse'));
	@include_once(D_P.'data/bbscache/tm_config.php');
	require_once(R_P.'require/credit.php');

	$gids = array(0);
	if (!empty($_tmconf['group'])) {
		$gids = $_tmconf['group'];
	}
	$admindb = array();
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$members = $userService->getByGroupIds($gids);
	foreach ($members as $rt) {
		$admindb[$rt['uid']] = $rt['username'];
	}
	$datef	 = get_date($timestamp,'Y - m');
	$msgdata = Char_cv($_tmconf['msgdata']);
	$arousemsg = Char_cv($_tmconf['arousemsg']);

	foreach ($paycredit as $uid => $value) {
		$addcredit = '';
		foreach ($value as $k => $v) {
			if (empty($v) || !is_numeric($v)) continue;
			$addcredit .= ($addcredit ? ',' : '')."[color=#0000ff]{$v}[/color]".$credit->cType[$k];
		}
		$credit->addLog('hack_teampay',$value,array(
			'uid'		=> $uid,
			'username'	=> $admindb[$uid],
			'ip'		=> $onlineip,
			'datef'		=> $datef
		));
		$credit->sets($uid,$value,false);

		if ($addcredit) {
			if ($_tmconf['arouse'] && in_array($uid,$arouse) || $_tmconf['ifmsg']) {
				M::sendNotice(array($admindb[$uid]),array('title' => $_tmconf['msgtitle'],'content' => str_replace(array('$username','$db_bbsname','$credit','$time'),array($admindb[$uid],$db_bbsname,$addcredit,get_date($timestamp)),($_tmconf['arouse'] && in_array($uid,$arouse)) ? $arousemsg : $msgdata)));
			}
		}
	}
	$credit->runsql();
	adminmsg('operate_success');
}

function updatecache_tm() {
	global $db;
	$rs = $db->get_one("SELECT hk_value FROM pw_hack WHERE hk_name='tm_setting'");
	$ar = (array)unserialize($rs['hk_value']);
	writeover(D_P.'data/bbscache/tm_config.php',"<?php\r\n\$_tmconf=".pw_var_export($ar).";\r\n?>");
}
?>
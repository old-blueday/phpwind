<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('action','set'));
$rt = $db->get_one("SELECT ifopen,nexttime FROM pw_plan WHERE filename='team'");
pwCache::getData(D_P.'data/bbscache/config.php');

if (empty($action)) {
	S::gp(array('team'));
	require_once(R_P.'require/credit.php');
	$team = unserialize($db_team);
	//* @include_once pwCache::getPath(D_P.'data/bbscache/tm_config.php');
	ifcheck($rt['ifopen'],'ifopen');
	ifcheck($team['ifmsg'],'ifmsg');
	ifcheck($team['arouse'],'arouse');
	$nexttime = get_date($rt['nexttime']);
	
	$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype='system'");
	$group = array();
	while ($rs = $db->fetch_array($query)) {
		$group[] = $rs;
	}
	include PrintEot('team');exit;
}elseif($action == 'teamList'){
	S::gp(array('step','dataOut'));
	$query = $db->query("SELECT uid FROM pw_administrators ORDER BY uid");
	$teamUid = array();
	while ($rt = $db->fetch_array($query)) {
		$teamUid[] = $rt['uid'];
	}
	$systemQuery = $db->query("SELECT gid FROM pw_usergroups WHERE gptype = 'system' ORDER BY gid");
	
	$systemGroup = array();
	while ($rt = $db->fetch_array($systemQuery)) {
		$systemGroup[] = $rt['gid'];
	}

	$sql = $teamUid ? ' AND uid IN (' .S::sqlImplode($teamUid).')' : '';
	$admindb = array();
	$query = $db->query("SELECT m.uid,m.username,m.groupid,m.groups,m.regdate,md.lastvisit,md.lastpost FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE 1 $sql ORDER BY uid");
	if(!$query) adminmsg('读取数据出错');
	$userInfo = array();
	while ($rt = $db->fetch_array($query)) {
		$rt['lastvisit'] = $rt['lastvisit'] ? get_date($rt['lastvisit'],'Y-m-d') : '';
		$rt['lastpost']  = $rt['lastpost'] ? get_date($rt['lastpost'],'Y-m-d') : '';
		$rt['regdate']   = $rt['regdate'] ? get_date($rt['regdate'],'Y-m-d') : '';
		if(!in_array($rt['groupid'],$systemGroup)){
			$groups = array();
			$groups = explode(",",$rt['groups']);
			$teamId = array_values(array_intersect($groups,$systemGroup));
			$rt['groupid'] = $teamId[0];
		}
		if(!$rt['groupid']) continue;
		$userInfo[$rt['username']] = array(
			'uid'		=> $rt['uid'],
			'groupid'	=> $rt['groupid'],
			'lastvisit'	=> $rt['lastvisit'],
			'lastpost'	=> $rt['lastpost'],
			'regdate'	=> $rt['regdate'],
		);	
	}
	$logForums = getUserManageForums();
	foreach ($logForums as $key => $f) {
		if (isset($userInfo[$key])) {
			$f = array_filter($f);
			$userInfo[$key][forumName] = array_shift($f);
			$f && $userInfo[$key][forumNames] = implode(' ',$f);
		}
	}
	if($dataOut){
		 $filename = 'teamlist_' . get_date($timestamp, 'Ymd');
	      $outData = '';
	      foreach ($userInfo as $key => $data) {
	      	 S::isArray($logForums[$key]) && $tmpForumNames = array_unique($logForums[$key]);
	         $tmpForumNames = $tmpForumNames ? implode(',',$tmpForumNames) : '无';
	         $outData .= $data['uid']."\t";
	         $outData .= $key."\t";
	         $outData .= $ltitle[$data['groupid']]."\t";
	         $outData .= $data['regdate']."\t";
	         $outData .= $data['lastvisit']."\t";
	         $outData .= $data['lastpost']."\t";
//	         $outData .= $tmpForumNames."\t";
	         $outData .= "\r\n";
	      }
	
	      ob_end_clean();
	      header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	      header('Cache-control: no-cache');
	      header('Content-Encoding: none');
	      header('Content-Disposition: attachment; filename=' . $filename . ".xls");
	      header("Content-type:application/vnd.ms-excel");
	      header("Content-Transfer-Encoding: binary");
	      header('Content-Length: ' . strlen($outData));
	      echo "用户ID"."\t";
	      echo "用户名"."\t";
	      echo "用户组"."\t";
	      echo "注册时间"."\t";
	      echo "最后发表"."\t";
	      echo "最后登录"."\t";
//	      echo "操作版块"."\t";
		  echo "\r\n";
	      echo $outData;
	      exit();
	}
	include PrintEot('team');exit;
} elseif ($action == 'actionList') {
	$baseurl = 'admin.php?adminjob=team&action=actionList';
	S::gp(array('step','sortType','dataOut','postStartTime','postEndTime','adminName','fid'));
	pwCache::getData(D_P . 'data/bbscache/forumcache.php');
	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
	$sqlAdd = '';
	if ($step) {
	//	S::gp(array('postStartTime','postEndTime','adminName','fid'));
		$adminName = trim($adminName);
		$endDate = $postEndTime ? PwStrtoTime($postEndTime) : 0;
		$startDate = $postStartTime ? PwStrtoTime($postStartTime) : 0;
		$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);

		if ($startDate && $endDate && $startDate > $endDate) Showmsg('起始时间大于截止时间');
		$urlAdd = "&step=2";
		if ($startDate) {
			$sqlAdd .= ' AND timestamp >= ' . S::sqlEscape($startDate);
			$urlAdd .= "&postStartTime=$postStartTime";
		}
		if ($endDate) {
			$sqlAdd .= ' AND timestamp <= ' . S::sqlEscape($endDate);
			$urlAdd .= "&postEndTime=$postEndTime";
		}
		if ($adminName) {
			$sqlAdd .= ' AND username2 = ' . S::sqlEscape($adminName);
			$urlAdd .= "&adminName=$adminName";
		}
		if ($fid) {
			$sqlAdd .= ' AND field1 = ' . S::sqlEscape($fid);
			$urlAdd .= "&fid=$fid";
		}
	}
	$urlAdd = urlencode($urlAdd);

	$query = $db->query("SELECT COUNT(*) AS count,username2 AS manager,type FROM pw_adminlog WHERE 1 $sqlAdd GROUP BY username2,type");
	while ($rt = $db->fetch_array($query)) {
		if (!$rt['manager']) continue;
		$admindb[$rt['manager']][$rt['type']] = $rt['count'];
		$admindb[$rt['manager']]['total'] += $rt['count'];
	}
	if ($sortType) {
		function sortByTtpe($a,$b) {
			global $sortType;
			return $a[$sortType] == $b[$sortType] ? 0 : ($a[$sortType] > $b[$sortType] ? -1 : 1);
		}
		uasort($admindb,"sortByTtpe");
		$sort_a[$sortType] = "↓";
	}
	
	if($dataOut){
		$filename = 'teamData_' . get_date($timestamp, 'Ymd');
		$outData = '';
		$i = 1;
		foreach ($admindb as $key => $data) {
			$highlight = $data[highlight] ? $data[highlight] : 0;
			$delete = $data[delete] ? $data[delete] : 0;
			$credit = $data[credit] ? $data[credit] : 0;
			$topped = $data[topped] ? $data[topped] : 0;
			$edit = $data[edit] ? $data[edit] : 0;
			$digest = $data[digest] ? $data[digest] : 0;
			$copy = $data[copy] ? $data[copy] : 0;
			$move = $data[move] ? $data[move] : 0;
			$down = $data[down] ? $data[down] : 0;
			$banuser = $data[banuser] ? $data[banuser] : 0;
			$locked = $data[locked] ? $data[locked] : 0;
			$push = $data[push] ? $data[push] : 0;
			$unite = $data[unite] ? $data[unite] : 0;
			$shield = $data[shield] ? $data[shield] : 0;
			$remind = $data[remind] ? $data[remind] : 0;
			$recycle = $data[recycle] ? $data[recycle] : 0;
			$deluser = $data[deluser] ? $data[deluser] : 0;
			$total = $data[total] ? $data[total] : 0;

			$outData .= $i ."\t";
			$outData .= $key ."\t";
			$outData .= $highlight ."\t";
			$outData .= $delete ."\t";
			$outData .= $credit ."\t";
			$outData .= $topped ."\t";
			$outData .= $edit ."\t";
			$outData .= $digest ."\t";
			$outData .= $copy ."\t";
			$outData .= $move ."\t";
			$outData .= $down ."\t";
			$outData .= $banuser ."\t";
			$outData .= $locked ."\t";
			$outData .= $push ."\t";
			$outData .= $unite ."\t";
			$outData .= $shield ."\t";
			$outData .= $remind ."\t";
			$outData .= $recycle ."\t";
			$outData .= $deluser ."\t";
			$outData .= $total ."\t";
			$outData .= "\r\n";
			$i++;
		}
	    ob_end_clean();
	    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	    header('Cache-control: no-cache');
	    header('Content-Encoding: none');
	    header('Content-Disposition: attachment; filename=' . $filename . ".xls");
	    header("Content-type:application/vnd.ms-excel");
	    header("Content-Transfer-Encoding: binary");
	    header('Content-Length: ' . strlen($outData));
	      
		echo "序号"."\t";
		echo "用户名"."\t";
		echo "加亮"."\t";
		echo "删除"."\t";
		echo "评分"."\t";
		echo "置顶"."\t";
		echo "编辑"."\t";
		echo "精华"."\t";
		echo "复制"."\t";
		echo "移动"."\t";
		echo "压帖"."\t";
		echo "禁言"."\t";
		echo "锁定"."\t";
		echo "提前"."\t";
		echo "合并"."\t";
		echo "屏蔽"."\t";
		echo "提醒"."\t";
		echo "还原帖子"."\t";
		echo "删除用户"."\t";
		echo "总计"."\t";
		echo "\r\n";
	    echo $outData;
	    exit();
	}
	include PrintEot('team');exit;

} elseif ($action == 'sort') {
	require_once(R_P.'require/credit.php');
	//* @include_once pwCache::getPath(D_P.'data/bbscache/tm_config.php');
	pwCache::getData(D_P.'data/bbscache/config.php');
	$_tmconf = unserialize($db_team);

	$hours	 = gmdate('G',$timestamp+$db_timedf*3600);
	$tdtime	 = PwStrtoTime(get_date($timestamp,'Y-m-d'));
	$montime = PwStrtoTime(get_date($timestamp,'Y-m')."-1");

	$gids = 0;
	if (!empty($_tmconf['group'])) {
		$gids = S::sqlImplode($_tmconf['group']);
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
	$query = $db->query("SELECT COUNT(*) AS count,username2 AS manager FROM pw_adminlog WHERE timestamp>".S::sqlEscape($montime)."GROUP BY username2");

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
	include PrintEot('team');exit;

} elseif ($action == 'set') {

	S::gp(array('set','ifopen'));
	$set['param']['opr'] = S::isNum($set['param']['opr']) ? $set['param']['opr'] : 0;
	$set['param']['post'] = S::isNum($set['param']['post']) ? $set['param']['post'] : 0;
	$set['param']['oltime'] = S::isNum($set['param']['oltime']) ? $set['param']['oltime'] : 0;
	$set['eligibility'] && $set['eligibility'] = intval($set['eligibility']);
	$set['msgtitle'] && $set['msgtitle'] = trim(stripslashes($set['msgtitle']));
	$set['msgdata'] && $set['msgdata'] = stripslashes($set['msgdata']);
	$set['arousemsg'] && $set['arousemsg'] = stripslashes($set['arousemsg']);
	
	foreach ($set['wages'] as $k=>$value) {
		if (!in_array($k,$set['group'])) {
			unset($set['wages'][$k]);
		}
		$set['wages'][$k]['money'] && $set['wages'][$k]['money'] = intval($set['wages'][$k]['money']);
		$set['wages'][$k]['rvrc'] && $set['wages'][$k]['rvrc'] = intval($set['wages'][$k]['rvrc']);
		$set['wages'][$k]['credit'] && $set['wages'][$k]['credit'] = intval($set['wages'][$k]['credit']);
		$set['wages'][$k]['currency'] && $set['wages'][$k]['currency'] = intval($set['wages'][$k]['currency']);
		$set['wages'][$k]['1'] && $set['wages'][$k]['1'] = intval($set['wages'][$k]['1']);
	}
	
	foreach ($set['bonus'] as $k=>$v){
		$set['bonus']['money'] && $set['bonus']['money'] = intval($set['bonus']['money']);
		$set['bonus']['rvrc'] && $set['bonus']['rvrc'] = intval($set['bonus']['rvrc']);
		$set['bonus']['credit'] && $set['bonus']['credit'] = intval($set['bonus']['credit']);
		$set['bonus']['currency'] && $set['bonus']['currency'] = intval($set['bonus']['currency']);
		$set['bonus']['1'] && $set['bonus']['1'] = intval($set['bonus']['1']);
	}
	
	$set = serialize($set);
	setConfig('db_team',$set);
	//$db->Update("REPLACE INTO pw_hack SET hk_name='tm_setting',hk_value=".S::sqlEscape($set,false));
	updatecache_c();

	if ($rt['ifopen'] != $ifopen) {
		if ($ifopen && $rt['nexttime'] < $timestamp) {
			adminmsg('请先到计划任务中开启“管理团队工资发放”');
		}
		$db->Update("UPDATE pw_plan SET ifopen=".S::sqlEscape($ifopen)."WHERE filename='team'");
		updatecache_plan();
	}

	adminmsg('operate_success');

} elseif ($_POST['action'] == 'payoff') {

	S::gp(array('paycredit','arouse'));
	//* @include_once pwCache::getPath(D_P.'data/bbscache/tm_config.php');
	pwCache::getData(D_P.'data/bbscache/config.php');
	require_once(R_P.'require/credit.php');
	$_tmconf = unserialize($db_team);
	
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
	$msgdata = S::escapeChar($_tmconf['msgdata']);
	$arousemsg = S::escapeChar($_tmconf['arousemsg']);

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

function getUserManageForums() {
	global $db;
	$query = $db->query("SELECT DISTINCT username2 AS manager,field1 AS fid FROM pw_adminlog");
	$rsOut = $actionLog = array();
	while ($result = $db->fetch_array($query)) {
		$rs[manager] = $result[manager];
		$rs[fid] = $result[fid];
		$rsOut[] = $rs;
	}
	if (!S::isArray($rsOut)) return array();
	extract(pwCache::getData( D_P . 'data/bbscache/forum_cache.php' , false));
	if (!$forum) return array();
	foreach ($rsOut as $v) {
		if(!$forum[$v[fid]][name]) continue;
		$forumName = strip_tags($forum[$v[fid]][name]);
		$actionLog[$v[manager]][] = '<a href="thread.php?fid='.$v[fid].'" alt="'.$forumName.'" title="'.$forumName.'" target="_blank">'.$forum[$v[fid]][name].'</a>';
	}
	return $actionLog;
}
?>
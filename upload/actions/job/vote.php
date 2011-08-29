<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
PostCheck();
$rt = $db->get_one('SELECT t.fid,t.tid,t.postdate,t.lastpost,t.state,t.locked,t.ifcheck,p.* FROM pw_polls p LEFT JOIN pw_threads t ON p.tid=t.tid WHERE p.tid=' . S::sqlEscape($tid));
!$rt && Showmsg('data_error');
@extract($rt);
//得到版块基本信息,版块权限验证

if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}
require_once (R_P . 'require/forum.php');
wind_forumcheck($foruminfo);

//获取管理权限
if ($groupid == '3' || $groupid == '4' || admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid)) {
	$admincheck = 1;
} else {
	$admincheck = 0;
}


//用户组权限验证
$_G['allowvote'] == 0 && Showmsg('job_vote_right');
if ((!$admincheck && $locked > 0) || ($foruminfo['forumset']['lock'] && $timestamp - $postdate > (int) $foruminfo['forumset']['lock'] * 86400 && !$admincheck)) {
	Showmsg('job_vote_lock');
} elseif ($state || ($timelimit && $timestamp - $postdate > $timelimit * 86400)) {
	Showmsg('job_vote_close');
}
if (!$admincheck && $regdatelimit && $winddb['regdate'] > $regdatelimit) {
	$regdatelimit = get_date($regdatelimit, 'Y-m-d');
	Showmsg('job_vote_regdatelimit');
}
S::gp(array(
	'voteid',
	'voteaction'
), 'P');
$votearray = unserialize($voteopts);
!is_array($voteid) && $voteid = explode('|',$voteid);

if (empty($voteid) || !is_array($voteid)) {
	Showmsg('job_vote_sel');
}
$voteid = array_unique($voteid);
$v_sum = count($voteid);
if ($multiple && $v_sum > $mostvotes || !$multiple && $v_sum != 1) {
	Showmsg('job_vote_num');
}
if ($multiple && $leastvotes > 0 && $v_sum < $leastvotes) {
	Showmsg('job_vote_leastnum');
}
if (!$admincheck && $creditlimit) {
	checkCreditLimit($creditlimit);
}
if (!$admincheck && $postnumlimit && $winddb['postnum'] < $postnumlimit) {
	Showmsg('job_vote_postnum');
}
$sql_1 = '';
if ($groupid == 'guest') {
	$windid = $onlineip;
	$winduid = 0;
	$sql_1 = ' AND username=' . S::sqlEscape($windid);
}
$sqlw = 'tid=' . S::sqlEscape($tid) . ' AND uid=' . S::sqlEscape($winduid) . $sql_1;

$sql_2 = ',voters=voters+1';
if ($voteaction == 'modify') {
	if ($_G['edittime'] && ($timestamp - $postdate) > $_G['edittime'] * 60) {
		Showmsg('modify_timelimit');
	} elseif (!$modifiable) {
		Showmsg('vote_not_modify');
	}
	$query = $db->query("SELECT vote FROM pw_voter WHERE $sqlw");
	while ($rt = $db->fetch_array($query)) {
		if (isset($votearray[$rt['vote']])) {
			$votearray[$rt['vote']][1]--;
		}
		$sql_2 = '';
	}
	$db->update("DELETE FROM pw_voter WHERE $sqlw");
} else {
	if ($db->get_value("SELECT COUNT(*) AS SUM FROM pw_voter WHERE $sqlw") > 0) {
		Showmsg('job_havevote');
	}
}
$pwSQL = array();
foreach ($voteid as $k => $id) {
	if (isset($votearray[$id])) {
		$votearray[$id][1]++;
		$pwSQL[] = array(
			$tid,
			$winduid,
			$windid,
			$id,
			$timestamp
		);
	}
}
$voteopts = serialize($votearray);
$db->update('UPDATE pw_polls SET voteopts=' . S::sqlEscape($voteopts, false) . "$sql_2 WHERE tid=" . S::sqlEscape($tid));
if ($pwSQL) {
	$db->update("INSERT INTO pw_voter (tid,uid,username,vote,time) VALUES " . S::sqlMulti($pwSQL, false));
}
if ($locked < 3 && $lastpost < $timestamp) {
	//$db->update('UPDATE pw_threads SET lastpost=' . S::sqlEscape($timestamp) . ' WHERE tid=' . S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('lastpost'=>$timestamp));
	# memcache refresh
	// $threadList = L::loadClass("threadlist", 'forum');
	// $threadList->updateThreadIdsByForumId($fid, $tid);
	Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
}
if ($foruminfo['allowhtm'] == 1) {
	$StaticPage = L::loadClass('StaticPage');
	$StaticPage->update($tid);
}
empty($j_p) && $j_p = "read.php?tid=$tid&ds=1";
refreshto($j_p, defined('AJAX') ? "success\toperate_success" : 'operate_success');

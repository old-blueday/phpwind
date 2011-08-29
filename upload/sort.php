<?php
define('SCR','sort');
require_once('global.php');
$groupid == 'guest' && Showmsg('not_login');
!$_G['allowsort'] && Showmsg('sort_group_right');

require_once(R_P.'require/header.php');
$per = 24;//更新时间，小时单位
$cachenum = 20;//查询数
$cachetime = '';
S::gp(array('action'),'G');

if (empty($action)) {
	if ($db_online) {
		/**
		$userinbbs = $guestinbbs = 0;
		$query = $db->query("SELECT uid!=0 as ifuser,COUNT(*) AS count FROM pw_online GROUP BY uid='0'");
		while($rt = $db->fetch_array($query)){
			if($rt['ifuser']) $userinbbs=$rt['count']; else	$guestinbbs=$rt['count'];
		}**/
		
		$onlineService = L::loadClass('OnlineService', 'user');
		$userinbbs = $onlineService->countOnlineUser();			
		$guestinbbs = $onlineService->countOnlineGuest();	
	} else {
		@include_once (D_P.'data/bbscache/olcache.php');
	}
	$usertotal = $guestinbbs+$userinbbs;
	$bbsinfo = $db->get_one("SELECT id,newmember,totalmember,higholnum,higholtime,tdtcontrol,yposts,hposts FROM pw_bbsinfo WHERE id=1");
	$bbsinfo['higholtime'] = get_date($bbsinfo['higholtime']);
	$rs = $db->get_one("SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
	$topic = $rs['topic']+$rs['subtopic'];
	$article = $rs['article'];
	$tposts = $rs['tposts'];
	if ($bbsinfo['tdtcontrol'] < $tdtime) {
		if ($db_hostweb == 1) {
			//* $db->update("UPDATE pw_bbsinfo SET yposts=".S::sqlEscape($tposts).',tdtcontrol='.S::sqlEscape($tdtime).' WHERE id=1');
			pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('yposts'=>$tposts, 'tdtcontrol'=>$tdtime));
			//* $db->update("UPDATE pw_forumdata SET tpost=0 WHERE tpost<>'0'");
			pwQuery::update('pw_forumdata', 'tpost<>:tpost', array(0), array('tpost'=>'0'));
		}
		if (file_exists(D_P.'data/bbscache/ip_cache.php')) {
			//* P_unlink(D_P.'data/bbscache/ip_cache.php');
			pwCache::deleteData(D_P.'data/bbscache/ip_cache.php');
		}
	}
	require PrintEot('sort');footer();

} elseif ($action == 'ipstate') {

	!$db_ipstates && Showmsg('ipstates_not_open');
	S::gp(array('type','year','month'));
	!$type && $type='month';
	if ($type == 'month') {
		$c_month = get_date($timestamp,'Y-n');
		$c_year = is_numeric($year) ? $year : get_date($timestamp,'Y');
		$p_year = $c_year-1;
		$n_year = $c_year+1;
		$summip = 0;
		$m_ipdb = array();
		$query = $db->query("SELECT month,sum(nums) as nums FROM pw_ipstates WHERE month LIKE ".S::sqlEscape("$c_year%").' GROUP BY month');
		while ($rt = $db->fetch_array($query)) {
			$summip += $rt['nums'];
			$key = substr($rt['month'],strrpos($rt['month'],'-')+1);
			$rt['_month'] = str_replace('-','_',$rt['month']);
			$m_ipdb[$key] = $rt;
		}
		for ($i = 1; $i <= 12; $i++) {
			!$m_ipdb[$i] && $m_ipdb[$i] = array('month'=>$c_year.'-'.$i,'_month'=>$c_year.'_'.$i,'nums'=>'0');
		}
		ksort($m_ipdb);
	} elseif ($type == 'day') {
		$c_month = $month ? str_replace('_','-',$month) : get_date($timestamp,'Y-n');
		list($Y,$M)=explode('-',$c_month);
		if (!is_numeric($Y) || !is_numeric($M)) {
			Showmsg('undefined_action');
		}
		if ($M == 1) {
			$p_month = ($Y-1).'_12';
			$n_month = $Y.'_2';
		} elseif ($M == 12) {
			$p_month = $Y.'_11';
			$n_month = ($Y+1).'_1';
		} else {
			$p_month = $Y.'_'.($M-1);
			$n_month = $Y.'_'.($M+1);
		}
		$sumip  = 0;
		$d_ipdb = array();
		$query  = $db->query("SELECT day,nums FROM pw_ipstates WHERE month=".S::sqlEscape($c_month).' ORDER BY day');
		while ($rt = $db->fetch_array($query)) {
			$sumip += $rt['nums'];
			$key = substr($rt['day'],strrpos($rt['day'],'-')+1);
			$d_ipdb[$key] = $rt;
		}
		for ($i = 1; $i <= get_date(PwStrtoTime($c_month.'-1'),'t'); $i++) {
			!$d_ipdb[$i] && $d_ipdb[$i] = array('day'=>"$c_month-$i",'nums'=>'0');
		}
		ksort($d_ipdb);
	}
	require PrintEot('sort');footer();

} elseif ($action == 'online') {

	//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	//* require_once pwCache::getPath(D_P.'data/bbscache/level.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/level.php');
	
	S::gp(array('page'));
	(!is_numeric($page) || $page<1) && $page=1;

	$ltitle['-1'] = 'Member';
	$threaddb = array();
	if ($db_online) {
		/**
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_online");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"sort.php?action=online&");
		$count = $rt['sum'];
		$query = $db->query("SELECT * FROM pw_online $limit");
		while ($rt = $db->fetch_array($query)) {
			$groupid!=3 && $rt['ip'] = '-';
			$rt['forum']	= $forum[$rt['fid']]['name'];
			$ac	= $rt['forum'] ? substrs(strip_tags($rt['forum']),13) : getLangInfo('action',$rt['action']);
			$rt['action'] = $ac!=$rt['action'] ? $ac : getLangInfo('action','other');
			$rt['lasttime']	= get_date($rt['lastvisit']);
			$rt['group']	= $ltitle[$rt['groupid']];
			if($rt['tid']){
				$rt['atc']	= $rt['tid'];
			}
			if(!$rt['uid']){
				$rt['username'] = $rt['group'] = 'Guest';
			}
			$threaddb[] = $rt;
		}**/
		
		$onlineService = L::loadClass('OnlineService', 'user');
		$allOnline = $onlineService->getAllOnlineWithPaging($page, $db_perpage , $count);		
		$pages = numofpage($count ,$page,ceil($count/$db_perpage),"sort.php?action=online&");
		foreach ($allOnline as $k => $rt){
			$groupid!=3 && $rt['ip'] = '-';
			$rt['forum']	= $forum[$rt['fid']]['name'];
			$ac	= $rt['forum'] ? substrs(strip_tags($rt['forum']),13) : getLangInfo('action',$rt['action']);
			$rt['action'] = $ac!=$rt['action'] ? $ac : getLangInfo('action','other');
			$rt['lasttime']	= get_date($rt['lastvisit']);
			$rt['group']	= $ltitle[$rt['groupid']];
			if($rt['tid']){
				$rt['atc']	= $rt['tid'];
			}
			if(!$rt['uid']){
				$rt['username'] = $rt['group'] = 'Guest';
			}			
			$threaddb[] = $rt;
		}				
	} else {
		require_once(R_P.'require/functions.php');
		$onlinedb = openfile(D_P.'data/bbscache/online.php');
		if (count($onlinedb) == 1) {
			$onlinedb = array();
		} else {
			unset($onlinedb[0]);
		}
		$online_A = $guest_A = array();
		foreach ($onlinedb as $online) {
			if (trim($online)) {
				$detail = explode("\t",$online);
				$online_A[$online] = $detail[1];
			}
		}
		unset($onlinedb);
		@asort($online_A);
		$online_A = @array_keys($online_A);

		$guestdb = openfile(D_P.'data/bbscache/guest.php');
		if (count($guestdb) == 1) {
			$guestdb = array();
		} else {
			unset($guestdb[0]);
		}
		foreach ($guestdb as $online) {
			if (trim($online)) {
				$detail = explode("\t",$online);
				$guest_A[$online] = $detail[1];
			}
		}
		unset($guestdb);
		@asort($guest_A);
		$guest_A = @array_keys($guest_A);
		$online_A = array_merge ($online_A, $guest_A);
		unset($guest_A);
		$count = count($online_A);

		$numofpage = $count%$db_perpage==0 ? floor($count/$db_perpage) : floor($count/$db_perpage)+1;
		$numofpage && $page>$numofpage && $page = $numofpage;
		$pages = numofpage($count,$page,$numofpage,"sort.php?action=online&");
		$start = ($page-1)*$db_perpage;
		$end = min($start+$db_perpage,$count);

		for ($i = $start; $i < $end; $i++) {
			if (!$online_A[$i]) continue;
			$thread = explode("\t",$online_A[$i]);
			if (count($thread) < 10) {
				$thread['username'] = 'Guest';
				$thread['uid']		= 0;
				$thread['ip']		= S::inArray($windid,$manager) ? $thread[0] : "-";
				$thread['group']	= 'Guest';
				$thread['action']	= $thread[4];
				$thread['lasttime'] = $thread[5];
				$thread[2] = str_replace('<FiD>','',$thread[2]);
				$forum[$thread[2]]['name'] && $thread['forum'] = "<a href='thread.php?fid=$thread[2]'>".$forum[$thread[2]]['name']."</a>";
				$thread['atc'] = $thread[3];
			} else {
				$thread['username']	= $thread[0];
				$thread['uid']		= $thread[8];
				$thread['ip']		= S::inArray($windid,$manager) ? $thread[2] : "-";
				$thread['group']	= $ltitle[$thread[5]];
				$thread['action']	= $thread[6];
				$thread['lasttime']	= $thread[7];
				$forum[$thread[3]]['name'] && $thread['forum'] = "<a href='thread.php?fid=$thread[3]'>".$forum[$thread[3]]['name']."</a>";
				$thread['atc'] = $thread[4];
			}
			$threaddb[] = $thread;
		}
	}
	require_once PrintEot('sort');footer();

} elseif ($action == 'member') {

	$_SORTDB = $member = array();

	$array = array('todaypost','rvrc','postnum','onlinetime','monthpost','monoltime','f_num','money', 'digests', 'currency', 'credit');
	foreach ($_CREDITDB as $key => $value) {
		array_push($array,$key);
	}
	if ($db_ifpwcache & 1) {
		$element = L::loadClass('element');
		$element->setDefaultNum($cachenum);
		$_SORTDB = $element->getAllUserSort();
	} else {
		$cachetime = pwFilemtime(D_P."data/bbscache/member_sort.php");
		if (!$per || !file_exists(D_P."data/bbscache/member_sort.php") || ($timestamp-$cachetime>$per*3600)) {
			$step = $_GET['step'] ? intval($_GET['step']) : 0;
			if ($array[$step]) {
				//* @include pwCache::getPath(D_P."data/bbscache/member_tmp.php");
				pwCache::getData(D_P."data/bbscache/member_tmp.php");
				$element = L::loadClass('element');
				$element->setDefaultNum($cachenum);
				$member = $element->userSort($array[$step],0,false);
				unset($_SORTDB[$array[$step]]);
				foreach ($member as $v ) {
					$_SORTDB[$array[$step]][] = array($v['addition']['uid'],$v['title'],$v['value']);
				}
				$step++;
				pwCache::setData(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".pw_var_export($_SORTDB).";\r\n?>");
				refreshto("sort.php?action=member&step=$step",'update_cache');
			} else {
				//* @include pwCache::getPath(D_P."data/bbscache/member_tmp.php");
				pwCache::getData(D_P."data/bbscache/member_tmp.php");
				pwCache::writeover(D_P.'data/bbscache/member_sort.php',"<?php\r\n\$_SORTDB=".pw_var_export($_SORTDB).";\r\n?>");
				//* P_unlink(D_P."data/bbscache/member_tmp.php");
				pwCache::deleteData(D_P."data/bbscache/member_tmp.php");
				refreshto("sort.php?action=member",'update_cache');
			}
		}
		@include pwCache::getPath(D_P."data/bbscache/member_sort.php");
		$cachetime=get_date($cachetime+$per*3600);		
	}
	$show_url="u.php?uid";
	require PrintEot('sort');footer();

} elseif ($action == 'forum') {
	$cachetime = pwFilemtime(D_P."data/bbscache/forum_sort.php");
	if (!$per || !file_exists(D_P."data/bbscache/forum_sort.php") || ($timestamp-$cachetime>$per*3600)) {
		$element = L::loadClass('element');
		$element->setDefaultNum($cachenum);
		$_SORTDB=$_sort=array();
		$_sort = $element->forumSort('topic');
		foreach ($_sort as $key => $value) {
			$_sort[$key] = array($value['addition']['fid'],$value['title'],$value['value']);
		}
		$_SORTDB['topic'] = $_sort;
		$_sort = $element->forumSort('article');
		foreach ($_sort as $key => $value) {
			$_sort[$key] = array($value['addition']['fid'],$value['title'],$value['value']);
		}
		$_SORTDB['article'] = $_sort;
		$_sort = $element->forumSort('tpost');
		foreach ($_sort as $key => $value) {
			$_sort[$key] = array($value['addition']['fid'],$value['title'],$value['value']);
		}
		$_SORTDB['tpost'] = $_sort;
		pwCache::writeover(D_P.'data/bbscache/forum_sort.php',"<?php\r\n\$_FORUMDB=".pw_var_export($_SORTDB).";\r\n?>");
	} else {
		include pwCache::getPath(D_P."data/bbscache/forum_sort.php");
		$_SORTDB = $_FORUMDB;
		unset($_FORUMDB);
	}
	$show_url="thread.php?fid";
	$cachetime=get_date($cachetime+$per*3600);
	require PrintEot('sort');footer();
} elseif ($action == 'article') {
	$_SORTDB = $article = array();
	$array = array('digest','replies','hits');
	$cachetime = pwFilemtime(D_P."data/bbscache/article_sort.php");
	if (!$per || !file_exists(D_P."data/bbscache/article_sort.php") || ($timestamp-$cachetime>$per*3600)) {
		$element = L::loadClass('element');
		$element->setDefaultNum($cachenum);
		if (($db_ifpwcache & 2) || ($db_ifpwcache & 32)) {
			$article = $element->digestSubject();
			foreach ($article as $key => $value) {
				$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
			}
			$_SORTDB['digest'] = $article;
			$article = $element->replySort();
			foreach ($article as $key => $value) {
				$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
			}
			$_SORTDB['replies'] = $article;
			$article = $element->hitSort();
			foreach ($article as $key => $value) {
				$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
			}
			$_SORTDB['hits'] = $article;
			pwCache::writeover(D_P.'data/bbscache/article_sort.php',"<?php\r\n\$_SORTDB=".pw_var_export($_SORTDB).";\r\n?>");	
		} else {
			$step = $_GET['step'] ? intval($_GET['step']) : 0;
			if ($array[$step]) {
				//* @include pwCache::getPath(D_P."data/bbscache/article_tmp.php");
				pwCache::getData(D_P."data/bbscache/article_tmp.php");
				unset($_SORTDB[$array[$step]]);
				switch ($array[$step]) {
					case 'digest':
						$article = $element->digestSubject();
						foreach ($article as $key => $value) {
							$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
						}
						break;
					case 'replies':
						$article = $element->replySort();
						foreach ($article as $key => $value) {
							$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
						}
						break;
					case 'hits':
						$article = $element->hitSort();
						foreach ($article as $key => $value) {
							$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
						}
						break;
				}
				$_SORTDB[$array[$step]] = $article;
				$step++;
				pwCache::setData(D_P.'data/bbscache/article_tmp.php',"<?php\r\n\$_SORTDB=".pw_var_export($_SORTDB).";\r\n?>");
				refreshto("sort.php?action=article&step=$step",'update_cache');
			} else {
				//* @include pwCache::getPath(D_P."data/bbscache/article_tmp.php");
				pwCache::getData(D_P."data/bbscache/article_tmp.php");
				pwCache::writeover(D_P.'data/bbscache/article_sort.php',"<?php\r\n\$_SORTDB=".pw_var_export($_SORTDB).";\r\n?>");
				//* P_unlink(D_P."data/bbscache/article_tmp.php");
				pwCache::deleteData(D_P."data/bbscache/article_tmp.php");
				refreshto("sort.php?action=article",'update_cache');
			}
		}
	} else {
		include pwCache::getPath(D_P."data/bbscache/article_sort.php");
	}
	$cachetime=get_date($cachetime+$per*3600);
	$show_url = "read.php?tid";
	require PrintEot('sort');footer();

} elseif ($action == 'team') {

	/*
	$lockfile = D_P.'data/bbscache/lock_team.txt';
	$fp = fopen($lockfile,'wb+');
	flock($fp,LOCK_EX);
	*/
	$cachetime = pwFilemtime(D_P."data/bbscache/team_sort.php");
	if ((!$per || $timestamp - $cachetime > $per * 3600) && procLock('sort_team')) {
		//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
		pwCache::getData(D_P.'data/bbscache/level.php');
		$uids  = $gids = array();
		$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype='system' AND gid NOT IN(5,6,7)");
		while ($rt = $db->fetch_array($query)) {
			$gids[] = $rt['gid'];
		}
		$teamdb = $tfdb = $fadmindb = $forumdb = $admin_a = $men = array();
		$query = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE cms!='1' AND forumadmin!=''");
		while ($rt = $db->fetch_array($query)) {
			$fuids = explode(',',substr($rt['forumadmin'],1,-1));
			foreach ($fuids as $key => $val) {
				if ($val) {
					$tfdb[$rt['fid']][] = $val;
					$admin_a[] = $val;
				}
			}
		}
		if($gids){
			$gids = S::sqlImplode($gids);
			$query = $db->query("SELECT m.uid,m.username,m.groupid,m.groups,m.memberid,md.lastvisit,md.lastpost,md.postnum,md.rvrc,md.money,md.onlinetime,md.monoltime,md.monthpost FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.groupid IN($gids)");
			while ($rt = $db->fetch_array($query)) {
				$men[$rt['uid']] = $rt;
			}
		}
		if (!empty($admin_a)) {
			$admin_a = array_unique($admin_a);
			$fname = S::sqlImplode($admin_a);
			$query = $db->query("SELECT m.uid,m.username,m.groupid,m.groups,m.memberid,md.lastvisit,md.lastpost,md.postnum,md.rvrc,md.money,md.onlinetime,md.monoltime,md.monthpost FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.username IN($fname)");
			while ($rt = $db->fetch_array($query)) {
				$men[$rt['uid']] = $rt;
			}
		}
		foreach ($men as $key => $rt) {
			$rt['leavedays'] = floor(($timestamp-$rt['lastvisit'])/86400);
			$rt['lastpost'] < $montime && $rt['monthpost'] = 0;
			$rt['lastpost']		= $rt['lastpost'] ? get_date($rt['lastpost']) : '';
			$rt['onlinetime']	= round($rt['onlinetime']/3600,2);
			$rt['monoltime']	= $rt['lastvisit'] > $montime ? round($rt['monoltime']/3600,2) : 0;
			$rt['systitle']		= $ltitle[$rt['groupid']];
			$rt['memtitle']		= $ltitle[$rt['memberid']];
			$rt['rvrc']			= floor($rt['rvrc']/10);
			if ($rt['groupid'] != 5 && $rt['groupid'] != '-1') {
				$teamdb[] = $rt;
			}
			if ($rt['groupid'] == 5 || strpos($rt['groups'],',5,') !== false) {
				$rt['hits'] = $rt['post'] = 0;
				$fadmindb[$rt['uid']] = $rt;
				$uids[] = $rt['uid'];
			}
		}

		if ($uids) {
			$uids = S::sqlImplode($uids);
			$query = $db->query("SELECT COUNT(*) AS post,SUM(hits) AS count,authorid FROM pw_threads WHERE postdate>".S::sqlEscape($montime)." AND authorid IN($uids) GROUP BY authorid");
			while ($rt = $db->fetch_array($query)) {
				$fadmindb[$rt['authorid']]['hits'] = $rt['count'];
				$fadmindb[$rt['authorid']]['post'] = $rt['post'];
			}
		}
		foreach ($tfdb as $fid => $value) {
			foreach ($fadmindb as $key => $val){
				if (in_array($val['username'],$value)) {
					$forumdb[$fid][$val['uid']] = $val;
				}
			}
		}
		pwCache::setData(D_P.'data/bbscache/team_sort.php', "<?php\r\n\$teamdb=" . pw_var_export($teamdb) . ";\r\n\$forumdb=" . pw_var_export($forumdb) . ";\n?>");
		touch(D_P.'data/bbscache/team_sort.php');
		procUnLock('sort_team');
	} else {
		//* include pwCache::getPath(D_P.'data/bbscache/team_sort.php');
		pwCache::getData(D_P.'data/bbscache/team_sort.php');
	}
	//fclose($fp);
	$cachetime = get_date($cachetime + $per * 3600);
	//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');

	require PrintEot('sort');footer();

} elseif ($action == 'admin') {
	$baseurl = 'sort.php?action=admin';
	S::gp(array('postStartDate','postEndDate','adminName','type','step'));

	$monthFile = S::escapePath(D_P."data/bbscache/admin_sort_".$montime.".php");
	if (file_exists($monthFile)) $cachetime = pwFilemtime($monthFile);
	$startDate = $endDate = $montime;
	//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
	pwCache::getData(D_P.'data/bbscache/level.php');

	if (!$per || !file_exists($monthFile) || ($cachetime && $timestamp-$cachetime>$per*3600)) {
		$gids  = array('0');
		$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype='system'");
		while ($rt = $db->fetch_array($query)) {
			$gids[] = $rt['gid'];
		}
		$gids = S::sqlImplode($gids);
		$admindb = array();
		$query = $db->query("SELECT uid,username,groupid,groups FROM pw_members WHERE groupid IN($gids) ORDER BY groupid");
		while ($rt = $db->fetch_array($query)) {
			$admindb[$rt['username']] = array('uid'=>$rt['uid'],'gid'=>$rt['groupid'],'total'=>0);
		}
		$query = $db->query("SELECT COUNT(*) AS count,username2 AS manager,type FROM pw_adminlog WHERE timestamp>".S::sqlEscape($montime)." GROUP BY username2,type");
		while ($rt = $db->fetch_array($query)) {
			if (isset($admindb[$rt['manager']])) {
				$admindb[$rt['manager']][$rt['type']] = $rt['count'];
				$admindb[$rt['manager']]['total'] += $rt['count'];
			}
		}
		pwCache::setData($monthFile,"<?php\n\$admindb=".pw_var_export($admindb).";\n?>");
		touch($monthFile);
	} 
	
	$cachetime = get_date($cachetime+$per*3600);
	$sort_a = $dateSelect = array();
	$fg = opendir(D_P.'data/bbscache/');
	while ($othermonth=readdir($fg)) {
		if (eregi("^admin_sort_[0-9]+\.php$",$othermonth)) {
			$fileTime = pwFilemtime(D_P.'data/bbscache/'.$othermonth);
			$outTime = get_date($fileTime,'Y-m');
			$keyTime = PwStrtoTime($outTime.'-1');
			$dateSelect[$keyTime] = $outTime;
			$tmpDateTime[] = $keyTime;
		}
	}closedir($fg);
	ksort($dateSelect);
	$urlAdd = "";
	if ($step) {
		$endDate = $postEndDate ? PwStrtoTime($postEndDate.'-1') : 0;
		$startDate = $postStartDate ? PwStrtoTime($postStartDate.'-1') : 0;

		if (!$startDate || !$endDate) Showmsg('请选择时间');
		if ($startDate > $endDate) Showmsg('起始时间大于截止时间');
		if ($endDate > strtoTime("1 year",$startDate)) Showmsg('查询时间跨度不能大于一年');
		if (!S::isArray($tmpDateTime)) Showmsg('缓存文件不存在');
		
		$urlAdd .= "&step=2&postStartDate=$postStartDate&postEndDate=$postEndDate";
		if ($adminName) $urlAdd .= "&adminName=$adminName";

		$tmpDateTime = array_unique($tmpDateTime);
		$dateTime = array();
		foreach ($tmpDateTime as $v) {
			if ($v < $startDate || $v > $endDate) continue;
			$dateTime[] = $v;
		}

/*		for ($i=0;$i<12;$i++) {
			$nowTime = PwStrtoTime(get_date(strtoTime("+$i month",$startDate),'Y-m').'-1');
			if ($nowTime > $endDate) break;
			$dateTime[$nowTime] = $nowTime;
		}*/
		
		function arrayAdd(&$a,$b){
			foreach ((array)$b as $k=>$v){
				if(!isset($a[$k])){
					$a[$k] = $v;
					continue;
				} else {
					if (is_array($v)){
						arrayAdd($a[$k],$v);
					} else {
						if($k == 'uid' || $k == 'gid') continue;
						$a[$k] += $v;
					}
				}
			}
			return $a;
		}
		
		$countData = array();	
		foreach ($dateTime as $time) {
			$dataFile = D_P."data/bbscache/admin_sort_".$time.".php";
			//* if (file_exists($dataFile)) include_once S::escapePath($dataFile);
			if (file_exists($dataFile)) extract(pwCache::getData(S::escapePath($dataFile), false));
			$admindb = arrayAdd($countData,$admindb);
		}
		if ($adminName) $admindb = array_key_exists($adminName,$admindb) ? array($adminName => $admindb[$adminName]) : array();
		
	} else {
		//* include S::escapePath($monthFile);
		extract(pwCache::getData($monthFile, false));
	}

	if (!empty($type)) {
		function cmp($a,$b) {
			global $type;
			return $a[$type] == $b[$type] ? 0 : ($a[$type] > $b[$type] ? -1 : 1);
		}
		uasort($admindb,"cmp");
		$sort_a[$type] = "class='link_down'";
	}

	require PrintEot('sort');footer();

} elseif ($action == 'delsort') {

	PostCheck();
	S::gp(array('month'));
	(!$month || !is_numeric($month) || $groupid!='3') && Showmsg('undefined_action');
	if (file_exists(D_P.'data/bbscache/admin_sort_'.$month.'.php')) {
		//* P_unlink(D_P.'data/bbscache/admin_sort_'.$month.'.php');
		pwCache::deleteData(D_P.'data/bbscache/admin_sort_'.$month.'.php');
	}
	refreshto("sort.php?action=admin",'operate_success');
} elseif ($action == 'favor') {
	$cachetime = pwFilemtime(D_P."data/bbscache/favor_sort.php");
	if (!$per || !file_exists(D_P."data/bbscache/favor_sort.php") || ($timestamp-$cachetime>$per*3600)) {
		$element = L::loadClass('element');
		$element->setDefaultNum(50);
		$_sort = array();
		$_SORTDB = $element->hotFavorsort();

		pwCache::writeover(D_P.'data/bbscache/favor_sort.php',"<?php\r\n\$_FAVORS=".pw_var_export($_SORTDB).";\r\n?>");
	} else {
		include pwCache::getPath(D_P."data/bbscache/favor_sort.php");
		$_SORTDB = $_FAVORS;
		unset($_FAVORS);
	}
	$cachetime=get_date($cachetime+$per*3600);
	require PrintEot('sort');footer();
}
?>
<?php
define('SCR', 'index');
require_once ('global.php');
if (checkFromWap() && $db_wapifopen) ObHeader("m/index.php");
$cateid = (int) S::getGP('cateid');
$m = S::getGP('m');
if ($db_channeldomain && $secdomain = array_search($pwServer['HTTP_HOST'], $db_channeldomain)) {
	$m = 'area';
	$db_bbsurl = $_mainUrl;
	$alias = $secdomain;
	define('HTML_CHANNEL', 1);
}
selectMode($m);
if (defined('M_P') && file_exists(M_P . 'index.php')) {
	pwCache::getData(S::escapePath(D_P . 'data/bbscache/' . $db_mode . '_config.php'));
	if (file_exists(M_P . 'require/core.php')) {
		require_once (M_P . 'require/core.php');
	}
	$basename = "index.php?m=$m";
	require_once (M_P . 'index.php');
	exit();
}

pwCache::getData(D_P . 'data/bbscache/cache_index.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');

//notice
$noticedb = array();
foreach ($notice_A as $value) {
	if ($value['startdate'] <= $timestamp && (!$value['enddate'] || $value['enddate'] >= $timestamp)) {
		$value['startdate'] = $value['stime'] ? $value['stime'] : get_date($value['startdate'], 'y-m-d');
		!$value['url'] && $value['url'] = 'notice.php#' . $value['aid'];
		$noticedb[$value['aid']] = $value;
	}
}
$notice_A = $noticedb;
unset($noticedb);
$topics = $article = $tposts = 0;

//SEO Settings
bbsSeoSettings('index');

$newpic = (int) GetCookie('newpic');
$forumdb = $catedb = $showsub = array();
$c_htm = 0;
$sqlwhere = '';
$updateDaily = 1;

if ($cateid > 0) {
	$catestyle = $forum[$cateid]['style'];
	($catestyle && file_exists(D_P . "data/style/$catestyle.php")) && $skin = $catestyle;
	$_seo = array('title' => $forum[$cateid]['title'], 'metaDescription' => $forum[$cateid]['metadescrip'], 'metaKeywords' => $forum[$cateid]['keywords']);
	bbsSeoSettings('thread', $_seo, $forum[$cateid]['name'], '');
	$sqlwhere = 'AND (f.fid=' . S::sqlEscape($cateid) . ' OR f.fup=' . S::sqlEscape($cateid) . ')';
	$updateDaily = 0;
} elseif ($db_forumdir) {
	require_once (R_P . 'require/dirname.php');
}
require_once (R_P . 'require/header.php');
!$db_showcms && $sqlwhere .= " AND f.cms!='1'";

/*The app client*/
if ($db_siteappkey && ($db_apps_list['17']['status'] == 1 || is_array($db_threadconfig))) {
	$appclient = L::loadClass('appclient');
	if (is_array($db_threadconfig)) {
		$threadright = (array) $appclient->getThreadRight();
	}
}
/*The app client*/

if ($cookie_deploy = $_COOKIE['deploy']) {
	$deployfids = array_flip((array)explode("\t", $cookie_deploy));
	unset($cookie_deploy);
} else {
	$deployfids = array();
}

if (!$cateid && Perf::checkMemcache()) {
	$_cacheService = Perf::getCacheService();
	$_tmpForums = $_cacheService->get('all_forums_info');
}

if (!Perf::checkMemcache() || !$_tmpForums){
	$query = $db->query("SELECT f.fid,f.name,f.type,f.childid,f.fup,f.logo,f.descrip,f.metadescrip,f.forumadmin,f.across,f.allowhtm,f.password,f.allowvisit,f.showsub,f.ifcms,fd.tpost,fd.topic,fd.article,fd.subtopic,fd.top1,fd.lastpost FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.ifcms!=2 $sqlwhere ORDER BY f.vieworder");
	$_tmpForums = array();
	while ($forums = $db->fetch_array($query)) {
		$_tmpForums[$forums['fid']] = $forums;
	}
	!$cateid && Perf::checkMemcache() &&  $_cacheService->set('all_forums_info', $_tmpForums, 300);
}

foreach ($_tmpForums as $forums) {
	if ($forums['type'] === 'forum') {
		if ($forums['showsub'] && $forums['childid']) {
			$showsub[$forums['fid']] = '';
		}
		$forums['topics'] = $forums['topic'] + $forums['subtopic'];
		$article += $forums['article'];
		$topics += $forums['topics'];
		$tposts += $forums['tpost'];
		$forums['au'] = $forums['admin'] = '';
		if (S::inArray($windid, $manager) || (!$forums['password'] && (!$forums['allowvisit'] || allowcheck($forums['allowvisit'], $groupid, $winddb['groups'], $forums['fid'], $winddb['visit'])))) {
			list($forums['t'], $forums['au'], $forums['newtitle'], $forums['ft']) = explode("\t", $forums['lastpost']);
			$forums['pic']      = $newpic < $forums['newtitle'] && ($forums['newtitle'] + $db_newtime > $timestamp) ? 'new' : 'old';
			$forums['newtitle'] = get_date($forums['newtitle']);
			$forums['t']        = substrs($forums['t'], 26);
		} elseif ($forum[$forums['fid']]['f_type'] === 'hidden') {
			if ($forums['password'] && allowcheck($forums['allowvisit'], $groupid, $winddb['groups'], $forums['fid'], $winddb['visit'])) {
				$forums['pic'] = 'lock';
			} else {
				if (!S::inArray($windid, $manager)) {
					continue;
				}
			}
		} else {
			$forums['pic'] = 'lock';
		}
		$forums['allowhtm'] == 1 && $c_htm = 1;
		if ($db_indexfmlogo == 2) {
			if (!empty($forums['logo']) && strpos($forums['logo'], 'http://') !== false) {
				$forums['logo'] = $forums[logo];
			} elseif (!empty($forums['logo'])) {
				$forumLogo = geturl($forums[logo]);
				$forums['logo'] = $forumLogo[0];
			}
		} elseif ($db_indexfmlogo == 1 && file_exists("$imgdir/$stylepath/forumlogo/$forums[fid].gif")) {
			$forums['logo'] = "$imgpath/$stylepath/forumlogo/$forums[fid].gif";
		} else {
			$forums['logo'] = '';
		}
		if ($forums['forumadmin']) {
			$forumadmin = explode(',', $forums['forumadmin']);
			foreach ($forumadmin as $value) {
				if ($value) {
					if (!$db_adminshow) {
						$forums['admin'] .= '<a href="u.php?username=' . rawurlencode($value) . '" class=" _cardshow" target="_blank" data-card-url="pw_ajax.php?action=smallcard&type=showcard&username='.rawurlencode($value).'" data-card-key='.$value.'>'.$value.'</a> ';
					} else {
						$forums['admin'] .= "<option value=\"$value\">$value</option>";
					}
				}
			}
		}
		
		/*The app client*/
		if ($db_siteappkey && $db_apps_list['17']['status'] == 1) {
			$forums['forumappinfo'] = $appclient->showForumappinfo($forums['fid'], 'forum_erect,forum_across', '17');
		}
		/*The app client*/
		
		$forumdb[$forums['fup']][] = $forums;
	} elseif ($forums['type'] === 'category') {
		if (isset($deployfids[$forums['fid']])) {
			$forums['deploy_img'] = 'open';
			$forums['tbody_style'] = 'none';
			$forums['admin'] = '';
		} else {
			$forums['deploy_img'] = 'fold';
			$forums['tbody_style'] = $forums['admin'] = '';
		}
		if ($forums['forumadmin']) {
			$forumadmin = explode(',', $forums['forumadmin']);
			foreach ($forumadmin as $key => $value) {
				if ($value) {
					if ($key == 10) {
						$forums['admin'] .= '...';
						break;
					}
					$forums['admin'] .= '<a href="u.php?username=' . rawurlencode($value) . '" class="cfont _cardshow" target="_blank" data-card-url="pw_ajax.php?action=smallcard&type=showcard&username='.rawurlencode($value).'" data-card-key='.$value.'>' . $value . '</a> ';
				}
			}
		}
		$catedb[] = $forums;
	}
} 
$db->free_result($query);
// View sub
if (!empty($showsub)) {
	foreach ($forum as $value) {
		if (isset($showsub[$value['fup']]) && $value['f_type'] != 'hidden' && $value['ifcms'] != 2) {
			$showsub[$value['fup']] .= ($showsub[$value['fup']] ? ' | ' : '') . "<a href=\"thread.php?fid=$value[fid]\">$value[name]</a>";
		}
	}
}
unset($forums, $forum, $db_showcms);
//info deploy
if (isset($deployfids['info'])) {
	$cate_img = 'open';
	$cate_info = 'none';
} else {
	$cate_img = 'fold';
	$cate_info = '';
}
// update birth day
if ($db_indexshowbirth) {
	$brithcache = '';
	require_once (R_P . 'require/birth.php');
}
// get bbsinfo
if(Perf::checkMemcache()){
	$_cacheService = Perf::getCacheService();
	$_bbsInfoResult = $_cacheService->get('bbsinfo_id_1');
	if(!$_bbsInfoResult){
		$_bbsInfoService = L::loadClass('BbsInfoService', 'forum'); 
		$_bbsInfoResult = $_bbsInfoService->getBbsInfoById(1);
	}
	extract($_bbsInfoResult);
}else{
	extract($db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1"));
}
$newmember = '<a href="u.php?username=' . rawurlencode($newmember) . '" target="_blank" class=" _cardshow" data-card-url="pw_ajax.php?action=smallcard&type=showcard&username='.rawurlencode($newmember).'" data-card-key='.$newmember.'>' . $newmember . '</a>';

$article += $o_post;
$topics  += $o_post;
$tposts  += $o_tpost;

// online users
Update_ol();
if (empty($db_online)) {
	include_once (D_P . 'data/bbscache/olcache.php');
} else {
	$userinbbs = $guestinbbs = 0;	
	if (count($online_info =  explode("\t", GetCookie('online_info'))) == 3 && $timestamp - $online_info[0] < 60){
		list(, $userinbbs, $guestinbbs) = $online_info;
	}else {
		$onlineService = L::loadClass('OnlineService', 'user');
		$userinbbs = $onlineService->countOnlineUser();
		$guestinbbs = $onlineService->countOnlineGuest();
		Cookie('online_info', $timestamp . "\t" . $userinbbs . "\t" . $guestinbbs);
	}
}

if ($last_statistictime == 0 || get_date($timestamp,'G') - get_date($last_statistictime,'G') > 1 ||  $timestamp - $last_statistictime > 3600) {
	$stasticsService = L::loadClass('Statistics', 'datanalyse');
	$stasticsService->updateOnlineInfo();
}

$usertotal = $guestinbbs + $userinbbs;
if ($db_indexonline) {
	S::gp(array('online'));
	empty($online) && $online = GetCookie('online');
	if ($online == 'yes') {
		if ($usertotal > 2000 && !S::inArray($windid, $manager)) {
			//$online = 'no';
			Cookie('online', 'no');
		} else {
			$index_whosonline = '';
			$db_online = intval($db_online);
			Cookie('online', $online);
			include_once S::escapePath(R_P . "require/online_{$db_online}.php");
		}
	}
	if ($online == 'no') Cookie('online', 'no');
}
$showgroup = $db_showgroup ? explode(',', $db_showgroup) : array();
// Share union
if ($db_indexmqshare && $sharelink[1]) {
	$sharelink[1] = "<marquee scrolldelay=\"100\" scrollamount=\"4\" onmouseout=\"if (document.all!=null){this.start()}\" onmouseover=\"if (document.all!=null){this.stop()}\" behavior=\"alternate\">$sharelink[1]</marquee>";
}

if ($db_hostweb == 1 && $updateDaily && $tdtcontrol < $tdtime && !defined('M_P')) {
	require_once (R_P . 'require/updateforum.php');
	updateshortcut();
	pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('yposts' => $tposts, 'tdtcontrol' => $tdtime, 'o_tpost' => 0));
	pwQuery::update('pw_forumdata', 'tpost<>:tpost', array(0), array('tpost'=>0));
}

// update posts hits
if ($c_htm || $db_hits_store == 2) {
	$db_hithour == 0 && $db_hithour = 4;
	$hit_wtime = $hit_control * $db_hithour;
	$hit_wtime > 24 && $hit_wtime = 0;
	$hitsize = @filesize(D_P . 'data/bbscache/hits.txt');
	if ($hitsize && ($hitsize > 1024 || ($timestamp - $hit_tdtime) > $hit_wtime * 3600) && procLock('hitupdate')) {
		require_once (R_P . 'require/hitupdate.php');
		procUnLock('hitupdate');
	}
}

if ($higholnum < $usertotal) {
	pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('higholnum' => $usertotal, 'higholtime' => $timestamp));
	$higholnum = $usertotal;
}
if ($hposts < $tposts) {
	pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('hposts'=>$tposts));
	$hposts = $tposts;
}
$mostinbbstime = get_date($higholtime);
if (!$ol_offset && $db_onlinelmt != 0 && $usertotal >= $db_onlinelmt) {
	Cookie('ol_offset', '', 0);
	Showmsg('most_online');
}
if ($plantime && $timestamp > $plantime && procLock('task')) {
	require_once (R_P . 'require/task.php');
	procUnLock('task');
}
require_once PrintEot('index');
CloudWind::yunSetCookie(SCR);
footer();
?>
<?php
define('SCR','read');
require_once('global.php');
L::loadClass('forum', 'forum', false);
//require_once(R_P.'require/forum.php');
require_once(R_P.'require/bbscode.php');
include_once pwCache::getPath(D_P.'data/bbscache/cache_read.php',true);
S::gp(array('fpage','toread'), 'GP', 2);
S::gp(array('uid'));

$_showSmallImg = 5;
$ordertype = S::getGP('ordertype');
$fieldadd = $tablaadd = $sqladd = $fastpost = $special = $ifmagic = $urladd = $fieldinfo = $tableinfo = '';
$_uids    = $_pids = $colony = array();
$page = S::getGP('page');
$page < 1 && $page != 'e' && $page = 1;
if (Perf::checkMemcache()) {
	$_cacheService = Perf::getCacheService();
	$_thread = $_cacheService->get('thread_tid_' . $tid);
	if ($page < 2) {
		$_thread && $_tmsg = $_cacheService->get('thread_tmsg_tid_' . $tid);
		$read = ($_thread && $_tmsg) ? array_merge($_thread, $_tmsg) : false;
	} else {
		$read = $_thread;
	}
	if(!$read){
		$_cacheService = Perf::gatherCache('pw_threads');
		$read = ($page>1) ? $_cacheService->getThreadByThreadId($tid) : $_cacheService->getThreadAndTmsgByThreadId($tid);	
	}
} else {
	if (($page>1)) {
		$read = $db->get_one("SELECT * FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
	} else {
		$read = $db->get_one("SELECT t.* ,tm.* FROM pw_threads t LEFT JOIN ".S::sqlMetadata(GetTtable($tid))." tm ON t.tid=tm.tid WHERE t.tid=" . S::sqlEscape($tid));
	}
}
!$read && Showmsg('illegal_tid');

if (getstatus($read['tpcstatus'], 1)) {#帖子是否来自群组
	$_sql_sel = $_sql_tab = '';
	if ($winduid) {
		$_sql_sel = ',cm.id AS ifcyer,cm.ifadmin';
		$_sql_tab = ' LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid AND cm.uid=' . S::sqlEscape($winduid);
	}
	$colony = $db->get_one("SELECT a.cyid,c.cname,c.ifopen,c.admin,c.speciallevel,c.commonlevel,c.classid,c.viewtype{$_sql_sel} FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id{$_sql_tab} WHERE tid=" . S::sqlEscape($tid));
	if ($colony && (!$colony['classid'] || $colony['viewtype'] == '2')) {
		ObHeader("apps.php?q=group&a=read&cyid={$colony[cyid]}&tid=$tid");
	}
}

$_uids[$read['authorid']] = 'UID_'.$read['authorid'];#用户

list($fid,$ptable,$ifcheck,$openIndex) = array($read['fid'],$read['ptable'],$read['ifcheck'],getstatus($read['tpcstatus'], 2));
$pw_posts = GetPtable($ptable);

/*The app client*/
if ($db_siteappkey && ($db_apps_list['17']['status'] == 1 || is_array($db_threadconfig))) {
	$appclient = L::loadClass('appclient');
	if ($db_apps_list['17']['status'] == 1) {
		$forumappinfo = array();
		$forumappinfo = $appclient->showForumappinfo($fid,'read','17');
	}
	if (is_array($db_threadconfig)) {
		$threadright = array();
		$threadright = $appclient->getThreadRight();
	}
}
/*The app client*/

$pwforum = new PwForum($fid);
if (!$pwforum->isForum()) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$forumset =& $pwforum->forumset;

if (!S::inArray($windid, $manager)) {
	$pwforum->forumcheck($winddb, $groupid);
}
$pwforum->setForumStyle();

if (!$foruminfo['allowvisit'] && $_G['allowread']==0 && $_COOKIE) {
	Showmsg('read_group_right');
}
/**************************************/

if (is_array($customfield)) {
	foreach ($customfield as $key => $value) {
		$fieldinfo .= ',mi.field_'.(int)$value['id'];
	}
}
if ($db_union) {
	$db_union = explode("\t",stripslashes($db_union));
	$db_union[0] && $db_hackdb = array_merge((array)$db_hackdb,(array)unserialize($db_union[0]));
}
$showfield	= array();
$custominfo = $db_union[7] ? (array)unserialize($db_union[7]) : array();
foreach ($custominfo as $key => $val) {
	if (substr($val[3],2,1)=='1') {
		$showfield[] = $key;
	}
}
!empty($showfield) && $fieldinfo .= ',mi.customdata';
$fieldinfo && $tableinfo = 'LEFT JOIN pw_memberinfo mi ON mi.uid=m.uid';

/**************************************/

//帖子浏览及管理权限
$isGM = $isBM = $admincheck = $managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
$pwSystem = array();
if ($groupid != 'guest') {
	$isGM = S::inArray($windid,$manager);
	$isBM = $pwforum->isBM($windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
	if (!$isGM) {#非创始人权限获取
		$pwSystem = pwRights($isBM);
		if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'] || $pwSystem['unite'] || $pwSystem['pingcp'] || $pwSystem['areapush'] || $pwSystem['split'])) {
			$managecheck = 1;
		}
		$pwPostHide = $pwSystem['posthide'];
		$pwSellHide = $pwSystem['sellhide'];
		$pwEncodeHide = $pwSystem['encodehide'];
	} else {
		$managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
	}
}

//版块查看权限
if ($foruminfo['allowread'] && !$admincheck && !allowcheck($foruminfo['allowread'],$groupid,$winddb['groups'])) {
	Showmsg('forum_read_right');
}
if (!$admincheck) {
	$pwforum->creditcheck($winddb, $groupid);#积分限制浏览
	$pwforum->sellcheck($winduid);#出售版块
}
if ($read['ifcheck'] == 0 && !$isGM && $windid != $read['author'] && !$pwSystem['viewcheck']) {
	Showmsg('read_check');
}
if ($read['locked']%3==2 && !$isGM && !$pwSystem['viewclose']) {
	Showmsg('read_locked');
}
unset($S_sql,$J_sql,$foruminfo['forumset']);

if ($colony) {#帖子是否来自群组
	if (!$colony['ifopen'] && !$admincheck && (!$colony['ifcyer'] || $colony['ifadmin'] == -1)) {
		Showmsg('该群组话题内容仅对成员开放!');
	}
	$read = $read + array('cyid' => $colony['cyid'], 'cname' => $colony['cname']);
	$cyid = $colony['cyid'];
	$ifColonyAdmin = ($colony['ifadmin'] == '1' || $colony['admin'] == $windid) ? 1 : 0; //群组管理员
	$ifColonyRight = $SYSTEM['colonyright']; //有群组管理权限的用户组
	$ifForumColonyRight = $pwSystem['forumcolonyright']; //版主开启群组管理权限
	$colonyManageCheck = ($ifColonyAdmin || $ifColonyRight || $isGM || $ifForumColonyRight) ? 1 : 0;
	if ($ifColonyAdmin) {//根据等级获取群组管理员所具有的权限
		$colonyLevel = $colony['speciallevel'] ? $colony['speciallevel'] : $colony['commonlevel'];
		if ($colonyLevel && $colonyRights = $db->get_one("SELECT * FROM pw_cnlevel WHERE id=" . S::sqlEscape($colonyLevel))) {
			$colonyTopicAdmin = $colonyRights['topicadmin'] ? unserialize($colonyRights['topicadmin']) : array();
		}
	}
}

$creditnames = pwCreditNames();
$creditunits = pwCreditUnits();

/**** 帖子排序  版块-帖子*****/
if ($ordertype) {
	/*performance*/
	$cookie_rorder = GetCookie('rorder');
	if ($cookie_rorder && count($cookie_rorder) > 10) {
		Cookie("rorder",'',0);
	}
	Cookie("rorder[$tid]",$ordertype);
	$orderby = $ordertype == 'desc' ? 'desc' : 'asc';
	unset($cookie_rorder);
} else {
	$replayOrder = GetCookie('rorder');
	if ($replayOrder && is_array($replayOrder) && array_key_exists($tid,$replayOrder)) {
		//$replayOrder = GetCookie('rorder');
		$orderby = $replayOrder[$tid] == 'desc' ? 'desc' : 'asc';
	} else {
		$forumset['replayorder'] && $orderby = $forumset['replayorder'] == '1' ? 'asc' : 'desc';
		$threadorder = bindec(getstatus($read['tpcstatus'],4).getstatus($read['tpcstatus'],3));
		$threadorder && $threadorder != 3 && $orderby = $threadorder == '1' ? 'asc' : 'desc';
	}
	!$orderby && $orderby = 'asc';
	$ordertype = $orderby;
	unset($replayOrder);
}

/******** end *********/

$rewardtype = null; /*** 悬赏 ***/
//过滤匿名作者相关动态
if ($db_threadrelated && $forumset['ifrelated'] && !($read['anonymous'] && in_array($forumset['relatedcon'],array('ownpost', 'owndigest', 'ownhits', 'ownreply', 'oinfo')))) {
	if ($forumset['relatedcon'] == 'custom') {
		$relatedb = $forumset['relatedcustom'];
	} else {
		$relatedb = threadrelated($forumset['relatedcon']);
	}
}

list(,,$downloadmoney,$downloadimg) = explode("\t",$forumset['uploadset']);
$subject  = $read['subject'];
$authorid = $read['authorid'];
if ($read['ifmagic'] && $db_windmagic) {
	$ifmagic = 1;
	list($magicid,$magicname) = explode("\t",$read['magic']);
}
if (isset($uid) && $read['replies'] > 0) {#只看作者回复数统计
	$uid = intval($uid);
	$rt = $db->get_one("SELECT COUNT(*) AS n FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND authorid=" . S::sqlEscape($uid) . " AND anonymous='0' AND ifcheck='1'");
	$read['replies'] = $rt['n'];
	$sqladd = 'AND t.authorid=' . S::sqlEscape($uid) . " AND t.anonymous='0'";
	$urladd = "&uid=$uid";
	$openIndex = false;
}
if ($openIndex) {#高楼帖子索引
	$count = 1 + $db->get_value("SELECT max(floor) FROM pw_postsfloor WHERE tid =". S::sqlEscape($tid));
} else {
	$count = $read['replies']+1;
}
$topped_count = $read['topreplays'];

//帖子来源分类
$read_category = getThreadType();

//门户阅读方式
if ($foruminfo['ifcms'] && $db_modes['area']['ifopen']) {
	S::gp(array('viewbbs'));
	if (!$viewbbs) {
		require_once R_P. 'mode/area/area_read.php';exit;
	}
	$viewbbs = $viewbbs ? "&viewbbs=$viewbbs" : "";
}
if ($winddb['p_num']) {
	$db_readperpage = $winddb['p_num'];
} elseif ($forumset['readnum']) {
	$db_readperpage = $forumset['readnum'];
}
$numofpage = ceil(($count+$topped_count)/$db_readperpage);
if ($page == 'e' || $page > $numofpage) {
	$numofpage == 1 && $page > 1 && ObHeader("read.php?tid=$tid&toread=$toread");
	//$numofpage > 1 && $page == 'e' && $orderby == 'desc' && ObHeader("read.php?tid=$tid&page=1&toread=$toread");
	$page = ($page == 'e' && $orderby == 'desc') ? 1 : $numofpage;
}

$page == 1 && $read['aid'] && $_pids['tpc'] = 0;#附件 TODO 首页才读

//当前位置导航
list($guidename, $forumtitle) = $pwforum->getTitle();
//list($guidename,$forumtitle) = getforumtitle(forumindex($foruminfo['fup'],1),1);
$forumtitle = '|'.$forumtitle;


/** Labs Code By cn0zz
if ($db_htmifopen) {
	$link_ref_canonical = ($_GET['fpage'] || $_GET['uid'] || $_GET['skinco']) ? "read{$db_dir}tid-$tid".($page>1 ? "-page-$page" : '').$db_ext : '';
} else {
	$link_ref_canonical = ($_GET['fpage'] || $_GET['uid'] || $_GET['skinco']) ? "read.php?tid=$tid".($page>1 ? "&page=$page" : '') : '';
}
*/

/*SEO*/
$_summary = strip_tags(stripWindCode($read['content']));
$_summary = str_replace(array('"', "\n", "\r", '&nbsp;', '&amp;', '&lt;', '', '&#160;'), '', $_summary);
$_summary = substrs($_summary, 255);
if ($ifConvert) {
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$_summary = $wordsfb->convert($_summary);
}

bbsSeoSettings('read','',$foruminfo['name'],$foruminfo['topictype'][$read['type']],$read['subject'],$read['tags'],$_summary);
/*SEO*/

require_once(R_P.'require/header.php');

require_once(R_P.'require/showimg.php');
Update_ol();

$readdb = $authorids = array();

//主题印戳
if($forumset['overprint']){
	$overPrintService = L::loadclass("overprint", 'forum');
	$overprint = $overPrintService->getOverPrintIcon($read['overprint']);
}

if ($read['modelid'] || $foruminfo['modelid']) {
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($read);
}
if ($read['special'] > 20 || $foruminfo['pcid']) {
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($read);
}
if ($read['special'] == 8 || $foruminfo['actmids']) {
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);
}
//分类信息主题帖
if ($read['modelid']) {
	$modelid = $read['modelid'];
	$topicvalue = $postTopic->getTopicvalue($read['modelid']);
	$initSearchHtml = $postTopic->initSearchHtml($read['modelid']);

	foreach ($postTopic->topicmodeldb as $key => $value) {
		if ($value['cateid'] == $foruminfo['cateid']){
			$modeldb[$key] = $value;
		}
	}
}

//团购主题帖
if ($read['special'] > 20) {
	$pcid = $read['special'] - 20;
	list($fieldone,$topicvalue) = $postCate->getCatevalue($pcid);
	$initSearchHtml = $postCate->initSearchHtml($pcid);
	is_array($fieldone) && $read = array_merge($read,$fieldone);
	$isadminright = $postCate->getAdminright($pcid,$read['authorid']);
	list($pcuid) = $postCate->getViewright($pcid,$tid);
	$payway = $fieldone['payway'];
	$ifend = $read['endtime'] < $timestamp ? 1 : 0;
}

//活动主题帖
if ($read['special'] == 8) {
	$actmid = $postActForBbs->getActmid($tid);//获取子分类id
	list($topicvalue,$activityValue) = $postActForBbs->getActValue($actmid);
	$orderMemberList = $postActForBbs->getOrderMemberList($actmid,$tid,$fid,$activityValue['paymethod'],$read['authorid']);//报名列表
	$actRecommendHtml = $postActForBbs->getActRecommendHtml($actmid,$tid,$activityValue['recommend']);//推荐
}

//特殊主题帖
if ($read['special'] == 1 && ($foruminfo['allowtype'] & 2) && ($page == 1 || $numofpage == 1)) {#投票帖
	require_once(R_P.'require/readvote.php');
//} elseif ($read['special'] == 2 && ($foruminfo['allowtype'] & 4) && ($page == 1 || $numofpage == 1)) {#活动帖
} elseif ($read['special'] == 2 && ($page == 1 || $numofpage == 1)) {#活动帖
	require_once(R_P.'require/readact.php');
} elseif ($read['special'] == 3 && ($foruminfo['allowtype'] & 8)) {#悬赏帖
	require_once(R_P.'require/readrew.php');
} elseif ($read['special'] == 4 && ($foruminfo['allowtype'] & 16)) {#交易帖
	require_once(R_P.'require/readtrade.php');
} elseif ($read['special'] == 5 && ($foruminfo['allowtype'] & 32)) {#辩论帖
	require_once(R_P.'require/readdebate.php');
}

//帖子回复短消息提醒
if ($db_replysitemail && $read['authorid'] == $winduid && $read['ifmail'] == 4) {
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('ifmail' => 2));
}

if ($page == 1) {
	$read['pid'] = 'tpc';
	if ($foruminfo['allowhtm'] == 1) {#纯静态页面生成
		$htmurl = $db_readdir.'/'.$fid.'/'.date('ym',$read['postdate']).'/'.$read['tid'].'.html';
		if (!$foruminfo['cms'] && !$toread && file_exists(R_P.$htmurl)) {
			ObHeader("$R_url/$htmurl");
		}
	}
	$readdb[] = $read;
}
$toread && $urladd .= "&toread=$toread";
$fpage > 1 && $urladd .= "&fpage=$fpage";
$pages = numofpage($count+$topped_count,$page,$numofpage,"read.php?tid=$tid{$urladd}$viewbbs&");
$tpc_locked = $read['locked']%3<>0 ? 1 : 0;

//更新帖子点击
if (!$db_hithour) {
	//$db->update('UPDATE pw_threads SET hits=hits+1 WHERE tid='.S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('hits=hits+1')));
} else {
	pwCache::setData(D_P.'data/bbscache/hits.txt',$tid."\t", false, 'ab');
}

//帖子浏览记录
$readlog = str_replace(",$tid,",',',GetCookie('readlog'));
$readlog.= ($readlog ? '' : ',').$tid.',';
substr_count($readlog,',')>11 && $readlog = preg_replace("/[\d]+\,/i",'',$readlog,3);
Cookie('readlog',$readlog);

$favortitle = str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"),$subject);
$db_bbsname_a = addslashes($db_bbsname);#模版内用到

if($readdb[0]['cyid']){
	$guidename .= "<em>&gt;</em><a href=\"thread.php?cyid={$readdb[0][cyid]}\">".$readdb[0]['cname']."</a>";
}
$guidename .= "<em>&gt;</em><a href=\"read.php?tid=$tid{$viewbbs}\">".substrs($subject,30)."</a>";

//帖内置顶相关处理
if ($topped_count) {
	$topped_page_num = $db_readperpage;
	$start_limit = (int)($page-1)*$db_readperpage - 1;
	if ($start_limit < 0) {
		$topped_page_num += $start_limit;
		$start_limit = 0;
	}
	$topped_count - $start_limit < $db_readperpage && $topped_page_num = $topped_count - $start_limit;
	$topped_page_num = $topped_page_num < 0 ? 0 : $topped_page_num;
	if ($topped_count > $start_limit) {
		$limit = S::sqlLimit($start_limit,$topped_page_num);
		$query = $db->query("SELECT t.floor, p.* $fieldadd FROM pw_poststopped t
			LEFT JOIN $pw_posts p ON t.pid = p.pid $tablaadd
			WHERE t.tid = ".S::sqlEscape($tid)." AND t.fid = '0' AND t.pid != '0' AND p.ifcheck = '1' ORDER BY t.uptime desc $limit");
		while ($rd = $db->fetch_array($query)) {
			$_uids[$rd['authorid']] = 'UID_'.$rd['authorid'];
			$rd['aid'] && $_pids[$rd['pid']] = $rd['pid'];
			$rd['istop'] = "topped";
			$_page = ceil(($rd['floor'] + 1 + $topped_count)/$db_readperpage);
			$rd['jumpurl'] = "read.php?tid=$tid&page=$_page#".$rd['pid'];
			//$rd['remindinfo'] = '';
			$readdb[] = $rd;
		}
	}
}
list($replies,$hits) = array(intval($read['replies']),intval($read['hits']));
//帖子回复信息
if ($read['replies'] > 0 && $topped_page_num < $db_readperpage) {
	if ($openIndex) {#高楼索引处理
		$readnum = $db_readperpage;
		$start_limit = (int)($page-1)*$db_readperpage-1;
		if ($start_limit < 0) {
			$readnum += $start_limit;
			$start_limit = 0 ;
		}
		$start_limit = $start_limit-$topped_count <= 0 ? 0 : $start_limit-$topped_count;
		$end = $start_limit + $readnum - $topped_page_num;
		$sql_floor = " AND f.floor > " . $start_limit ." AND f.floor <= ".$end." ";
		$query = $db->query("SELECT f.pid FROM pw_postsfloor f WHERE f.tid = ". S::sqlEscape($tid) ." $sql_floor ORDER BY f.floor $orderby");
		while ($rt = $db->fetch_array($query)) {
			$postIds[] = $rt['pid'];
		}
		if ($postIds) {
			$postIds && $sql_postId = " AND t.pid IN ( ". S::sqlImplode($postIds,false) ." ) ";
			$query = $db->query("SELECT t.* $fieldadd FROM $pw_posts t $tablaadd WHERE t.tid=".S::sqlEscape($tid)." $sql_postId $sqladd");
			while ($read = $db->fetch_array($query)) {
				if ($read['ifcheck']!='1') {
					$read['subject'] = '';
					$read['content'] = getLangInfo('bbscode','post_unchecked');
				}
				$_uids[$read['authorid']] = 'UID_'.$read['authorid'];
				$read['aid'] && $_pids[$read['pid']] = $read['pid'];
				$read['istop'] = strpos($read['remindinfo'],getLangInfo('bbscode','read_topped_tag')) !== false ? 'top' : '';
				$currentPostsId[] = $read['pid'];
				$currentPosts[$read['pid']] = $read;
			}
			foreach ($postIds as $key => $value) {
				if (in_array($value,$currentPostsId)) {
					$readdb[] = $currentPosts[$value];
				}else{
					$readdb[] = array('postdate'=>'N','content'=>getLangInfo('bbscode','post_deleted'));
				}
			}
		}
	} else {#正常分页
		$readnum	 = $db_readperpage;
		$orderby = $orderby != 'desc' ? 'asc' : 'desc';
		$pageinverse = $page > 20 && $page > ceil($numofpage/2) ? true : false;
		if ($pageinverse) {
			$start_limit = $count-($page)*$db_readperpage;
			$orderby = $orderby != 'desc' ? 'desc' : 'asc';
			$order = $rewardtype != null ? "t.ifreward ASC,t.postdate $orderby" : "t.postdate $orderby";
		} else {
			$start_limit = ($page-1)*$db_readperpage-1;
			$order = $rewardtype != null ? "t.ifreward DESC,t.postdate $orderby" : "t.postdate $orderby";
		}
		if ($start_limit < 0) {
			$readnum += $start_limit;
			$start_limit = 0;
		}
		$start_limit = $pageinverse ? ($start_limit+$topped_count) : ($start_limit-$topped_count <= 0 ? 0 : $start_limit-$topped_count);
		$limit = S::sqlLimit($start_limit,($readnum-$topped_page_num));
		$query = $db->query("SELECT t.* $fieldadd FROM $pw_posts t $tablaadd WHERE t.tid=".S::sqlEscape($tid)." AND t.ifcheck='1' $sqladd ORDER BY $order $limit");
		while ($read = $db->fetch_array($query)) {
			$_uids[$read['authorid']] = 'UID_'.$read['authorid'];
			$read['aid'] && $_pids[$read['pid']] = $read['pid'];
			$read['istop'] = strpos($read['remindinfo'],getLangInfo('bbscode','read_topped_tag')) !== false ? 'top' : '';
			$readdb[] = $read;
		}
	}
	$db->free_result($query);
	$pageinverse && $readdb = array_reverse($readdb);
}
//读取帖子及回复的附件信息
$attachdb = $pwMembers = $colonydb = $customdb = array();
if ($_pids) {
	$query = $db->query('SELECT * FROM pw_attachs WHERE tid='.S::sqlEscape($tid)."AND pid IN (".S::sqlImplode($_pids).")");
	while($rt=$db->fetch_array($query)){
		if ($rt['pid'] == '0') $rt['pid'] = 'tpc';
		$attachdb[$rt['pid']][$rt['aid']] = $rt;
	}
}

//游客浏览权限控制
if ($forumset['viewpic'] && !$winduid && $attachdb) {
	$viewpic = 1;
}

$showCustom = 0;
if($db_showcustom && is_array($db_showcustom)){
	foreach ($db_showcustom as $key => $value) {
		is_numeric($value) && $showCustom = 1;
	}
}
//读取用户信息

if ($_uids) {
	$_userIds = array_keys($_uids);
	if (perf::checkMemcache()){
		$_cacheService = Perf::gatherCache('pw_members');
		$pwMembers = $tableinfo ? $_cacheService->getAllByUserIds($_userIds, true, true, true) : $_cacheService->getAllByUserIds($_userIds, true, true);
		$showCustom && $customdb = $_cacheService->getMemberCreditByUserIds($_userIds);
		$db_showcolony && $colonydb = $_cacheService->getCmemberAndColonyByUserIds($_userIds);	
		//为了兼容原来版本中的查询字段取别名 'icon as micon'
		if (S::isArray($pwMembers)){
			foreach ($pwMembers as $k=>$v){
				$pwMembers[$k]['micon'] = $pwMembers[$k]['icon'];
				unset($pwMembers[$k]['icon']);
			}
		}
	} else {
		$_dbCacheService = Perf::gatherCache('pw_membersdbcache');
		list($pwMembers, $customdb, $colonydb) = $_dbCacheService->getUserDBCacheByUserIds($_userIds, $showCustom, $db_showcolony, $showfield);
		/**
		$skey = array();
		foreach ($_uids as $key=>$value) {
			$skey[$value] = $key;
			$db_showcolony && $skey['UID_GROUP_'.$key] = $key;
			$showCustom && $skey['UID_CREDIT_'.$key] = $key;
		}
		
		$_cache = getDatastore();
		$arrValues = $_cache->get(array_keys($skey));
	
		$tmpUIDs = $tmpGROUPs = $tmpGROUPs = $tmpCacheData = $tmpColonydb = $tmpCustomdb = array();
		foreach ($skey as $key=>$value) {
			$prefix = substr($key,0,strrpos($key,'_'));
	
			switch ($prefix) {
				case 'UID' :
					if (!isset($arrValues[$key])) {
						$tmpUIDs[$key] = $value;
						$tmpCacheData[$key] = '';
					} else {
						$pwMembers[$value] = $arrValues[$key];
					}
					break;
				case 'UID_CREDIT' :
					if (!isset($arrValues[$key])) {
						$tmpCREDITs[$key] = $value;
						$tmpCustomdb[$key] = '';
					} else {
						$customdb[$value] = $arrValues[$key];
					}
					break;
				case 'UID_GROUP' :
					if (!isset($arrValues[$key])) {
						$tmpGROUPs[$key] = $value;
						$tmpColonydb[$key] = '';
					} else {
						$colonydb[$value] = $arrValues[$key];
					}
					break;
			}
		}
		if ($db_showcolony && $tmpGROUPs) {#会员群组信息
			$query = $db->query("SELECT c.uid,cy.id,cy.cname"
								. " FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid"
								. " WHERE c.uid IN(".S::sqlImplode($tmpGROUPs,false).") AND c.ifadmin!='-1'");
			while ($rt = $db->fetch_array($query)) {
				$colonydb[$rt['uid']] = $tmpColonydb['UID_GROUP_'.$rt['uid']] = $rt;
			}
			is_object($_cache) && $_cache->update($tmpColonydb,3600);
			$db->free_result($query);
		}
	
		if ($showCustom && $tmpCREDITs) {#自定义积分显示
			$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN(".S::sqlImplode($tmpCREDITs,false).")");
			while ($rt = $db->fetch_array($query)) {
				$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
				$tmpCustomdb['UID_CREDIT_'.$rt['uid']][$rt['cid']] = $rt['value'];
			}
			is_object($_cache) && $_cache->update($tmpCustomdb,3600);
			$db->free_result($query);
		}
	
		if ($tmpUIDs) {#会员信息
			$query = $db->query("SELECT m.uid,m.username,m.gender,m.oicq,m.aliww,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.lastvisit,md.onlinetime,md.starttime $fieldinfo FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid $tableinfo WHERE m.uid IN (".S::sqlImplode($tmpUIDs,false).") ");
			while ($rt = $db->fetch_array($query)) {
				is_array($pwMembers[$rt['uid']]) ? $pwMembers[$rt['uid']] += $rt : $pwMembers[$rt['uid']] = $rt;
				$tmpCacheData['UID_'.$rt['uid']] = $rt;
			}
			is_object($_cache) && $_cache->update($tmpCacheData,3600);
			$db->free_result($query);
		}
		unset($skey,$_uids,$_cache,$tmpUIDs,$tmpCREDITs,$tmpGROUPs,$tmpColonydb,$tmpCustomdb,$tmpCacheData);
		**/
	}
}

//用户禁言及词语过滤
$bandb = $pwforum->forumBan($pwMembers);
$start_limit = ($page == 1 || $start_limit < 0)? 0 : $start_limit + 1;

//帖子详细内容
$ping_logs = array();
$pageinverse && $start_limit += $readnum - 1;

$sellAttachs = array();

foreach ($readdb as $key => $read) {
	$read = array_merge((array)$read,(array)$pwMembers[$read['authorid']]);
	isset($bandb[$read['authorid']]) && $read['groupid'] = 6;
	if ($read['istop'] == 'topped') {
		$readdb[$key] = viewread($read,'');
	} else {
		if( $pageinverse) {
			$readdb[$key] = viewread($read,$start_limit--);
		} else {
			$readdb[$key] = viewread($read,$start_limit++);
		}
	}
	if ($db_mode == 'area') {
		$db_menuinit .= ",'td_read_".$read['pid']."':'menu_read_".$read['pid']."'";
	}
}
unset($_cache,$sign,$ltitle,$lpic,$lneed,$_G['right'],$_MEDALDB,$fieldadd,$tablaadd,$read,$order,$readnum,$pwMembers,$attachdb);
//快速回复
if ($groupid != 'guest' && !($tpc_locked && !$SYSTEM['replylock']) && ($admincheck || !$foruminfo['allowrp'] || allowcheck($foruminfo['allowrp'],$groupid,$winddb['groups'],$fid,$winddb['reply']))) {
	$psot_sta = 'reply';//control the faster reply
	$titletop1= substrs('Re:'.str_replace('&nbsp;',' ',$subject),$db_titlemax-3);
	$fastpost = 'fastpost';
	$db_forcetype = 0;

	if (!allowcheck($foruminfo['allowrp'],$groupid,$winddb['groups'],$fid,$winddb['reply'])) {
		$fastpost = '';
	} else {
		$fastpost = 'fastpost';
	}
	!$foruminfo['allowrp'] && $_G['allowrp'] && $fastpost = 'fastpost';

} elseif ($groupid == 'guest' && !$tpc_locked) {//显示快速回复表单
    $fastpost = 'fastpost';
    $psot_sta = 'reply';
    $titletop1= substrs('Re:'.str_replace('&nbsp;',' ',$subject),$db_titlemax-3);
    $db_forcetype = 0;
    if ((!$_G['allowrp'] && !$foruminfo['allowrp']) || $foruminfo['allowrp']) {
		$anonymity = true;
    }
}

$postUrl = 'post.php?fid=' . $fid;
if ($readdb[0]['cyid']) {
	$db_menuinit .= ",'td_admin' : 'menu_admin'";
	$postUrl .= '&cyid=' . $readdb[0]['cyid'];
} else {
	$db_menuinit .= ",'td_post' : 'menu_post','td_post1' : 'menu_post','td_admin' : 'menu_admin'";
}

//allowtype onoff

if ($foruminfo['allowtype'] && (($foruminfo['allowtype'] & 1) || ($foruminfo['allowtype'] & 2 && $_G['allownewvote']) || ($foruminfo['allowtype'] & 4 && $_G['allowactive']) || ($foruminfo['allowtype'] & 8 && $_G['allowreward'])|| ($foruminfo['allowtype'] & 16) || $foruminfo['allowtype'] & 32 && $_G['allowdebate'])) {
	$N_allowtypeopen = true;
} else {
	$N_allowtypeopen = false;
}

//分类信息
if ($foruminfo['modelid']) {
	$modelids = explode(",",$foruminfo['modelid']);
	$N_allowtypeopen = true;
}

//团购
if ($foruminfo['pcid']) {
	$pcids = explode(",",$foruminfo['pcid']);
	$N_allowtypeopen = true;
}

//活动
if ($foruminfo['actmids']) {
	$actmids = explode(",",$foruminfo['actmids']);
	$firstactmid = 0;
	foreach ($actmids as $value) {
		if(isset($postActForBbs->activitymodeldb[$value]) && $postActForBbs->activitymodeldb[$value]['ifable'] && $postActForBbs->activitycatedb[$postActForBbs->activitymodeldb[$value]['actid']]['ifable']){
			$firstactmid = $value;
			break;
		}
	}
	$N_allowtypeopen = true;
}

$msg_guide = $pwforum->headguide($guidename);
//$msg_guide = headguide($guidename);
unset($fourm,$guidename);

//评价功能开启
$rateSets = unserialize($db_ratepower);
if (!$forumset['rate'] && $rateSets[1] && isset($db_hackdb['rate'])) {
	list($noAjax,$objectid,$typeid,$elementid) = array(TRUE,$tid,1,'vote_box');
	require_once R_P . 'hack/rate/index.php';
}

if ($ping_logs) {
	$pingService = L::loadClass("ping", 'forum');
	$ping_logs = $pingService->getPingLogs($tid, $ping_logs);
} else {
	$ping_logs = array();
}

require_once PrintEot('read');
footer();

function viewread($read,$start_limit) {
	global $db,$_G,$isGM,$pwSystem,$groupid,$attach_url,$winduid,$tablecolor,$tpc_author,$tpc_buy,$tpc_pid,$tpc_tag,$count,$orderby,$pageinverse,$timestamp,$db_onlinetime,$attachdir,$attachpath,$readcolorone,$readcolortwo,$lpic,$ltitle,$imgpath,$db_ipfrom,$db_showonline,$stylepath,$db_windpost,$db_windpic,$db_signwindcode,$fid,$tid,$pid,$md_ifopen,$_MEDALDB,$rewardtype,$db_shield,$db_iftag,$viewpic;
	global $ping_logs, $buyAids,$creditdb;
	if ($read['istop'] == 'topped') {
		$read['lou'] = $read['floor'];
	} else {
		$read['lou'] = ($orderby != 'desc'|| $start_limit == 0) ? $start_limit : $count - $start_limit;
	}
	$read['jupend'] = $start_limit==$count-1 ? "<a name=a></a><a name=$read[pid]></a>" : "<a name=$read[pid]></a>";
	$orderby == 'desc' && ($read['jupend'] = $start_limit == 1 ? "<a name=a></a><a name=$read[pid]></a>" : "<a name=$read[pid]></a>");
	$tpc_buy = $read['buy'];
	$tpc_pid = $read['pid'];
	$tpc_tag = array();
	$tpc_shield = 0;

	$read['ifsign']<2 && $read['content'] = str_replace("\n","<br />",$read['content']);

	if ($read['anonymous']) {
		$anonymous = (!$isGM && $winduid != $read['authorid'] && !$pwSystem['anonyhide']);
		$read['anonymousname'] = $GLOBALS['db_anonymousname'];
	} else {
		$anonymous = false;
		$read['anonymousname'] = $read['username'];
	}
	$read['ipfrom'] = $db_ipfrom==1 && $_G['viewipfrom'] ? (trim($read['ipfrom']) == 'Unknown' ? '未知IP' : $read['ipfrom']) : '';
	$read['ip'] = ($isGM || $pwSystem['viewip']) ? 'IP:'.$read['userip'] : '';

	if ($read['groupid'] && !$anonymous) {
		$read['groupid'] == '-1' && $read['groupid'] = $read['memberid'];
		!array_key_exists($read['groupid'],(array)$lpic) && $read['groupid'] = 8;
		$read['lpic']		= $lpic[$read['groupid']];
		$read['level']		= $ltitle[$read['groupid']];
		$read['regdate']	= get_date($read['regdate'],"Y-m-d");
		$read['lastlogin']	= get_date($read['lastvisit'],"Y-m-d");
		$read['rvrc']		= floor($read['rvrc']/10);
		$read['author']		= $read['username'];
		$tpc_author			= $read['author'];

		if (!empty($GLOBALS['showfield'])) {
			$customdata = $read['customdata'] ? (array)unserialize($read['customdata']) : array();
			$read['customdata'] = array();
			foreach ($customdata as $key => $val) {
				if ($val && in_array($key,$GLOBALS['showfield'])) {
					$read['customdata'][$key] = $val;
				}
			}
		}
		$read['ontime'] = (int)($read['onlinetime']/3600);
		$read['groupid'] == 6 && $read['honor'] = '';

		if ($read['groupid'] <> 6 && ($read['ifsign'] == 1 || $read['ifsign'] == 3)) {
			global $sign;
			if (!$sign[$read['author']]) {
				global $db_signmoney,$db_signgroup,$tdtime,$db_signcurtype;
				if ($db_signmoney && strpos($db_signgroup,",$read[groupid],") !== false && (!getstatus($read['userstatus'], PW_USERSTATUS_SHOWSIGN) || !$read['starttime'] || $read[$db_signcurtype] < (($tdtime-$read['starttime'])/86400)*$db_signmoney)) {
					$read['signature'] = '';
				} else {
					if ($db_signwindcode && getstatus($read['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
						if ($GLOBALS['gp_right'][$read['groupid']]['imgwidth'] && $GLOBALS['gp_right'][$read['groupid']]['imgheight']) {
							$db_windpic['picwidth']  = $GLOBALS['gp_right'][$read['groupid']]['imgwidth'];
							$db_windpic['picheight'] = $GLOBALS['gp_right'][$read['groupid']]['imgheight'];
						}
						if ($GLOBALS['gp_right'][$read['groupid']]['fontsize']) {
							$db_windpic['size'] = $GLOBALS['gp_right'][$read['groupid']]['fontsize'];
						}
						$read['signature'] = convert($read['signature'],$db_windpic,2);
					}
					$read['signature'] = str_replace("\n","<br />",$read['signature']);
				}
				$sign[$read['author']] = $read['signature'];
			} else {
				$read['signature'] = $sign[$read['author']];
			}
		} else {
			$read['signature'] = '';
		}
	} else {
		$read['lpic']   = '8';
		$read['level']  = $read['digests']   = $read['postnum'] = $read['money'] = $read['currency'] = '*';
		$read['rvrc']	= $read['lastlogin'] = $read['credit']  = $read['regdate'] = '*';
		$read['honor']  = $read['signature'] = $read['micon']   = $read['aliww']   = '';
		if ($anonymous) {
			$read['oicq']		= $read['ip'] = $read['medals'] = $read['ipfrom'] = '';
			$read['author']		= $GLOBALS['db_anonymousname'];
			$read['authorid']	= 0;
			foreach ($GLOBALS['customfield'] as $key => $val) {
				$field = "field_".(int)$val['id'];
				$read[$field] = '*';
			}
		}
	}
	list($read['face'],,$httpWidth,$httpHeight,,,,$read['facesize']) = showfacedesign($read['micon'], true, 'm');
	//list(,,$originalWidth,$originalHeight,,) = explode('|', $read['micon']);
	if ($httpWidth > $GLOBALS['db_imgwidth'] || $httpHeight > $GLOBALS['db_imgheight']) {
		$read['facesize'] = ' width="' . $GLOBALS['db_imgwidth'] . '" height="' . $GLOBALS['db_imgheight'] . '"';
	}
	list($read['posttime'],$read['postdate']) = getLastDate($read['postdate']);
	$read['mark'] = $read['reward'] = $read['tag'] = NULL;
	if ($read['ifmark']) {
		$ping_logs[$read['pid']] = $read['ifmark'];
	}
	if ($rewardtype != null) {
		if ($read['lou'] == 0 || $read['ifreward'] > 0 || ($rewardtype == '0' && $winduid == $GLOBALS['authorid'] && $winduid != $read['authorid'])) {
			$read['reward'] = Getrewhtml($read['lou'],$read['ifreward'],$read['pid']);
		}
	}
	if ($read['icon']) {
		$read['icon'] = "<img src=\"$imgpath/post/emotion/$read[icon].gif\" align=\"absmiddle\" />";
	} else{
		$read['icon'] = '';
	}
	if ($md_ifopen && $read['medals']) {
		$read['medals'] = getMedals($read['authorid'], $read['medals']) . '<br />';
	} else {
		$read['medals'] = '';
	}
	$read['leaveword'] && $read['content'] .= leaveword($read['leaveword'],$read['pid']);
	if ($db_iftag && $read['tags']) {
		list($read['tag'], $tpc_tag) = getReadTag($read['tags']);
	}
	if ($read['ifshield'] || $read['groupid'] == 6 && $db_shield) {
		if ($read['ifshield'] == 2) {
			$read['content'] = shield('shield_del_article');
			$read['subject'] = '';
			$tpc_shield = 1;
		} else {
			if ($groupid == '3') {
				$read['subject'] = shield('shield_title');
			} else {
				$read['content'] = shield($read['ifshield'] ? 'shield_article' : 'ban_article');
				$read['subject'] = '';
				$tpc_shield = 1;
			}
		}
		$read['icon'] = '';
	}
	if (!$tpc_shield) {
		$aids = array();
		if ($read['aid']) {
			$attachs = $GLOBALS['attachdb'][$read['pid']];
			$read['ifhide'] > 0 && ifpost($tid) >= 1 && $read['ifhide'] = 0;
			if (is_array($attachs) && !$read['ifhide']) {
				$aids = attachment($read['content']);
			}
		}
		if ($read['ifwordsfb'] != $GLOBALS['db_wordsfb']) {
			$read['content'] = wordsConvert($read['content'], array(
				'id'	=> ($tpc_pid == 'tpc') ? $tid : $tpc_pid,
				'type'	=> ($tpc_pid == 'tpc') ? 'topic' : 'posts',
				'code'	=> $read['ifwordsfb']
			));
		}
		if ($read['ifconvert'] == 2) {
			$read['content'] = convert($read['content'], $db_windpost);
		} else {
			$tpc_tag && $read['content'] = relatetag($read['content'], $tpc_tag);
			strpos($read['content'],'[s:') !== false && $read['content'] = showface($read['content']);
		}
		if ($attachs && is_array($attachs) && !$read['ifhide'] && empty($viewpic)) {
			if ($winduid == $read['authorid'] || $isGM || $pwSystem['delattach']) {
				$dfadmin = 1;
			} else {
				$dfadmin = 0;
			}
			foreach ($attachs as $at) {
				$atype = '';
				$rat = array();	
				
				if ($at['type'] == 'img' && $at['needrvrc'] == 0 && (!$GLOBALS['downloadimg'] || !$GLOBALS['downloadmoney'] || $_G['allowdownload'] == 2)) {
					$a_url = geturl($at['attachurl'], 'show');
					if (is_array($a_url)) {
						$atype = 'pic';
						$dfurl = cvpic($a_url[0], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $at['ifthumb'] & 1);
						$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'url' => $a_url[0], 'img' => $dfurl, 'dfadmin' => $dfadmin, 'desc' => $at['descrip'], 'miniUrl' => getMiniUrl($at['attachurl'], $at['ifthumb'], $a_url[1]));
					} elseif ($a_url == 'imgurl') {
						$atype = 'picurl';
						$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'dfadmin' => $dfadmin, 'verify' => md5("showimg{$tid}{$read[pid]}{$fid}{$at[aid]}{$GLOBALS[db_hash]}"));
					}
				} else {
					$atype = 'downattach';
					$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'size' => $at['size'], 'hits' => $at['hits'],'special' => $at['special'], 'ctype' => $at['ctype'], 'cname' => $GLOBALS['creditnames'][$at['ctype']], 'type' => $at['type'], 'dfadmin' => $dfadmin, 'desc' => $at['descrip'], 'ext' => strtolower(substr(strrchr($at['name'],'.'),1)));
					if ($at['needrvrc'] > 0) {
						!$at['ctype'] && $at['ctype'] = $at['special'] == 2 ? 'money' : 'rvrc';
						if ($at['type'] == 'img') {
							$a_url = geturl($at['attachurl'],'show');
							$dfurl = '<br>'.cvpic($a_url[0], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $at['ifthumb'] & 1);
							$rat['img'] = $dfurl;
						}
						if ($at['special'] == 2) {
							$GLOBALS['db_sellset']['price'] > 0 && $at['needrvrc'] = min($at['needrvrc'], $GLOBALS['db_sellset']['price']);
							$GLOBALS['sellAttachs'][] = $at['aid'];
						}
						$rat['needrvrc'] = $at['needrvrc'];
					}
				}
				if (!$atype) continue;
				if (in_array($at['aid'], $aids)) {
					$read['content'] = attcontent($read['content'], $atype, $rat);
				} else {
					$read[$atype][$at['aid']] = $rat;
				}
			}
		}
	}
	
	/**
	* convert the post content
	*/
	$read['alterinfo'] && $read['content'] .= "<div id=\"alert_$read[pid]\" style=\"color:gray;margin-top:30px\">[ $read[alterinfo] ]</div>";
	if ($read['remindinfo']) {
		$remind = explode("\t",$read['remindinfo']);
		$remind[0] = str_replace("\n","<br />",$remind[0]);
		$remind[2] && $remind[2] = get_date($remind[2]);
		$read['remindinfo'] = $remind;
	}
	if ($_GET['keyword']) {
		$keywords = explode("|",$_GET['keyword']);
		foreach ($keywords as $key => $value) {
			if ($value) $read['content'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($value,'/').")([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$)/siU","<u><font color=\"red\">\\1</font></u>\\2",$read['content']);
		}
	}
	$GLOBALS['foruminfo']['copyctrl'] && $read['content'] = preg_replace("/<br \/>/eis","copyctrl()",$read['content']);
	return $read;
}

function getMedals($uid, $medals) {
	global $_MEDALDB;
	$html = '';$flag = 0;
	$md_a = explode(',', $medals);
	foreach ($md_a as $key => $value) {
		if ($value && $_MEDALDB[$value]) {
			$html .= "<img src=\"hack/medal/image/{$_MEDALDB[$value][picurl]}\" title=\"{$_MEDALDB[$value][name]}\" /> ";
		} else {
			unset($md_a[$key]);
			$flag = 1;
		}
	}
	if ($flag) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($uid, array('medals' => implode(',', $md_a)));
	}
	return $html;
}

function viewHiddenAtt($attach) {
	if ($attach['dfadmin']) return true;
	if ($attach['special'] == 2 && isBuyFromSellAtt($attach['aid'])) {
		return true;
	}
	if ($attach['special'] == 1 && checkCreditFromHiddenAtt($attach['ctype'], $attach['needrvrc'])) {
		return true;
	}
	return false;
}

function isBuyFromSellAtt($aid) {
	static $buyAids = null;
	if (!isset($buyAids)) {
		global $db,$sellAttachs,$winduid;
		$buyAids = array();
		if ($sellAttachs) {
			$query = $db->query("SELECT aid FROM pw_attachbuy WHERE uid= " . S::sqlEscape($winduid) . ' AND aid IN(' . S::sqlImplode($sellAttachs) . ')');
			while ($rt = $db->fetch_array($query)) {
				$buyAids[] = $rt['aid'];	
			}
		}
	}
	return in_array($aid, $buyAids);
}

function checkCreditFromHiddenAtt($ctype, $v) {
	$hav = 0;
	if (in_array($ctype, array('money', 'rvrc', 'credit', 'currency'))) {
		$hav = $ctype == 'rvrc' ? $GLOBALS['userrvrc'] : $GLOBALS['winddb'][$ctype]; 
	}
	if (is_numeric($ctype)) {
		static $creditdb = null;
		if (!isset($creditdb)) {
			global $credit;
			require_once( R_P ."require/credit.php");
			$creditdb = $credit->get($GLOBALS['winduid'],'CUSTOM');
		}
		$hav = $creditdb[$ctype];
	}
	return $hav > $v;
}

function threadrelated ($relatedcon) {

	global $db,$db_iftag,$db_threadrelated,$forumset,$fid,$read,$tid,$db_modes,$db_dopen,$db_phopen,$db_share_open,$db_groups_open,$groupid,$timestamp;

	$relatedb = array();

	if (in_array($relatedcon,array('allpost','alldigest','allhits','allreply','forumpost','forumdigest','forumhits','forumreply'))) {
		//require_once(R_P.'require/element.class.php');
		//$element = new Element($forumset['relatednums']);
		$element = L::loadClass('element');
		$element->setDefaultNum($forumset['relatednums']);
		switch ($relatedcon) {
			case 'allpost' :
				$relatedb = $element->newSubject();break;
			case 'alldigest' :
				$relatedb = $element->digestSubject();break;
			case 'allhits' :
				$relatedb = $element->hitSort();break;
			case 'allreply' :
				$relatedb = $element->replySort();break;
			case 'forumpost' :
				$relatedb = $element->newSubject($fid);break;
			case 'forumdigest' :
				$relatedb = $element->digestSubject($fid);break;
			case 'forumhits' :
				$relatedb = $element->hitSort($fid);break;
			case 'forumreply' :
				$relatedb = $element->replySort($fid);break;
		}
	} elseif ($relatedcon == 'oinfo') {//继续改进
		if ($db_modes['o']['ifopen']) {
			require_once(R_P."require/app_core.php");
			$addwhere = '';
			if (!$db_dopen) {
				$addwhere .= " AND type!='diary'";
			}
			if (!$db_phopen) {
				$addwhere .= " AND type!='photo'";
			}
			if (!$db_share_open) {
				$addwhere .= " AND type!='share'";
			}
			if (!$db_groups_open) {
				$addwhere .= " AND type!='colony'";
			}
			$query = $db->query("SELECT type,descrip FROM pw_feed WHERE uid=".S::sqlEscape($read['authorid']).$addwhere." ORDER BY timestamp DESC  ".S::sqlLimit(0,$forumset['relatednums']));
			while ($rt = $db->fetch_array($query)) {
				$rt['title'] = parseFeedRead($rt['descrip']);
				$rt['url'] = USER_URL."$read[authorid]";
				unset($rt['type']);
				$relatedb[] = $rt;
			}
		}

	} elseif (in_array($relatedcon,array('pictags','hottags'))) {
		$tagid = $tagdbs = array();
		$endtime = $timestamp - 30*24*3600;
		$sql = 'WHERE t.ifcheck=1 AND t.tid !='.S::sqlEscape($tid). ' AND t.postdate >='.S::sqlEscape($endtime);
		$fidout = array('0');
		$query = $db->query("SELECT fid,allowvisit,password FROM pw_forums WHERE type<>'category'");
		while ($rt = $db->fetch_array($query)) {
			$allowvisit = (!$rt['allowvisit'] || $rt['allowvisit'] != str_replace(",$groupid,",'',$rt['allowvisit'])) ? true : false;
			if ($rt['password'] || !$allowvisit) {
				$fidout[] = $rt['fid'];
			}
		}
		$fidout = S::sqlImplode($fidout);
		$fidout && $sql .= " AND fid NOT IN ($fidout)";

		if ($db_iftag) {
			if ($read['tags'] && $relatedcon == 'pictags') {
				list($tagdb,$tpc_tag) = explode("\t",$read['tags']);
				$tagdbs = explode(' ',$tagdb);
			} elseif ($relatedcon == 'hottags') {
				@include_once pwCache::getPath(D_P.'data/bbscache/tagdb.php');
				$j = 0;
				foreach ($tagdb as $key => $val) {
					$j++;
					if ($j > 5) break;
					$tagdbs[] = $key;
				}
				unset($tagdb);
			}

			if ($tagdbs) {
				$query = $db->query("SELECT tagid FROM pw_tags WHERE tagname IN(" . S::sqlImplode($tagdbs) . ')');
				while ($rt = $db->fetch_array($query)) {
					$tagid[] = $rt['tagid'];
				}
			}
			if ($tagid) {
				$query = $db->query("SELECT t.tid,t.subject FROM pw_tagdata tg LEFT JOIN pw_threads t USING(tid) $sql AND tg.tagid IN(" . S::sqlImplode($tagid) . ") GROUP BY tid ORDER BY postdate DESC ".S::sqlLimit(0,$forumset['relatednums']));
				while ($rt = $db->fetch_array($query)) {
					$rt['title'] = $rt['subject'];
					$rt['url'] = "read.php?tid=".$rt['tid'];
					unset($rt['subject']);
					unset($rt['tid']);
					$relatedb[] = $rt;
				}
			}
		}
	} elseif (in_array($relatedcon,array('ownpost','owndigest','ownhits','ownreply'))) {
		$endtime = $timestamp - 15*24*3600;
		$sql = "WHERE ifcheck=1 AND tid !=".S::sqlEscape($tid). "AND postdate >=".S::sqlEscape($endtime)." AND authorid=".S::sqlEscape($read['authorid'])." AND fid>0 ";
		$orderby = '';

		switch ($relatedcon) {
			case 'ownpost' :
				$orderby .= " ORDER BY postdate DESC";
				break;
			case 'owndigest' :
				$sql .= " AND digest>0";
				$orderby .= " ORDER BY postdate DESC";
				break;
			case 'ownhits' :
				$orderby .= " ORDER BY hits DESC";
				break;
			case 'ownreply' :
				$orderby .= " ORDER BY replies DESC";
				break;
		}
		$query = $db->query("SELECT tid,subject FROM pw_threads FORCE INDEX(".getForceIndex('idx_postdate').") $sql $orderby".S::sqlLimit(0,$forumset['relatednums']));
		while ($rt = $db->fetch_array($query)) {
			$rt['title'] = $rt['subject'];
			$rt['url'] = "read.php?tid=".$rt['tid'];
			unset($rt['subject']);
			unset($rt['tid']);
			$relatedb[] = $rt;
		}
	}

	return $relatedb;
}

function getThreadType(){
	global $foruminfo,$read,$fid;
	$topic_type = isset($foruminfo['topictype'][$read['type']]) ? $foruminfo['topictype'][$read['type']] : '';
	return ($topic_type) ? '<a href="thread.php?fid='.$fid.'&type='.$read['type'].'" class="s5">['.$topic_type['name'].']</a>' : '';
}

function getMiniUrl($path, $ifthumb, $where) {
	$dir = '';
	($ifthumb & 1) && $dir = 'thumb/';
	($ifthumb & 2) && $dir = 'thumb/mini/';
	if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
	if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
	if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
	return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
}

function getTidAdert($fid, $lou) {
	$array = array();
	list($array['TidAmong']) = pwAdvert('Mode.Forum.Layer.TidAmong', $fid, $lou);
	list($array['TidRight']) = pwAdvert('Mode.Forum.Layer.TidRight', $fid, $lou+1);
	list($array['TidUp']) = pwAdvert('Mode.Forum.Layer.TidUp', $fid, $lou+1);
	list($array['TidDown']) = pwAdvert('Mode.Forum.Layer.TidDown', $fid, $lou+1);
	return $array;
}
?>
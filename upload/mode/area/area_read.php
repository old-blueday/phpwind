<?php
!defined('P_W') && exit('Forbidden');

$db_mode = 'area';
define('M_P',R_P."mode/$db_mode/");
$channelImagePath = 'mode/area/images';
$pwModeCss = 'mode/area/images/area_read_style.css';

$m = $db_mode;
$db_modepages = $db_modepages[$db_mode];
//* $threads = L::loadClass('Threads', 'forum');
//* if ('' == $read['content']) $read = $threads->getThreads($tid, true);

$_cacheService = Perf::gatherCache('pw_threads');
if ('' == $read['content']) $read = $_cacheService->getThreadAndTmsgByThreadId($tid);

$readnum = $db_readperpage = $forumset['readnum'] ? $forumset['readnum'] : ($db_readperpage ? $db_readperpage : 5);
if (!$openIndex) $count--;

$numofpage = ceil($count/$db_readperpage);

if ($page == 'e' || $page > $numofpage) {
	$numofpage == 1 && $page > 1 && ObHeader("read.php?tid=$tid&page=1&toread=$toread");
	$page = $numofpage;
}
//帖子排序
$forumset['replayorder'] && $orderby = $forumset['replayorder'] == '1' ? 'asc' : 'desc';
$threadorder = bindec(getstatus($read['tpcstatus'],4).getstatus($read['tpcstatus'],3));
$threadorder && $threadorder != 3 && $orderby = $threadorder == '1' ? 'asc' : 'desc';

//list($guidename,$forumtitle) = getforumtitle(forumindex($foruminfo['fup'],1),1);
list($guidename, $forumtitle) = $pwforum->getTitle();
$guidename .= "<em>&gt;</em><a href=\"read.php?tid=$tid\">$subject</a>";
$forumtitle = '|'.$forumtitle;

$db_metakeyword = substr($read['tags'],0,strpos($read['tags'],"\t"));
$db_metakeyword = (empty($db_metakeyword) ? $subject : $db_metakeyword).','.$forumtitle;
$db_metakeyword = trim(str_replace(array('|',' - ',"\t",' ',',,,',',,'),',',$db_metakeyword),',');

if ($groupid == 'guest' && !$read['ifshield'] && !$pwforum->forumBan($read)) {
	if ($read['ifconvert'] == 2) {
		$metadescrip = stripWindCode($read['content']);
		$metadescrip = strip_tags($metadescrip);
	} else {
		$metadescrip = strip_tags($read['content']);
	}
	$metadescrip = str_replace(array('"',"\n","\r",'&nbsp;','&amp;','&lt;','','&#160;'),'',$metadescrip);
	$metadescrip = substrs($metadescrip,255,false);
	if ($read['ifwordsfb'] != $db_wordsfb) {
		//$metadescrip = wordsfb($metadescrip,$read['ifwordsfb']);
	}
	if (trim($metadescrip)) {
		$db_metadescrip = $metadescrip;
	}
	unset($metadescrip,$tmpAllow);
}
$db_metadescrip = $db_bbsname.','.$db_metadescrip;

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

require_once(M_P.'require/header.php');
$msg_guide = $pwforum->headguide($guidename);
require_once(R_P.'require/showimg.php');
Update_ol();
$readdb = $authorids = array();

if ($read['modelid'] || $foruminfo['modelid']) {
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($read);
}
if ($read['special'] > 20 || $foruminfo['pcid']) {
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($read);
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

if ($read['special'] == 1 && ($foruminfo['allowtype'] & 2) ) {
	require_once(R_P.'require/readvote.php');
} elseif ($read['special'] == 2 && ($foruminfo['allowtype'] & 4) ) {
	require_once(R_P.'require/readact.php');
} elseif ($read['special'] == 3 && ($foruminfo['allowtype'] & 8)) {
	require_once(R_P.'require/readrew.php');
} elseif ($read['special'] == 4 && ($foruminfo['allowtype'] & 16)) {
	require_once(R_P.'require/readtrade.php');
} elseif ($read['special'] == 5 && ($foruminfo['allowtype'] & 32)) {
	require_once(R_P.'require/readdebate.php');
}

if ($db_replysitemail && $read['authorid']==$winduid && $read['ifmail']==4) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->get($winduid, false, false, true); //replyinfo

	$rt['replyinfo'] = str_replace(",$tid,",',',$rt['replyinfo']);
	if ($rt['replyinfo'] == ',') {
		if (getstatus($winddb['userstatus'], PW_USERSTATUS_NEWRP)) {
			$userService->setUserStatus($winduid, PW_USERSTATUS_NEWRP, false);
		}
		$rt['replyinfo'] = '';
	}
	$userService->update($winduid, array(), array(), array('replyinfo' => $rt['replyinfo']));
	//$db->update("UPDATE pw_threads SET ifmail='2' WHERE tid=".S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('ifmail'=>2));
}

$read['pid'] = 'tpc';
if (getstatus($read['tpcstatus'], 1)) {
	$rt = $db->get_one("SELECT a.cyid,c.cname FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE tid=" . S::sqlEscape($tid));
	$read = $read + $rt;
}
$thread_read = $read;
$thread_read['src_postdate'] = get_date($thread_read['postdate'], "Y-m-d H:i");
$thread_read['src_postdate2'] = $thread_read['postdate'];

$thread_read['aid'] && $_pids['tpc'] = 0;

$toread && $urladd .= "&toread=$toread";
$fpage > 1 && $urladd .= "&fpage=$fpage";
$pages = numofpage($count,$page,$numofpage,"read.php?tid=$tid{$urladd}&#newreply");

//更新帖子点击
if ($db_hits_store == 0){
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('hits=hits+1')));	
}elseif ($db_hits_store == 1){
	$db->update('UPDATE pw_hits_threads SET hits=hits+1 WHERE tid='.S::sqlEscape($tid)); 
}elseif ($db_hits_store == 2){
	pwCache::writeover(D_P.'data/bbscache/hits.txt',$tid."\t", 'ab');
}

/***  帖子浏览记录  ***/
$readlog = str_replace(",$tid,",',',GetCookie('readlog'));
$readlog.= ($readlog ? '' : ',').$tid.',';
$readlogCount = substr_count($readlog,',');
$readlogCount>11 && $readlog = preg_replace("/[\d]+\,/i",'',$readlog,$readlogCount-11);
Cookie('readlog',$readlog);

$favortitle = str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"),$subject);
$db_bbsname_a = addslashes($db_bbsname);//模版内用到

$readdb = array();

$pageinverse = $page > 20 && $page > ceil($numofpage/2) ? true : false;

if ($pageinverse) {
	$start_limit = $count-$page*$db_readperpage;
	$orderby = $orderby = 'asc' ? 'desc' : 'asc';
	$order = $rewardtype != null ? "t.ifreward ASC,t.postdate $orderby" : "t.postdate $orderby";
} else {
	$start_limit = ($page-1)*$db_readperpage;
	$order = $rewardtype != null ? "t.ifreward DESC,t.postdate $orderby" : "t.postdate $orderby";
}
if ($start_limit < 0) {
	$start_limit = 0;
	$readnum += $start_limit;
}
$limit = S::sqlLimit($start_limit,$readnum);
$query = $db->query("SELECT t.*,m.uid,m.username,m.gender,m.oicq,m.aliww,m.groupid,m.memberid,m.icon AS micon,
			m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.postnum,md.digests,md.rvrc,
			md.money,md.credit,md.currency,md.thisvisit,md.lastvisit,md.onlinetime,md.starttime $fieldadd
			FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $tablaadd
			WHERE t.tid=".S::sqlEscape($tid)." AND t.ifcheck='1' $sqladd ORDER BY $order $limit");
while ($read = $db->fetch_array($query)) {
	$read['src_postdate'] = $read['postdate'];
	$read['aid'] && $_pids[$read['pid']] = $read['pid'];
	$readdb[] = $read;
}

//读取帖子及回复的附件信息
$attachdb = array();
if ($_pids) {
	$query = $db->query('SELECT * FROM pw_attachs WHERE tid='.S::sqlEscape($tid)."AND pid IN (".S::sqlImplode($_pids).")");
	while($rt=$db->fetch_array($query)){
		if ($rt['pid'] == '0') $rt['pid'] = 'tpc';
		$attachdb[$rt['pid']][$rt['aid']] = $rt;
	}
	$attachShow = new attachShow(($isGM || $pwSystem['delattach']), $forumset['uploadset'], $forumset['viewpic']);
	$attachShow->init($tid, $_pids);
}

if(!$readdb){
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userInfo = $userService->get($thread_read['authorid']); //replyinfo
	$thread_read['groupid'] = $userInfo['groupid'];
	$thread_read['userstatus'] = $userInfo['userstatus'];
}

$bandb = $readdb ? $pwforum->forumBan($readdb) : $pwforum->forumBan($thread_read);
$wordsfb = L::loadClass('FilterUtil', 'filter');

isset($bandb[$thread_read['authorid']]) && $thread_read['groupid'] = 6;
$author = $thread_read['author'];
$authorids[] = $thread_read['authorid'];
$thread_read = viewread($thread_read, 0);
$thread_read['author'] = $author;

foreach ($readdb as $key => $read) {
	isset($bandb[$read['authorid']]) && $read['groupid'] = 6;
	$authorids[] = $read['authorid'];
	$readdb[$key] = viewread($read,$start_limit++);
	if ($db_mode == 'area') {
		$db_menuinit .= ",'td_read_".$read['pid']."':'menu_read_".$read['pid']."'";
	}
}

$authorids = S::sqlImplode($authorids);
unset($sign,$ltitle,$lpic,$lneed,$_G['right'],$_MEDALDB,$fieldadd,$tablaadd,$read,$order,$readnum,$pageinverse);

if ($db_showcolony && $authorids) {
	$colonydb = array();
	$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) AND c.ifadmin!='-1'");
	while ($rt = $db->fetch_array($query)) {
		if (!$colonydb[$rt['uid']]) {
			$colonydb[$rt['uid']] = $rt;
		}
	}
	$db->free_result($query);
}
$db_showcustom = ',' . implode(',', $db_showcustom) . ',';
if ($db_showcustom && $authorids) {
	$customdb = $cids = array();
	foreach ($_CREDITDB as $key => $value) {
		if (strpos($db_showcustom,",$key,") !== false) {
			$cids[] = $key;
		}
	}
	if ($cids) {
		$cids = S::sqlImplode($cids);
		$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN($authorids) AND cid IN($cids)");
		while ($rt = $db->fetch_array($query)) {
			$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
		}
		$db->free_result($query);
	}
}

//快速回复
if ($groupid != 'guest' && !$tpc_locked && ($admincheck || !$foruminfo['allowrp'] || allowcheck($foruminfo['allowrp'],$groupid,$winddb['groups'],$fid,$winddb['reply']))) {
	$psot_sta = 'reply';//control the faster reply
	$titletop1= substrs('Re:'.str_replace('&nbsp;',' ',$subject),$db_titlemax-2);
	$fastpost = 'fastpost';
	$db_forcetype = 0;
} else if($groupid == 'guest' && !$tpc_locked){//显示快速回复表单
    $fastpost = 'fastpost';
    $psot_sta = 'reply';
    $titletop1= substrs('Re:'.str_replace('&nbsp;',' ',$subject),$db_titlemax-2);
    $db_forcetype = 0;
    if((!$_G['allowrp'] && !$foruminfo['allowrp'])  || $foruminfo['allowrp']) {
        $anonymity = true;
    }
}
//$db_menuinit .= ",'td_post' : 'menu_post','td_post1' : 'menu_post','td_admin' : 'menu_admin'";
$db_menuinit .= ",'td_admin' : 'menu_admin'";

//allowtype onoff

if ($foruminfo['allowtype'] && (($foruminfo['allowtype'] & 1) || ($foruminfo['allowtype'] & 2 && $_G['allownewvote']) || ($foruminfo['allowtype'] & 4 && $_G['allowactive']) || ($foruminfo['allowtype'] & 8 && $_G['allowreward'])|| ($foruminfo['allowtype'] & 16) || $foruminfo['allowtype'] & 32 && $_G['allowdebate'])) {
	$N_allowtypeopen = true;
} else {
	$N_allowtypeopen = false;
}

//分类信息
if ($foruminfo['modelid']) {
	$modelids = explode(",",$foruminfo['modelid']);
	$N_allowtopicopen = true;
} else {
	$N_allowtopicopen = false;
}

//团购
if ($foruminfo['pcid']) {
	$pcids = explode(",",$foruminfo['pcid']);
	$N_allowpostcateopen = true;
} else {
	$N_allowpostcateopen = false;
}

$nxt_thread = $db->get_one("SELECT tid,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid,false)."AND ifcheck='1' AND specialsort='0' AND postdate<".S::sqlEscape($thread_read['src_postdate2'],false)."ORDER BY postdate DESC LIMIT 1");
$pre_thread = $db->get_one("SELECT tid,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid,false)."AND ifcheck='1' AND specialsort='0' AND postdate>".S::sqlEscape($thread_read['src_postdate2'],false)."ORDER BY postdate ASC LIMIT 1");


$element_class = L::loadClass('element');
$hot_threads = $element_class->replySortWeek('', 10);

$related_threads = threadrelated('allpost');

//评价功能开启部分开始
$rateSets = unserialize($db_ratepower);
if(!$forumset['rate'] && $rateSets[1] && isset($db_hackdb['rate'])){
	list($noAjax,$objectid,$typeid,$elementid) = array(TRUE,$tid,1,'vote_box');
	require_once R_P . 'hack/rate/index.php';
}
//评价功能开启部分结束
require_once PrintEot('area_read');
footer();

?>
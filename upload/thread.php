<?php
define('SCR','thread');
require_once('global.php');
L::loadClass('forum', 'forum', false);
//* include_once pwCache::getPath(D_P . 'data/bbscache/cache_thread.php',true);
pwCache::getData(D_P . 'data/bbscache/cache_thread.php');

S::gp(array('cyid'), '', 2);
S::gp(array('search','topicsearch','searchname'));
if ($cyid) {
	!$db_groups_open && Showmsg('groups_close');
	S::gp(array('showtype'));
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php',true);
	pwCache::getData(D_P . 'data/bbscache/o_config.php');
	$newColony = new PwColony($cyid);
	if (!$colony =& $newColony->getInfo()) {
		Showmsg('data_error');
	}
	$ifadmin = $newColony->getIfadmin();
	//当群组视图关闭状态下
	$colony['viewtype'] == 2 && $newColony->jumpToColony($showtype,$cyid);
	$fid = $colony['classid'];
	$tmpUrlAdd .= '&a=thread';
	if ($showtype && in_array($showtype, array('galbum', 'member', 'active', 'write', 'set'))) {
		$tmpUrlAdd = '';
		require_once S::escapePath(R_P."require/thread_{$showtype}.php");
	}
	require_once(R_P . 'require/bbscode.php');
	require_once(R_P . 'require/functions.php');
	$colony['descrip'] = convert($colony['descrip'], array());
	$annouce = convert(nl2br($colony['annouce']), $db_windpost);

	$colonyNums = PwColony::calculateCredit($colony);
	$magdb = $newColony->getManager();
}
$viewcolony = $cyid ? "cyid=$cyid" : "fid=$fid";
//读取版块信息
empty($fid) && Showmsg('data_error');

/*The app client*/
if ($db_siteappkey && $db_apps_list['17']['status'] == 1) {
	$forumappinfo = array();
	$appclient = L::loadClass('appclient');
	$forumappinfo = $appclient->showForumappinfo($fid,'thread','17');
}
/*The app client*/

$pwforum = new PwForum($fid);
if (!$pwforum->isForum(true)) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$forumset =& $pwforum->forumset;

if (Perf::checkMemcache()) {
	$_cacheService = Perf::getCacheService();
	$rt = $_cacheService->get('forumdata_announce_' . $fid);
}
if (!Perf::checkMemcache() || !$rt){
	$rt = $db->get_one("SELECT fd.tpost,fd.topic,fd.article,fd.subtopic,fd.top1,fd.top2,fd.topthreads,fd.lastpost,fd.aid,fd.aids,fd.aidcache,a.ifconvert,a.author,a.startdate,a.enddate,a.subject,a.content FROM pw_forumdata fd LEFT JOIN pw_announce a ON fd.aid=a.aid WHERE fd.fid=".S::sqlEscape($fid));
	Perf::checkMemcache() &&  $_cacheService->set('forumdata_announce_' . $fid, $rt, 300);
}

$rt && $foruminfo += $rt;#版块信息合并

!$forumset['commend'] && $foruminfo['commend'] = null;
$foruminfo['type'] == 'category' && ObHeader('cate.php?cateid=' . $fid);
if ($forumset['link']) {
	$flink = str_replace("&amp;","&",$forumset['link']);
	ObHeader($flink);
}

$threadBehavior = getThreadFactory($cyid, $search, $topicsearch);
if (($return = $threadBehavior->init()) !== true) {
	Showmsg($return);
}
$threadBehavior->setFid($fid);

$type = (int)S::getGP('type');

$foruminfo['logo'] = $threadBehavior->getLogo($foruminfo['logo']);

//门户形式浏览
if ($foruminfo['ifcms'] && $db_modes['area']['ifopen'] && !$cyid) {
	S::gp(array('viewbbs'));
	if (!$viewbbs) {
		require_once R_P. 'require/forum.php';
		require_once R_P. 'mode/area/area_thread.php';exit;
	}
	$viewbbs = $viewbbs ? "&viewbbs=$viewbbs" : "";
}

if (!S::inArray($windid,$manager)) {
	$pwforum->forumcheck($winddb, $groupid);
}
$pwforum->setForumStyle();

//版块浏览及管理权限
$pwSystem = array();
$isGM = $isBM = $admincheck = $ajaxcheck = $managecheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
if ($groupid != 'guest') {
	$isGM = S::inArray($windid,$manager);
	if ($colony) {//群组论坛浏览方式
		$ifcolonyadmin = $newColony->getColonyAdmin();
		$ifbbsadmin = $newColony->getBbsAdmin($isGM);
		$isBM = $pwforum->isBM($windid);
		$pwSystem = pwRights($isBM);
		if ($newColony->getManageCheck($ifbbsadmin,$ifcolonyadmin)) {
			$managecheck = 1;
		}
		$pwSystem['forumcolonyright'] && $managecheck = 1;
		($ifcolonyadmin || $ifbbsadmin || $pwSystem['forumcolonyright']) && $ajaxcheck = 1;
	} else {
		list($isBM,$admincheck,$ajaxcheck,$managecheck,$pwAnonyHide,$pwPostHide,$pwSellHide,$pwEncodeHide,$pwSystem) = $pwforum->getSystemRight();
	}
}
if (!$admincheck) {
	$pwforum->creditcheck($winddb, $groupid);#积分限制浏览
	$pwforum->sellcheck($winduid);#出售版块
}

$forumset['newtime'] && $db_newtime = $forumset['newtime'];
if ($foruminfo['aid'] && ($foruminfo['startdate']>$timestamp || ($foruminfo['enddate'] && $foruminfo['enddate']<$timestamp))) {
	$foruminfo['aid'] = 0;
}

list($guidename, $forumtitle) = $pwforum->getTitle();


/* SEO */
if ($type && is_array($foruminfo['topictype'])) {
	$_seo_type = $foruminfo['topictype'][$type];
}
$_seo = array('title'=>$foruminfo['title'],'metaDescription'=>$foruminfo['metadescrip'],'metaKeywords'=>$foruminfo['keywords']);
bbsSeoSettings('thread',$_seo,$foruminfo['name'],$_seo_type);
/* SEO */

require_once(R_P.'require/header.php');

$msg_guide = $pwforum->headguide($guidename);
unset($guidename,$foruminfo['forumset']);

//版主列表
$admin_T = array();
if ($foruminfo['forumadmin']) {
	$forumadmin = explode(',',$foruminfo['forumadmin']);
	foreach ($forumadmin as $key => $value) {
		if ($value) {
			if (!$db_adminshow) {
				if ($key==10) {$admin_T['admin'].='...'; break;}
				$admin_T['admin'] .= '<a href="u.php?username='.rawurlencode($value).'" target="_blank" class=" _cardshow" data-card-url="pw_ajax.php?action=smallcard&type=showcard&username='.rawurlencode($value).'" data-card-key='.$value.'>'.$value.'</a> ';
			} else {
				$admin_T['admin'] .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}
	}
	$admin_T['admin'] = '&nbsp;'.$admin_T['admin'];
}
//版主推荐
if ($forumset['commend'] && ($forumset['autocommend'] || $forumset['commendlist']) && $forumset['commendtime'] && $timestamp-$forumset['ifcommend']>$forumset['commendtime']) {
	require_once R_P. 'require/forum.php';
	updatecommend($fid,$forumset);
}
//版块浏览记录
$threadlog = str_replace(",$fid,",',',GetCookie('threadlog'));
$threadlog.= ($threadlog ? '' : ',').$fid.',';
substr_count($threadlog,',')>11 && $threadlog = preg_replace("/[\d]+\,/i",'',$threadlog,3);
Cookie('threadlog',$threadlog);

Update_ol();

$orderClass = array();//排序
S::gp(array('subtype','search','orderway','asc','topicsearch'));
S::gp(array('page','modelid','pcid','special','actmid','allactmid','allpcid','allmodelid'),'GP',2);

($orderway && $asc == "DESC") ? $orderClass[$orderway] = "class='s6 current'" : (($search == 'img') ? $orderClass['tid'] = "class='s6 current'" : $orderClass['lastpost'] = "class='s6 current'");

$searchadd = $thread_children = $thread_online = $fastpost = '';

$db_maxpage && $page > $db_maxpage && $page = $db_maxpage;
(int)$page<1 && $page = 1;

//版块及所属分类公告
$ifsort = 0;
$NT_A = $NT_C = array();
if ($page == 1) {
	$tempnotice = array('NT_A' => $notice_A,'NT_C' => $notice_C[$cateid]);
	foreach ($tempnotice as $key => $value) {
		if (!empty($value)) {
			$ifsort = 1;
			foreach ($value as $v) {
				if (empty(${$key}) && $v['startdate']<=$timestamp && (!$v['enddate'] || $v['enddate']>=$timestamp)) {
					$v['rawauthor'] = rawurlencode($v['author']);
					//$v['startdate'] = get_date($v['startdate']);
					!$v['url'] && $v['url'] = "notice.php?fid=$v[fid]#$v[aid]";
					${$key} = $v;
				}
			}
		}
	}
}
unset($notice_A,$notice_C);

if ($foruminfo['aid']) {
	require_once(R_P.'require/bbscode.php');
	$foruminfo['rawauthor'] = rawurlencode($foruminfo['author']);
	$foruminfo['startdate'] = get_date($foruminfo['startdate']);
	$announcement = $db_windpost;
	$announcement['picwidth'] = $db_threadsidebarifopen ? '800' : '910';
	$foruminfo['content'] = convert(str_replace(array("\n","\r\n"),'<br />',$foruminfo['content']),$announcement,'post');
}
if (strpos($_COOKIE['deploy'],"\tthread\t")===false) {
	$thread_img	 = 'fold';
	$cate_thread = '';
} else {
	$thread_img  = 'open';
	$cate_thread = 'display:none;';
}
if (strpos($_COOKIE['deploy'],"\tann\t")===false) {
	$ann_img	 = 'fold';
	$cate_ann = '';
} else {
	$ann_img  = 'open';
	$cate_ann = 'display:none;';
}
if (strpos($_COOKIE['deploy'],"\tchildren\t")===false) {
	$children_img	 = 'fold';
	$cate_children = '';
} else {
	$children_img  = 'open';
	$cate_children = 'display:none;';
}
if ($foruminfo['cnifopen'] && $forumset['viewcolony'] && !$cyid) {
	require_once(R_P . 'apps/groups/lib/colonys.class.php');
	$colonyServer = new PW_Colony();
	$cnGroups = $colonyServer->getColonysInForum($fid);
}

//子版块
$forumdb = array();
if (($foruminfo['childid'] || $cnGroups) && !$cyid) {
	require_once(R_P."require/thread_child.php");
}

//快捷管理
if ($managecheck) {
	S::gp(array('concle'));
	$concle || $concle = GetCookie('concle');
	if ($concle==1 && ($isGM || $pwSystem['topped'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'])) {
		$concle = 2;$managemode = 1;
		Cookie("concle","1",0);
	} else {
		$concle = 1;$managemode = 0;
		Cookie("concle","",0);
	}
	if ($colony) {
		$trd_adminhide = "<form action=\"mawholecolony.php?$viewbbs\" method=\"post\" name=\"mawhole\"><input type=\"hidden\" name=\"cyid\" value=\"$cyid\">";
	} else {
		$trd_adminhide = "<form action=\"mawhole.php?$viewbbs\" method=\"post\" name=\"mawhole\"><input type=\"hidden\" name=\"fid\" value=\"$fid\">";
	}
} else {
	$trd_adminhide = '';
}

$colspannum = 6;

if ($foruminfo['allowtype'] && (($foruminfo['allowtype'] & 1) || ($foruminfo['allowtype'] & 2 && $_G['allownewvote']) || ($foruminfo['allowtype'] & 4 && $_G['allowactive']) || ($foruminfo['allowtype'] & 8 && $_G['allowreward'])|| ($foruminfo['allowtype'] & 16) || $foruminfo['allowtype'] & 32 && $_G['allowdebate'])) {
	$N_allowtypeopen = true;
} else {
	$N_allowtypeopen = false;
}
/*分类、团购、活动 start*/
/*分类信息*/
if ($foruminfo['modelid'] || $modelid > 0) {
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($pwpost);
	$modelids = explode(",",$foruminfo['modelid']);
	if ($foruminfo['modelid']) {
		$N_allowtypeopen = true;
	}
}

/*团购*/
if ($foruminfo['pcid'] || $pcid > 0) {
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($pwpost);
	$pcids = explode(",",$foruminfo['pcid']);
	if ($foruminfo['pcid']) {
		$N_allowtypeopen = true;
	}
}

/*活动*/
if ($foruminfo['actmids'] || $actmid > 0) {
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$actmids = explode(",",$foruminfo['actmids']);
	$firstactmid = 0;
	foreach ($actmids as $value) {
		if(isset($postActForBbs->activitymodeldb[$value]) && $postActForBbs->activitymodeldb[$value]['ifable'] && $postActForBbs->activitycatedb[$postActForBbs->activitymodeldb[$value]['actid']]['ifable']){
			$firstactmid = $value;
			break;
		}
	}
	if ($foruminfo['actmids']) {
		$N_allowtypeopen = true;
	}
	$db_menuinit .= ",'td_activitylist' : 'menu_activitylist'";
}

$theSpecialFlag = false;//是否是特殊帖子（分类、团购、活动）
$theSpecialSearchHtml = false;
if ($modelid > 0) {/*分类信息*/
	$fielddb = $postTopic->getFieldData($modelid,'one');
	if (strpos(",".$foruminfo['modelid'].",",",".$modelid.",") === false) {
		Showmsg('forum_model_undefined');
	}
	!$postTopic->topicmodeldb[$modelid]['ifable'] && Showmsg('topic_model_unable');
	if (!$postTopic->topiccatedb[$postTopic->topicmodeldb[$modelid]['cateid']]['ifable']) {
		Showmsg('topic_cate_unable');
	}

	foreach ($fielddb as $key => $value) {
		if($value['threadshow'] == 1) {
			$threadshowfield[$key] = $value;
		}
	}
	$colspannum = count($threadshowfield) + 2;
	$initSearchHtml = $postTopic->initSearchHtml($modelid);
	$theSpecialSearchHtml = true;
} elseif ($pcid > 0) {/*团购*/
	$fielddb = $postCate->getFieldData($pcid,'one');
	if (strpos(",".$foruminfo['pcid'].",",",".$pcid.",") === false || !$postCate->postcatedb[$pcid]['ifable']) {
		Showmsg('forum_pc_undefined');
	}

	foreach ($fielddb as $key => $value) {
		if($value['threadshow'] == 1) {
			$threadshowfield[$key] = $value;
		}
	}
	$colspannum = count($threadshowfield) + 2;
	$initSearchHtml = $postCate->initSearchHtml($pcid);
	$theSpecialSearchHtml = true;
} elseif ($actmid > 0) {/*活动子分类*/
	$fielddb = $postActForBbs->getFieldData($actmid, 1);
	if (strpos(",".$foruminfo['actmids'].",",",".$actmid.",") === false) {
		Showmsg('act_model_undefined');
	}
	!$postActForBbs->activitymodeldb[$actmid]['ifable'] && Showmsg('act_model_disabled');
	if (!$postActForBbs->activitycatedb[$postActForBbs->activitymodeldb[$actmid]['actid']]['ifable']) {
		Showmsg('act_cate_disabled');
	}
	$i = $lastViewOrder = 0;
	$threadColumnName = $threadshowfield = array();
	foreach ($fielddb as $key => $value) {
		if($value['threadshow'] == 1) {
			if ($value['vieworder'] != $lastViewOrder || $value['vieworder'] == 0) {
				$i++;
				$threadColumnName[$i] = $postActForBbs->getFieldNameOneByName($value['name']);
			}
			$threadshowfield[$i][$key] = $value;
			$lastViewOrder = $value['vieworder'];
		}
	}
	$colspannum = count($threadshowfield) + 2;
	$initSearchHtml = $postActForBbs->initSearchHtml($actmid);
	$theSpecialSearchHtml = true;
} elseif ($allactmid) { /*活动所有分类*/
	$initSearchHtml = $postActForBbs->initSearchHtml();
	//$theSpecialFlag = true;
}elseif ($allpcid) { /*团购所有分类*/
	$initSearchHtml = $postCate->initSearchHtml();
	//$theSpecialFlag = true;
}
/*分类、团购、活动 end*/

$t_per = $foruminfo['t_type'];
$t_db = (array)$foruminfo['topictype'];
unset($foruminfo['t_type']);/* 0 */

if ($winddb['t_num']) {
	$db_perpage = $winddb['t_num'];
} elseif ($forumset['threadnum']) {
	$db_perpage = $forumset['threadnum'];
}
if ($winddb['p_num']) {
	$db_readperpage = $winddb['p_num'];
} elseif ($forumset['readnum']) {
	$db_readperpage = $forumset['readnum'];
}

$threadBehavior->setWhere();
$count = $threadBehavior->getThreadCount();
$pwSelectType = $threadBehavior->getSelectType();
$pwSelectSpecial = $threadBehavior->getSelectSpecial();

(!$pwSelectSpecial && !$type && !$cyid && $forumset['iftucool'] && $forumset['iftucooldefault']) && ObHeader("thread.php?fid=$fid&search=img");

if (!$theSpecialFlag && !$cyid && $pwSelectSpecial != 'img') { 
	$count += $foruminfo['top2'] + $foruminfo['top1'];
}
$numofpage = ceil($count/$db_perpage);
$numofpage < 1 && $numofpage = 1;
if ($page > $numofpage) {
	$page  = $numofpage;
}
$start_limit = intval(($page-1) * $db_perpage);
$totalpage	= min($numofpage,$db_maxpage);
$urlall	 = $threadBehavior->getUrlall();
$urladd	 = $threadBehavior->getUrladd();
$pageUrl = "thread.php?" . ($cyid ? "cyid=$cyid" : "fid=$fid");
$pages	 = numofpage($count, $page, $numofpage, "{$pageUrl}{$urladd}{$viewbbs}&", $db_maxpage);
require_once(R_P.'require/updateforum.php');
	
$threaddb = $threadBehavior->getThread($start_limit, !$theSpecialFlag);

// 同步pw_hits_threads数据到pw_threads, 进行该操作的概率是1/100
if ($db_hits_store == 1 && $timestamp % 100 == 0){
	$_tids = array();
	foreach ($threaddb as $_threads){
		$_tids[] = $_threads['tid'];
	}
	$db->update('UPDATE pw_threads t INNER JOIN pw_hits_threads h ON t.tid=h.tid SET t.hits=h.hits WHERE t.tid IN (' . S::sqlImplode($_tids) . ')');
	
	// 更新memcache缓存
	if (Perf::checkMemcache()){
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$_tids));
	}
}

//获取列表是否新窗打开的cookie
$newwindows = $_COOKIE['newwindows'];
$tucoolnewwindows = $_COOKIE['tucoolnewwindows'];
$isAuthStatus = $isGM || (!$forumset['auth_allowpost'] || $pwforum->authStatus($winddb['userstatus'],$forumset['auth_logicalmethod']) === true);
//!$isAuthStatus && $N_allowtypeopen = false;
if ($groupid != 'guest' && $db_threadshowpost == 1 && $_G['allowpost'] && $pwforum->allowpost($winddb,$groupid) && $isAuthStatus) {
	$fastpost = 'fastpost';
}
$psot_sta = $titletop1 = '';

$t_exits  = 0;
$t_typedb = $t_subtypedb = $withSystemType = $withSystemSubType = array();
if ($t_db) {
	foreach ($t_db as $value) {	
		if ($value['upid'] == 0) {
			$withSystemType[$value['id']] = $t_typedb[$value['id']] = $value;
		} else {
			$withSystemSubType[$value['upid']][$value['id']] = $t_subtypedb[$value['upid']][$value['id']] = strip_tags($value['name']);
		}
		if ($value['ifsys'] && $gp_gptype != 'system') unset($t_typedb[$value['id']], $t_subtypedb[$value['upid']][$value['id']]);
		$t_exits = 1;
	}
}
$t_childtypedb = $withSystemSubType;
foreach ($withSystemType as $value) {
	if ($t_childtypedb[$value['id']]) {
		$db_menuinit .= ",'thread_type_$value[id]' : 'thread_typechild_$value[id]'";
	}
}
$postUrl = 'post.php?fid=' . $fid;
if ($cyid) {
	$postUrl .= '&cyid=' . $cyid;
} else {
	$db_menuinit .= ",'td_post' : 'menu_post','td_post1' : 'menu_post','td_special' : 'menu_special'";
}
if ($t_subtypedb) {
	$t_subtypedb = pwJsonEncode($t_subtypedb);
	$t_sub_exits = 1;
}
$db_forcetype = $t_exits && $t_per=='2' && !$admincheck ? 1 : 0; // 是否需要强制主题分类

$db_maxtypenum == 0 && $db_maxtypenum = 5;
if ($winddb['shortcut']) {
	$myshortcut = 'true';
} else {
	$myshortcut = 'false';
}

if (defined('M_P') && file_exists(M_P.'thread.php')) {
	require_once(M_P.'thread.php');
}
CloudWind::yunSetCookie(SCR,'',$fid);
require_once PrintEot($threadBehavior->template);
$noticecache = 900;
$foruminfo['enddate'] && $foruminfo['enddate']<=$timestamp && $foruminfo['aidcache'] = $timestamp-$noticecache;
if ($foruminfo['aidcache'] && $timestamp-$foruminfo['aidcache']>$noticecache-1 && ($foruminfo['startdate']>$timestamp || ($foruminfo['enddate'] && ($foruminfo['enddate']<=$timestamp || $foruminfo['aids'])))) {
	$foruminfo['aid'] && $foruminfo['aids'] .= ",$foruminfo[aid]";
	require_once(R_P.'require/updatenotice.php');
	updatecache_i_i($fid,$foruminfo['aids']);
}
footer();

function getstart($start,$asc,$count) {
	global $db_perpage,$page,$numofpage;
	$limit = $db_perpage;
	if ($page>20 && $page>ceil($numofpage/2)) {
		$asc = $asc=='DESC' ? 'ASC' : 'DESC';
		$start = $count-$page*$db_perpage;
		if ($start < 0) {
			$limit = $db_perpage+$start;
			$start = 0;
		}
		return array($start,$limit,$asc,1);
	} else {
		return array($start,$limit,$asc,0);
	}
}

class baseThread {

	var $threadSearch;
	var $db;
	var $fid;
	var $template = 'thread';

	function baseThread() {
		$this->db =& $GLOBALS['db'];
		$this->threadSearch = new threadSearch();
	}

	function init() {
		return true;
	}

	function getThreadCount() {//abstruct
	}

	function getThread($start, $allowtop) {//abstruct
	}

	function setWhere() {//abstruct
	}

	function setFid($fid) {
		$this->fid = $fid;
	}

	function getUrladd() {
		return $this->threadSearch->urladd;
	}

	function getUrlall() {
		return $this->threadSearch->urlall;
	}
	
	function getSelectType() {
		return $this->threadSearch->selectType;
	}

	function getSelectSpecial() {
		return $this->threadSearch->selectSpecial;
	}

	function getLogo($logo) {
		global $db_indexfmlogo,$attachdir,$attachpath,$imgdir,$stylepath,$imgpath;
		if ($db_indexfmlogo == 2 && $logo) {
			if (strpos($logo,'http://') !== false) {
				return $logo;
			}
			$forumLogo = geturl($logo);
			return $forumLogo[0];
		}
		if ($db_indexfmlogo == 1 && file_exists("$imgdir/$stylepath/forumlogo/{$this->fid}.gif")) {
			return "$imgpath/$stylepath/forumlogo/$fid.gif";
		}
		return '';
	}

	function getThreadSortWithToppedThread($allowtop, $start) {
		global $count;
		$R = 0;
		$tpcdb = array();
		$asc = $this->threadSearch->asc;
		if ($allowtop) {
			global $foruminfo,$db_perpage;
			$toptids = trim($foruminfo['topthreads'], ',');
			$rows = count(explode(',', $toptids));
			if ($start < $rows) {
				$L = (int)min($rows - $start, $db_perpage);
				$limit  = S::sqlLimit($start,$L);
				$offset = 0;
				$limit2 = $L == $db_perpage ? '' : $db_perpage - $L;
				if ($rows && $toptids) {
					$query = $this->db->query("SELECT * FROM pw_threads WHERE tid IN($toptids) ORDER BY specialsort DESC,lastpost DESC $limit");
					while ($rt = $this->db->fetch_array($query)) {
						$tpcdb[] = $rt;
					}
					$this->db->free_result($query);
				}
				unset($toptids,$L,$limit);
			} else {
				list($offset,$limit2,$asc,$R) = getstart($start - $rows, $asc, $count);
			}
		} else {
			list($offset,$limit2,$asc,$R) = getstart($start, $asc, $count);
		}
		$this->threadSearch->asc = $asc;
		return array($offset, $limit2, $tpcdb, $R);
	}

	function analyseDataToCache($tpcdb) {
		global $db_ifpwcache,$timestamp;
		if (!($db_ifpwcache&112) || pwFilemtime(D_P.'data/bbscache/hitsort_judge.php') > $timestamp - 600) {
			return;
		}
		extract(pwCache::getData(D_P.'data/bbscache/hitsort_judge.php', false));
		$updatelist = $updatetype = array();
		foreach ($tpcdb as $thread) {
			if ($db_ifpwcache & 16) {
				if ($thread['hits'] > $hitsort_judge['hitsort'][$this->fid] && $thread['fid'] == $this->fid) {
					$updatelist[] = array('hitsort', $this->fid, $thread['tid'], $thread['hits'], '', 0);
					$updatetype['hitsort'] = 1;
				}
			}
			if ($db_ifpwcache & 32 && $thread['postdate'] > $timestamp - 24*3600) {
				if ($thread['hits'] > $hitsort_judge['hitsortday'][$this->fid] && $thread['fid'] == $this->fid) {
					$updatelist[] = array('hitsortday', $this->fid, $thread['tid'], $thread['hits'], $thread['postdate'], 0);
					$updatetype['hitsortday'] = 1;
				}
			}
			if ($db_ifpwcache & 64 && $thread['postdate'] > $timestamp-7*24*3600) {
				if ($thread['hits'] > $hitsort_judge['hitsortweek'][$this->fid] && $thread['fid'] == $this->fid) {
					$updatelist[] = array('hitsortweek', $this->fid, $thread['tid'], $thread['hits'], $thread['postdate'], 0);
					$updatetype['hitsortweek'] = 1;
				}
			}
		}
		if ($updatelist) {
			L::loadClass('elementupdate', '', false);
			$elementupdate = new ElementUpdate($this->fid);
			$elementupdate->setJudge('hitsort', $hitsort_judge);
			$elementupdate->setUpdateList($updatelist);
			$elementupdate->setUpdateType($updatetype);
			$elementupdate->updateSQL();
			unset($elementupdate);
		}
	}

	function parseThread($tpcdb) {
		global $isGM,$pwSystem,$foruminfo,$forumset,$db_readdir,$viewbbs,$page,$managemode,$imgpath,$stylepath,$db_readperpage,$managecheck, $db_threademotion,$winduid,$db_anonymousname,$modelid,$pcid,$actmid,$timestamp;

		$this->analyseDataToCache($tpcdb);
		$pwAnonyHide = $isGM || $pwSystem['anonyhide'];
		$updatetop = 0;
		$threaddb = $rewids = $cyids = $replyReward = array();
		$arrStatus = array(1 => 'vote', 2 => 'active', 3 => 'reward', 4 => 'trade', 5 => 'debate');
		$attachtype	= array('1' => 'img', '2' => 'txt', '3' => 'zip');
		foreach ($tpcdb as $key => $thread) {
			$foruminfo['allowhtm'] == 1 && $htmurl = $db_readdir.'/'.$this->fid.'/'.date('ym',$thread['postdate']).'/'.$thread['tid'].'.html';
			$thread['tpcurl'] = "read.php?tid={$thread[tid]}$viewbbs".($page>1 ? "&fpage=$page" : '');
			if ($managemode == 1) {
				$thread['tpcurl'] .= '&toread=1';
			} elseif (!$foruminfo['cms'] && $foruminfo['allowhtm']==1 && file_exists(R_P.$htmurl)) {
				$thread['tpcurl'] = "$htmurl";
			}
			if ($thread['toolfield']) {
				list($t,$e,$m) = explode(',',$thread['toolfield']);
				$sqladd = '';
				if ($t && $t<$timestamp) {
					$sqladd .= ",toolinfo='',specialsort=0,topped='0'";$t='';
					$thread['topped']>0 && $updatetop=1;
				}
				if ($e && $e<$timestamp) {
					$sqladd .= ",titlefont=''";$thread['titlefont']='';$e='';
				}
				if ($m && $m<$timestamp) {
					$sqladd .= ",specialsort=0";$m='';
					$kmdService = L::loadClass('kmdservice', 'forum');
					$kmdService->initKmdInfoByTid($thread['tid']);
				}
				if ($sqladd) {
					$thread['toolfield'] = implode(',',array(0=>$t,1=>$e,2=>$m));
					$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET toolfield=:toolfield $sqladd WHERE tid=:tid", array('pw_threads', $thread['toolfield'], $thread['tid'])));
					//* $threads = L::loadClass('Threads', 'forum');
					//* $threads->delThreads($thread['tid']);
				}
			}
			$forumset['cutnums'] && $thread['subject'] = substrs($thread['subject'],$forumset['cutnums']);
			$forumset['cutnums'] > 80 && $thread['subject'] = substrs(str_replace('&nbsp;',' ',$thread['subject']), 80);
			
			if ($thread['titlefont']) {
				$titledetail = explode("~",$thread['titlefont']);
				if ($titledetail[0]) $thread['subject'] = "<font color=$titledetail[0]>$thread[subject]</font>";
				if ($titledetail[1]) $thread['subject'] = "<b>$thread[subject]</b>";
				if ($titledetail[2]) $thread['subject'] = "<i>$thread[subject]</i>";
				if ($titledetail[3]) $thread['subject'] = "<u>$thread[subject]</u>";
			}
			if ($thread['ifshield']) {
				$thread['subject'] = threadShield('shield_title');
			}
			if ($thread['ifmark']) {
				$thread['ifmark'] = $thread['ifmark']>0 ? "<span class='gray tpage w'>&#xFF08;+$thread[ifmark]&#xFF09;</span>" : "<span class='gray tpage w'>&#xFF08;$thread[ifmark]&#xFF09;</span>";
			} else {
				unset($thread['ifmark']);
			}
			if (isset($arrStatus[$thread['special']])) {
				$p_status = $thread['locked']%3 == 0 ? $arrStatus[$thread['special']] : $arrStatus[$thread['special']].'lock';
			} elseif ($thread['locked']%3<>0) {
				$p_status = $thread['locked']%3 == 1 ? 'topiclock' : 'topicclose';
			} else {
				$p_status = $thread['ifmagic'] ? 'magic' : ($thread['replies']>=10 ? 'topichot' : 'topicnew');
			}
			if ($thread['special'] == 8 && $p_status == 'topicnew') {//活动帖图标展示
				$p_status = 'activity';
			}
			$thread['inspect'] && $thread['inspect'] = explode("\t",$thread['inspect']);
			$thread['tooltip'] = $p_status;
			$thread['status'] = "<img src=\"$imgpath/$stylepath/thread/".$p_status.".gif\" align=\"absmiddle\">";
			if ($thread['special'] == 8 && $p_status == 'activity') {//活动帖图标展示
				$thread['status'] = "<img src=\"$imgpath/activity/".$p_status.".gif\" border=0 align=\"absmiddle\">";
			}
			$thread['topped'] && $GLOBALS['ifsort']=1;
			$thread['ispage'] = '';
			if ($thread['topreplays']+$thread['replies']+1>$db_readperpage) {
				$numofpage = ceil(($thread['topreplays']+$thread['replies']+1)/$db_readperpage);
				$fpage = $page > 1 ? "&fpage=$page" : "";
				$thread['ispage']=' ';
				$thread['ispage'].="&nbsp;<img src=\"$imgpath/$stylepath/file/multipage.gif\" align=\"absmiddle\" alt=\"pages\">&nbsp;<span class=\"tpage\">";
				for($j=1; $j<=$numofpage; $j++) {
					if ($j==6 && $j+1<$numofpage) {
						$thread['ispage'].=" .. <a href=\"read.php?tid=$thread[tid]$fpage&page=$numofpage\">$numofpage</a>";
						break;
					} elseif ($j == 1) {
						$thread['ispage'].="";
		//				$thread['ispage'].=" <a href=\"read.php?tid=$thread[tid]$fpage\">$j</a>";
					} else {
						$thread['ispage'].=" <a href=\"read.php?tid=$thread[tid]$fpage&page=$j\">$j</a>";
					}
				}
				$thread['ispage'].='</span> ';
			}
			$postdetail = explode(",",$thread['lastpost']);

			if ($thread['ifupload']) {
				$atype = $attachtype[$thread['ifupload']];
				$thread['titleadd']=" <img src=\"$imgpath/$stylepath/file/$atype.gif\" alt=\"$atype\" align=\"absmiddle\">";
			} else {
				$thread['titleadd']="";
			}
			/*if ($managecheck) {
				if ($thread['fid'] == $this->fid) {
					$thread['adminbox'] = "<input type=\"checkbox\" autocomplete=\"off\" name=\"tidarray[]\" id=tid_{$thread[tid]} value=\"$thread[tid]\" onclick=\"postManage.show('postbatch','a_ajax_{$thread[tid]}')\" />";
				} else {
					$thread['adminbox'] = "&nbsp;&nbsp;&nbsp;";
				}
			}*/
			if ($db_threademotion) {
				if ($thread['icon']=="R"||!$thread['icon']) {
					$thread['useriocn']='';
				} else {
					$thread['useriocn']="<img src=\"$imgpath/post/emotion/$thread[icon].gif\" alt=\"$thread[icon]\" align=\"absmiddle\"> ";
				}
			}
			if ($thread['anonymous'] && $thread['authorid']!=$winduid && !$pwAnonyHide) {
				$thread['author']	= $db_anonymousname;
				$thread['authorid'] = 0;
			}
			if ($thread['special'] == 3 && $thread['state'] < 1) {
				$rewids[] = $thread['tid'];
			}

			//获取分类信息的帖子id
			if ($modelid > 0) {
				$topicids[] = $thread['tid'];
			}

			//获取团购的帖子id
			if ($pcid > 0) {
				$postcatepcids[] = $thread['tid'];
			}

			//获取活动的帖子id
			if ($actmid > 0 || $thread['special'] == 8) {
				$activitytiddb[] = $thread['tid'];
			}

			if (getstatus($thread['tpcstatus'], 1)) {
				$cyids[] = $thread['tid'];
			}
			
			if (getstatus($thread['tpcstatus'], 8)) {
				$replyReward[] = $thread['tid'];
			}
			
			$threaddb[$thread['tid']] = $thread;
		}

		if ($rewids) {
			$rewids = S::sqlImplode($rewids);
			$query = $this->db->query("SELECT tid,cbval,caval FROM pw_reward WHERE tid IN($rewids)");
			while ($rt = $this->db->fetch_array($query)) {
				$threaddb[$rt['tid']]['rewcredit'] = $rt['cbval'] + $rt['caval'];
			}
		}
		if ($cyids && !$cyid) {
			$query = $this->db->query("SELECT a.tid,a.cyid,c.cname FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE tid IN (" . S::sqlImplode($cyids) . ')');
			while ($rt = $this->db->fetch_array($query)) {
				$threaddb[$rt['tid']]['colony'] = $rt;
			}
		}
		if ($topicids) {
			$topicvaluetable = GetTopcitable($modelid);
			$query = $this->db->query("SELECT * FROM $topicvaluetable WHERE tid IN (" .S::sqlImplode($topicids). ")");
			while ($rt = $this->db->fetch_array($query)) {
				$threaddb[$rt['tid']]['topic'] = $rt;
			}
		}
		if ($postcatepcids) {//团购
			$pcvaluetable = GetPcatetable($pcid);
			$query = $this->db->query("SELECT * FROM $pcvaluetable WHERE tid IN (" .S::sqlImplode($postcatepcids). ")");
			while ($rt = $this->db->fetch_array($query)) {
				$threaddb[$rt['tid']]['topic'] = $rt;
			}
		}
		if ($activitytiddb) {//活动
			global $threadshowfield,$postActForBbs;
			$defaultValueTableName = getActivityValueTableNameByActmid();
			if ($actmid) {
				$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
				$query = $this->db->query("SELECT actmid,recommend,starttime,endtime,location,contact,telephone,picture1,picture2,picture3,picture4,picture5,signupstarttime,signupendtime,minparticipant,maxparticipant,userlimit,specificuserlimit,genderlimit,fees,feesdetail,paymethod,ut.* FROM $defaultValueTableName dt LEFT JOIN $userDefinedValueTableName ut USING(tid) WHERE dt.tid IN(".S::sqlImplode($activitytiddb). ")");
			} else {
				$query = $this->db->query("SELECT * FROM $defaultValueTableName WHERE tid IN(".S::sqlImplode($activitytiddb). ")");
			}
			while ($rt = $this->db->fetch_array($query)) {
				if ($rt['recommend'] == 1) {
					$threaddb[$rt['tid']]['recommendadd'] = " <img src=\"$imgpath/activity/actrecommend.gif\" border=0 align=\"absmiddle\" title=\"".getLangInfo('other','act_recommend')."\">";
				} else {
					$threaddb[$rt['tid']]['recommendadd'] = "";
				}
				if ($threadshowfield) {
					foreach ($threadshowfield AS $key => $column) {
						$i = 0;
						$columnHtml = '';
						foreach ($column AS $field) {
							// 如人数限制值为0，视为空
							if (in_array($field['fieldname'], array('minparticipant','maxparticipant')) && $rt[$field['fieldname']] == 0) {
								$rt[$field['fieldname']] = '';
							}
							if ($rt[$field['fieldname']] !== '') {
								$names = $postActForBbs->getNamePartsByName($field['name']);
								if ($i != 0) {
									$columnHtml .= ' ' . $names[0];
								}
								$columnHtml .= $names[1].' ';
								$fieldValueHtml = $postActForBbs->getThreadFieldValueHtml($field['type'], $rt[$field['fieldname']], $field['rules'], $field['fieldname']);
								$columnHtml .= $fieldValueHtml;
								$columnHtml .= $names[2];
								$columnHtml = trim($columnHtml);
							}
							$i++;
						}
						$threaddb[$rt['tid']]['topic'][$key] = ($columnHtml ? $columnHtml : '');
					}
				}
			}
		}
		
		if ($replyReward) {
			$replyRewardService = L::loadClass('ReplyReward', 'forum');/* @var $replyRewardService PW_ReplyReward */
			$replyRewardInfos = $replyRewardService->getRewardByTids($replyReward);
			foreach ($replyRewardInfos as $value) {
				$threaddb[$value['tid']]['replyrewardtip'] = '[回帖奖励' . intval($value['creditnum'] * $value['lefttimes']) . ']';
			}
		}

		if ($updatetop) {
			require_once(R_P.'require/updateforum.php');
			updatetop();
		}
		return $threaddb;
	}
}

class colonyThread extends baseThread {

	function getThreadCount() {
		global $newColony,$colony;
		$sqladd = $this->threadSearch->sqladd;
		return $sqladd ? $newColony->getArgumentCount($sqladd) : $colony['tnum'];
	}

	function getThread($start, $allowtop) {
		global $newColony;
		list($offset, $limit2, $tpcdb, $R) = $this->getThreadSortWithToppedThread(false, $start);
		$tpcdb = $newColony->getArgument($this->threadSearch->sqladd, $offset, $limit2, $this->threadSearch->order, $this->threadSearch->asc);
		$R && $tpcdb = array_reverse($tpcdb);
		return $this->parseThread($tpcdb);
	}

	function setWhere() {
		global $type,$search;
		$this->threadSearch->setType($type)
		|| $this->threadSearch->setDigest($search, 'a');

		$this->threadSearch->setOrder();
	}
}

class commonThread extends baseThread {

	function getThreadCount() {
		if ($this->threadSearch->sqladd) {
			$sqladd = $GLOBALS['theSpecialFlag'] ? $this->threadSearch->getSqlAdd() : $this->threadSearch->getSqlAdd(true);
			$count = $this->db->get_value('SELECT COUNT(*) AS count FROM pw_threads t WHERE t.fid =' . S::sqlEscape($this->fid) . $sqladd);
		} else {
			$count = $GLOBALS['theSpecialFlag'] ? $GLOBALS['foruminfo']['topic'] : $GLOBALS['foruminfo']['topic'] - $GLOBALS['foruminfo']['top1'];
		}
		return $count;
	}

	function getThread($start, $allowtop) {
		$method = $this->getThreadMethod();
		if ($method == 2) {
			return $this->getFromCache();
		}
		$tpcdb = $this->parseThread($this->getFromDB($start, $allowtop));
		if ($method == 1) {
			pwCache::setData(S::escapePath(D_P."data/bbscache/fcache_{$this->fid}_{$GLOBALS[page]}.php"), "<?php\r\n\$threaddb=" . pw_var_export($tpcdb) . ";\r\n?>");
			touch(S::escapePath(D_P."data/bbscache/fcache_{$this->fid}_{$GLOBALS[page]}.php"));
		}
		return $tpcdb;
	}

	function getThreadMethod() {
		global $page,$db_fcachenum,$fid,$foruminfo,$timestamp,$db_fcachetime;
		$fcache = 0;
		if ($db_fcachenum && $page <= $db_fcachenum && empty($this->threadSearch->urladd)) {
			$fcachetime = pwFilemtime(D_P . "data/bbscache/fcache_{$fid}_{$page}.php");
			$lastpost = explode("\t", $foruminfo['lastpost']);
			if (!file_exists(D_P."data/bbscache/fcache_{$fid}_{$page}.php") || $lastpost[2]>$fcachetime && $timestamp-$fcachetime>$db_fcachetime) {
				$fcache = 1;
			} else {
				$fcache = 2;
			}
		}
		return $fcache;
	}

	function getFromCache() {
		global $fid,$page,$ifsort;
		//* include pwCache::getPath(S::escapePath(D_P."data/bbscache/fcache_{$fid}_{$page}.php"));
		extract(pwCache::getData(S::escapePath(D_P."data/bbscache/fcache_{$fid}_{$page}.php"), false));
		if ($page == 1 && !$ifsort) {
			foreach ($threaddb as $key => $value) {
				$value['topped'] && $ifsort = 1;
				break;
			}
		}
		return $threaddb;
	}

	function getFromDB($start, $allowtop) {
		list($offset, $limit2, $tpcdb, $R) = $this->getThreadSortWithToppedThread($allowtop, $start);
		if ($limit2) {
			global $db_datastore;
			if ($this->threadSearch->order == 'lastpost' && empty($this->threadSearch->urladd) && perf::checkMemcache() && !$R && $offset < 980) {
				//* $threadlist = L::loadClass('threadlist', 'forum');
				//* $tmpTpcdb = $threadlist->getThreads($this->fid, $offset, $limit2);
				$_cacheService = Perf::gatherCache('pw_threads');
				$tmpTpcdb = $_cacheService->getThreadListByForumId($this->fid, $offset, $limit2);
				$tpcdb = array_merge((array)$tpcdb,(array)$tmpTpcdb);
			} else {
				$sqladd = $this->threadSearch->getSqlAdd($allowtop);
				$query = $this->db->query("SELECT * FROM pw_threads t WHERE t.fid=" . S::sqlEscape($this->fid) . " $sqladd ORDER BY t.{$this->threadSearch->order} {$this->threadSearch->asc} " . S::sqlLimit($offset, $limit2));
				while ($thread = $this->db->fetch_array($query)) {
					$tpcdb[] = $thread;
				}
				$this->db->free_result($query);
				$R && $tpcdb = array_reverse($tpcdb);
			}
		}
		//$tpcdb = $this->cookThreadHits($tpcdb); 
		return $tpcdb;
	}
	
	function setWhere() {
		global $type,$special,$search,$modelid,$pcid,$actmid,$allactmid,$allpcid,$allmodelid;
		$this->threadSearch->setType($type)
		|| $this->threadSearch->setSpecial($special)
		|| $this->threadSearch->setDigest($search)
		|| $this->threadSearch->setCheck($search)
		|| $this->threadSearch->setTime($search)
		|| $this->threadSearch->setAll($search);
		
		$this->threadSearch->setModel($modelid)
		|| $this->threadSearch->setPc($pcid)
		|| $this->threadSearch->setAct($actmid)
		|| $this->threadSearch->setAllact($allactmid)
		|| $this->threadSearch->setAllpcid($allpcid)
		|| $this->threadSearch->setAllmodelid($allmodelid);
		
		$this->threadSearch->setImg($search);
		$this->threadSearch->setOrder();
	}
}

class imgThread extends baseThread {
	
	function getThreadCount() {
		return $this->db->get_value('SELECT COUNT(*) AS count FROM pw_threads_img WHERE fid=' . S::sqlEscape($this->fid) . ' AND ifcheck=1');
	}

	function getThread($start, $allowtop) {
		list($offset, $limit2, $tpcdb, $R) = $this->getThreadSortWithToppedThread(true, $start);
		$query = $this->db->query("SELECT t.*,ti.cover,ti.totalnum,ti.collectnum,ti.ifthumb FROM pw_threads_img ti LEFT JOIN pw_threads t ON ti.tid=t.tid WHERE ti.fid=" . S::sqlEscape($this->fid) . " AND ti.ifcheck=1 AND ti.topped=0 ORDER BY {$this->threadSearch->order} {$this->threadSearch->asc} " . S::sqlLimit($offset, $limit2));
		while ($thread = $this->db->fetch_array($query)) {
			$tpcdb[] = $thread;
		}
		$this->db->free_result($query);
		$R && $tpcdb = array_reverse($tpcdb);
		return $this->parseThread($tpcdb);
	}

	function setWhere() {
		global $search,$type;
		$this->threadSearch->setType($type);
		$this->threadSearch->setImg($search);
		$this->threadSearch->setOrder();
	}

	function getThreadSortWithToppedThread($allowtop, $start) {
		global $count;
		$R = 0;
		$tpcdb = array();
		$asc = $this->threadSearch->asc;
		if ($allowtop) {
			global $foruminfo,$db_perpage;
			$toptids = trim($foruminfo['topthreads'], ',');

			$rows = !$toptids ? 0 : (int)$this->db->get_value("SELECT COUNT(*) FROM pw_threads_img WHERE tid IN($toptids) LIMIT 1");;
			if ($start < $rows) {
				$L = (int)min($rows - $start, $db_perpage);
				$limit  = S::sqlLimit($start,$L);
				$offset = 0;
				$limit2 = $L == $db_perpage ? '' : $db_perpage - $L;
				if ($toptids) {
					$query = $this->db->query("SELECT t.*,ti.cover,ti.totalnum,ti.collectnum,ti.ifthumb FROM pw_threads_img ti LEFT JOIN pw_threads t ON ti.tid=t.tid WHERE ti.tid IN($toptids) ORDER BY ti.topped DESC,ti.tid DESC $limit");
					while ($rt = $this->db->fetch_array($query)) {
						$tpcdb[] = $rt;
					}
					$this->db->free_result($query);
				}
				unset($toptids,$L,$limit);
			} else {
				list($offset,$limit2,$asc,$R) = getstart($start - $rows, $asc, $count);
			}
		} else {
			list($offset,$limit2,$asc,$R) = getstart($start, $asc, $count);
		}
		$this->threadSearch->asc = $asc;
		return array($offset, $limit2, $tpcdb, $R);
	}
}

class topicsearchThread extends baseThread {

	var $tiddb = array();
	var $alltiddb = array();

	function getThreadCount() {
		global $modelid,$pcid,$actmid,$allactmid;
		$searchname = S::escapeChar(S::getGP('searchname'));
		$new_searchname = S::escapeChar(S::getGP('new_searchname'));
		$searchname && $new_searchname = StrCode(serialize($searchname));
		$count = 0;
		$this->threadSearch->urladd .= '&topicsearch=1';
		foreach ($searchname as $key => $value) {
			if (!S::isArray($value)) {
				$this->threadSearch->urladd .= "&searchname[$key]=" . rawurldecode($value);
				continue;
			}
			$urladd = '';
			foreach ($value as $k => $v) {
				$urladd .= "&searchname[$key][$k]=" . rawurldecode($v);
			}
			$this->threadSearch->urladd .= $urladd;
		}
		if ($modelid > 0) {
			list($count, $tiddb, $alltiddb) = $GLOBALS['postTopic']->getSearchvalue($new_searchname, 'one', true);
			$this->threadSearch->urladd .= "&modelid=$modelid";
			$this->threadSearch->selectSpecial = 'allmodelid';
			$this->threadSearch->selectType = 'model_' . $modelid;
		} elseif($pcid > 0) {
			list($count, $tiddb, $alltiddb) = $GLOBALS['postCate']->getSearchvalue($new_searchname, 'one', true);
			$this->threadSearch->urladd .= "&pcid=$pcid";
			$this->threadSearch->selectSpecial = 'allpcid';
			$this->threadSearch->selectType = 'pcid_' . $pcid;
		} elseif($actmid > 0 || $allactmid) {
			list($count, $tiddb, $alltiddb) = $GLOBALS['postActForBbs']->getSearchvalue($new_searchname, 'one', true, true);
			$this->threadSearch->urladd .= "&actmid=$actmid";
			$this->threadSearch->selectSpecial = 'allactmid';
			$this->threadSearch->selectType = 'actmid_' . $actmid;
		}
		if ($this->threadSearch->sqladd && $count && $alltiddb) {
			$sqladd = $this->threadSearch->getSqlAdd();
			$count = $this->db->get_value("SELECT COUNT(*) as count FROM pw_threads t WHERE t.tid IN (" . S::sqlImplode($alltiddb) . ") $sqladd");
		}
		$this->tiddb = $tiddb;
		$this->alltiddb = $alltiddb;
		return $count;
	}

	function getThread($start, $allowtop) {
		$tpcdb = array();
		if ($this->tiddb) {
			$sqladd = $this->threadSearch->getSqlAdd();
			$query = $this->db->query("SELECT * FROM pw_threads t WHERE t.tid IN (" .S::sqlImplode($this->tiddb) . ") $sqladd ORDER BY {$this->threadSearch->order} {$this->threadSearch->asc}");
			while ($thread = $this->db->fetch_array($query)) {
				$tpcdb[] = $thread;
			}
			$this->db->free_result($query);
		}
		return $this->parseThread($tpcdb);
	}

	function setWhere() {
		global $search;
		$this->threadSearch->setDigest($search)
		|| $this->threadSearch->setCheck($search)
		|| $this->threadSearch->setTime($search);
		$this->threadSearch->setOrder();
	}
}

class threadSearch {

	var $sqladd;
	var $urladd;
	var $order;
	var $asc;
	var $_ifcheck;
	var $urlall;
	var $selectType;
	var $selectSpecial;

	function threadSearch() {
		$this->sqladd = '';
		$this->urladd = '';
		$this->order = 'lastpost';
		$this->asc = 'DESC';
		$this->_ifcheck = 1;
		$this->urlall = '&search=all';
		$this->selectType = 'all';
		$this->selectSpecial = ''; 	
	}

	function setType($type) {
		global $t_db;
		if (!($t_db && is_numeric($type) && isset($t_db[$type]))) {
			return false;
		}
		if ($t_db[$type]['upid'] == 0) {
			$typeids = array();
			foreach ($t_db as $key => $value) {
				$value['upid'] == $type && $typeids[] = $key;
			}
			if ($typeids) {
				$typeids = array_merge($typeids, array($type));
				$this->sqladd .= ' AND t.type IN(' . S::sqlImplode($typeids) . ")";
			} else {
				$this->sqladd .= ' AND t.type=' . S::sqlEscape($type);
			}
		} else {
			$this->sqladd .= ' AND t.type=' . S::sqlEscape($type);
		}
		$this->urladd .= "&type=$type";
		$this->selectType = $type;
		return true;
	}

	function setSpecial($special) {
		$special = (int)$special;
		if (!($special > 0)) {
			return false;
		}
		$this->sqladd .= ' AND t.special=' . S::sqlEscape($special);
		$this->urladd .= "&special=$special";
		$this->selectSpecial = $special;
		return true;
	}

	function setDigest($search, $t='t') {
		if ($search != 'digest') {
			return false;
		}
	//	$this->urlall = "&search=$search";
		$this->sqladd .= " AND {$t}.digest>'0'";
		$this->urladd .= "&search=$search";
		$t == 'a' && $GLOBALS['tmpUrlAdd'] .= '&digest=1';
		$this->selectType = 'digest';
		$this->selectSpecial = 'digest';
		return true;
	}

	function setCheck($search) {
		if ($search != 'check') {
			return false;
		}
		if ($GLOBALS['isGM'] || $GLOBALS['pwSystem']['viewcheck']) {
			$this->sqladd .= " AND t.ifcheck='0'";
		} else {
			$this->sqladd .= ' AND t.authorid=' . S::sqlEscape($GLOBALS['winduid']) . " AND t.ifcheck='0'";
		}
		$this->urladd .= "&search=$search";
		$this->_ifcheck = 0;
		$this->selectType = 'check';
		return true;
	}

	function setTime($search) {
		if (!is_numeric($search)) {
			return false;
		}
		$this->sqladd .= " AND t.lastpost>=" . S::sqlEscape($GLOBALS['timestamp'] - $search*84600);
		$this->urladd .= "&search=$search";
		return true;
	}

	function setAll($search) {
		if ($search != 'all') {
			return false;
		}
		$this->urladd .= "&search=$search";
		$this->selectSpecial = 'all';
		return true;
	}
	
	function setImg($search) {
		if ($search != 'img') {
			return false;
		}
		
		$this->urladd .= "&search=$search";
		$this->urlall = "&search=$search";
		$this->selectSpecial = 'img';
		return true;
	}

	function setModel($modelid) {
		if (!($modelid > 0)) {
			return false;
		}
		$this->sqladd .= " AND t.modelid=" . S::sqlEscape($modelid);
		$this->urladd .= "&modelid=$modelid";
		$this->selectType = 'model_' . $modelid;
		$this->selectSpecial = 'allmodelid';
		return true;
	}
	function setAllmodelid($allmodelid) {
		if(!($allmodelid > 0)) {
			return false;
		}
		$this->sqladd .= " AND t.modelid>0";
		$this->urladd .= "&allmodelid=1";
		$this->selectSpecial = 'allmodelid';
		return true;
		
	}
	function setPc($pcid) {
		if (!($pcid > 0)) {
			return false;
		}
		$this->sqladd .= " AND t.special=" . S::sqlEscape($pcid + 20);
		$this->urladd .= "&pcid=$pcid";
		$this->selectType = 'pcid_' . $pcid;
		$this->selectSpecial = 'allpcid';
		return true;
	}

	function setAct($actmid) {
		if (!($actmid > 0)) {
			return false;
		}
		global $postActForBbs,$fid;
		$actTidDb = $postActForBbs->getActTidDb($actmid, $fid);
		if ($actTidDb) {
			$this->sqladd .= " AND t.tid IN(" . S::sqlImplode($actTidDb) . ")";
		} else {
			$this->sqladd .= " AND t.tid=0";//让其搜索为0
		}
		$this->urladd .= "&actmid=$actmid";
		$this->selectType = 'actmid_'.$actmid;
		$this->selectSpecial = 'allactmid';
		return true;
	}

	function setAllact($allactmid) {
		if (!$allactmid) {
			return false;
		}
		$this->sqladd .= " AND t.special=8";
		$this->urladd .= "&allactmid=1";
		//$this->selectType = 'activity_list';
		$this->selectSpecial = 'allactmid';
		return true;
	}
	function  setAllpcid($allpcid) {
		if(!$allpcid) {
			return false;
		}
		$this->sqladd .= " AND (t.special=21 OR t.special=22) ";
		$this->urladd .= "&allpcid=1";
		$this->selectSpecial = 'allpcid';
		return true;
	}
	function setOrder() {
		global $_G, $orderway,$asc,$forumset,$search;
		if ($_G['alloworder']) {
			if (!in_array($orderway, array('lastpost','postdate','hits','replies','favors','totalnum','tid'))) {
				$orderway = ($forumset['orderway'] && $search != 'img') ? $forumset['orderway'] : (($search == 'img') ? 'tid' : 'lastpost');
			} else {   
				$this->urladd .= "&orderway=$orderway";
			}
			$this->order = ($search == 'img') ? 'ti.'.$orderway : $orderway;
			//$ordersel[$orderway] = 'selected';

			if (!in_array($asc, array('DESC','ASC'))) {
				$asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
			} else {
				$this->urladd .= "&asc=$asc";
			}
			$this->asc = $asc;
			//$ascsel[$asc]='selected';
		} else {
			$this->asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
			$this->order = ($forumset['orderway'] && $search != 'img') ? $forumset['orderway'] : (($search == 'img') ? 'ti.tid' : 'lastpost');
		}
	}

	function getSqlAdd($allowtop = false) {
		$sqladd = $this->sqladd;
		$this->_ifcheck && $sqladd .= " AND t.ifcheck='1'";
		$allowtop && $sqladd .= ' AND t.specialsort=0';
		return $sqladd;
	}
}
function threadShield($code){
	global $groupid;
	$code = getLangInfo('bbscode',$code);
	return "<del>$code</del>";
}
function getThreadFactory($cyid, $search, $topicsearch) {
	if ($cyid) {
		$object = 'colony';
	} elseif ($search == 'img') {
		$object = 'img';
	} elseif ($topicsearch) {
		$object = 'topicsearch';
	} else {
		$object = 'common';
	}
	$object .= 'Thread';
	return new $object();
}
?>
<?php
define('SCR','thread');
require_once('global.php');
require_once(R_P.'require/forum.php');
include_once(D_P.'data/bbscache/cache_thread.php');

InitGP(array('cyid'), '', 2);
/*群组相关*/
if ($cyid) {
	!$db_groups_open && Showmsg('groups_close');
	InitGP(array('showtype'));
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	include_once(D_P . 'data/bbscache/o_config.php');
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
		require_once Pcv(R_P."require/thread_{$showtype}.php");
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

if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}

$rt = $db->get_one("SELECT fd.tpost,fd.topic,fd.article,fd.subtopic,fd.top1,fd.top2,fd.topthreads,fd.lastpost,fd.aid,fd.aids,fd.aidcache,fd.tpost,fd.topic,fd.article ,a.ifconvert,a.author,a.startdate,a.enddate,a.subject,a.content FROM pw_forumdata fd LEFT JOIN pw_announce a ON fd.aid=a.aid WHERE fd.fid=".pwEscape($fid));

$rt && $foruminfo += $rt;#版块信息合并
$forumset = $foruminfo['forumset'];
!$forumset['commend'] && $foruminfo['commend'] = null;
$foruminfo['type']=='category' && ObHeader('cate.php?cateid='.$fid);
if ($forumset['link']) {
	$flink = str_replace("&amp;","&",$forumset['link']);
	ObHeader($flink);
}

$type = (int)GetGP('type');
$toptids = trim($foruminfo['topthreads'],',');

if ($db_indexfmlogo == 2) {
	if(!empty($foruminfo['logo']) && strpos($foruminfo['logo'],'http://') === false && file_exists($attachdir.'/'.$foruminfo['logo'])){
		$foruminfo['logo'] = "$attachpath/$foruminfo[logo]";
	}
} elseif ($db_indexfmlogo == 1 && file_exists("$imgdir/$stylepath/forumlogo/$foruminfo[fid].gif")) {
	$foruminfo['logo'] = "$imgpath/$stylepath/forumlogo/$foruminfo[fid].gif";
} else {
	$foruminfo['logo'] = '';
}

if (!$cyid) {
	//门户形式浏览
	if ($foruminfo['ifcms'] && $db_modes['area']['ifopen']) {
		InitGP(array('viewbbs'));
		if (!$viewbbs) {
			require_once R_P. 'mode/area/area_thread.php';exit;
		}
		$viewbbs = $viewbbs ? "&viewbbs=$viewbbs" : "";
	}
	if ($foruminfo['cnifopen']) {
		require_once(R_P . 'apps/groups/lib/colonys.class.php');
		$colonyServer = new PW_Colony();
	}
}
if (!CkInArray($windid,$manager)) {
	wind_forumcheck ( $foruminfo );
}
//版块浏览及管理权限
$pwSystem = array();
$isGM = $isBM = $admincheck = $ajaxcheck = $managecheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
if ($groupid != 'guest') {
	L::loadClass('forum', 'forum', false);
	$isGM = CkInArray($windid,$manager);
	if ($colony) {//群组论坛浏览方式
		$ifcolonyadmin = $newColony->getColonyAdmin();
		$ifbbsadmin = $newColony->getBbsAdmin($isGM);
		$fid = $newColony->info['classid'];
		$pwforum = new PwForum($fid);
		$isBM = $pwforum->isBM($windid);
		$pwSystem = pwRights($isBM);
		if($newColony->getManageCheck($ifbbsadmin,$ifcolonyadmin)) {
			$managecheck = 1;
		}
		$pwSystem['forumcolonyright'] && $managecheck = 1;
		($ifcolonyadmin || $ifbbsadmin || $pwSystem['forumcolonyright']) && $ajaxcheck = 1;
	} else {
		$pwforum = new PwForum($fid);
		list($isBM,$admincheck,$ajaxcheck,$managecheck,$pwAnonyHide,$pwPostHide,$pwSellHide,$pwEncodeHide,$pwSystem) = $pwforum->getSystemRight();
	}
}
if (!$admincheck) {
	!$foruminfo['allowvisit'] && forum_creditcheck();#积分限制浏览
	$foruminfo['forumsell'] && forum_sell($fid);#出售版块
}

$forumset['newtime'] && $db_newtime = $forumset['newtime'];
if ($foruminfo['aid'] && ($foruminfo['startdate']>$timestamp || ($foruminfo['enddate'] && $foruminfo['enddate']<$timestamp))) {
	$foruminfo['aid'] = 0;
}

list($guidename,$forumtitle) = getforumtitle(forumindex($foruminfo['fup'],1));


/* SEO */
if ($type && is_array($foruminfo['topictype'])) {
	$_seo_type = $foruminfo['topictype'][$type];
}
$_seo = array('title'=>$foruminfo['title'],'metaDescription'=>$foruminfo['metadescrip'],'metaKeywords'=>$foruminfo['keywords']);
bbsSeoSettings('thread',$_seo,$foruminfo['name'],$_seo_type);
/* SEO */

require_once(R_P.'require/header.php');

$msg_guide = headguide($guidename);
unset($guidename,$foruminfo['forumset']);

//版主列表
$admin_T = array();
if ($foruminfo['forumadmin']) {
	$forumadmin = explode(',',$foruminfo['forumadmin']);
	foreach ($forumadmin as $key => $value) {
		if ($value) {
			if (!$db_adminshow) {
				if ($key==10) {$admin_T['admin'].='...'; break;}
				$admin_T['admin'] .= '<a href="u.php?username='.rawurlencode($value).'" class="s7">'.$value.'</a> ';
			} else {
				$admin_T['admin'] .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}
	}
	$admin_T['admin'] = '&nbsp;'.$admin_T['admin'];
}
//版主推荐
if ($forumset['commend'] && ($forumset['autocommend'] || $forumset['commendlist']) && $forumset['commendtime'] && $timestamp-$forumset['ifcommend']>$forumset['commendtime']) {
	updatecommend($fid,$forumset);
}

//版块浏览记录
$threadlog = str_replace(",$fid,",',',GetCookie('threadlog'));
$threadlog.= ($threadlog ? '' : ',').$fid.',';
substr_count($threadlog,',')>11 && $threadlog = preg_replace("/[\d]+\,/i",'',$threadlog,3);
Cookie('threadlog',$threadlog);

Update_ol();

$orderClass = array();//排序
InitGP(array('subtype','search','orderway','asc','topicsearch'));
InitGP(array('page','modelid','pcid','special','actmid','allactmid'),'GP',2);
($orderway && $asc == "DESC" ) ? $orderClass[$orderway] = "↓" : $orderClass['lastpost'] = "↓";

$searchadd = $thread_children = $thread_online = $fastpost = $updatetop = $urladd = '';

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
	$foruminfo['content'] = convert(str_replace(array("\n","\r\n"),'<br />',$foruminfo['content']),$db_windpost,'post');
}
if (strpos($_COOKIE['deploy'],"\tthread\t")===false) {
	$thread_img	 = 'fold';
	$cate_thread = '';
} else {
	$thread_img  = 'open';
	$cate_thread = 'display:none;';
}
if (strpos($_COOKIE['deploy'],"\tchildren\t")===false) {
	$children_img	 = 'fold';
	$cate_children = '';
} else {
	$children_img  = 'open';
	$cate_children = 'display:none;';
}
if ($foruminfo['cnifopen'] && $forumset['viewcolony'] && !$cyid) {
	$cnGroups = $colonyServer->getColonysInForum($fid);
}

//子版块
$forumdb = array();
if (($foruminfo['childid'] || $cnGroups) && !$cyid) {
	require_once(R_P."require/thread_child.php");
}

//快捷管理
if ($managecheck) {
	InitGP(array('concle'));
	$concle || $concle = GetCookie('concle');
	if ($concle==1 && ($isGM || $pwSystem['topped'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'])) {
		$concle = 2;$managemode = 1;
		Cookie("concle","1",0);
	} else {
		$concle = 1;$managemode = 0;
		Cookie("concle","",0);
	}
	if ($colony) {
		$trd_adminhide = "<form action=\"mawholecolony.php?$viewbbs\" method=\"post\" name=\"mawhole\"><input type=\"hidden\" name=\"cyid\" value=\"$cyid\"";
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
	$theSpecialFlag = true;
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
	$theSpecialFlag = true;
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
	$theSpecialFlag = true;
} elseif ($allactmid) { /*活动所有分类*/
	$initSearchHtml = $postActForBbs->initSearchHtml();
	$theSpecialFlag = true;
}
/*分类、团购、活动 end*/

$t_per = $foruminfo['t_type'];
$t_db = (array)$foruminfo['topictype'];
unset($foruminfo['t_type']);/* 0 */
$pwSelectType = $pwSelectSpecial = 'all';

if ($t_db && is_numeric($type) && isset($t_db[$type])) {
	if ($t_db[$type]['upid'] == 0) {
		foreach ($t_db as $key => $value) {
			$value['upid'] == $type && $typeids[] = $key;
		}
		if ($typeids) {
			$typeids = array_merge($typeids,array($type));
			$searchadd = ' AND t.type IN('.pwImplode($typeids).") AND t.ifcheck='1'";
		} else {
			$searchadd = ' AND t.type='.pwEscape($type)." AND t.ifcheck='1'";
		}
	} else {
		$searchadd = ' AND t.type='.pwEscape($type)." AND t.ifcheck='1'";
	}
	$urladd .= "&type=$type";
	$pwSelectType = $type;
} elseif ((int)$special>0) {
	$searchadd = ' AND t.special='.pwEscape($special)." AND t.ifcheck='1'";
	$urladd .= "&special=$special";
	$pwSelectSpecial = $special;
} elseif ($search == 'digest') {
	if($cyid) {
		$searchadd = " AND a.digest>'0' AND t.ifcheck='1'";
		$tmpUrlAdd .= '&digest=1';
	} else {
		$searchadd = " AND t.digest>'0' AND t.ifcheck='1'";
	}
	$urladd .= "&search=$search";
	$pwSelectType = 'digest';
} elseif ($search == 'check') {
	if ($isGM || $pwSystem['viewcheck']) {
		$searchadd = " AND t.ifcheck='0'";
	} else {
		$searchadd = ' AND t.authorid=' . pwEscape($winduid) . " AND t.ifcheck='0'";
	}
	$urladd .= "&search=$search";
	$pwSelectType = 'check';
} elseif (is_numeric($search)) {
	$searchadd = " AND t.lastpost>=" . pwEscape($timestamp - $search*84600) . " AND t.ifcheck='1'";
	$urladd .= "&search=$search";
}
if ($modelid > 0) {//选择某个信息分类中的某个模板的条件下
	$searchadd .= " AND t.modelid=".pwEscape($modelid) . " AND t.ifcheck='1'";
	$urladd .= "&modelid=$modelid";
	$pwSelectType = 'model_'.$modelid;
} elseif ($pcid > 0) {//团购
	$searchadd .= " AND t.special=".pwEscape($pcid+20) . " AND t.ifcheck='1'";
	$urladd .= "&pcid=$pcid";
	$pwSelectType = 'pcid_'.$pcid;
} elseif ($actmid > 0) {//活动子分类
	$actTidDb = $postActForBbs->getActTidDb($actmid,$fid);
	if ($actTidDb) {
		$searchadd .= " AND tid IN(".pwImplode($actTidDb).")";
	} else {
		$searchadd .= " AND tid=0";//让其搜索为0
	}
	$urladd .= "&actmid=$actmid";
	$pwSelectType = 'activity_list';
} elseif ($allactmid) { //所有活动
	$searchadd .= " AND t.special=8 AND t.ifcheck='1'";
	$urladd .= "&allactmid=1";
	$pwSelectType = 'activity_list';
}

if ($cyid) {
	$count = $searchadd ? $newColony->getArgumentCount($searchadd) : $colony['tnum'];
} elseif ($searchadd) {
	$rs = $db->get_one('SELECT COUNT(*) AS count FROM pw_threads t WHERE t.fid='.pwEscape($fid).$searchadd);
	$count = $rs['count'];
} else {
	$searchadd = " AND t.ifcheck='1'";
	$count = $foruminfo['topic'];
}

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


if (!$theSpecialFlag) {
	$count += $foruminfo['top2']+$foruminfo['top1'];
}
if (!$theSpecialFlag) {
	$sql = 'fid='.pwEscape($fid).' AND topped=0';
} else {//分类信息、团购、活动
	$sql = 'fid='.pwEscape($fid);
}

$tpcdb = $ordersel = $ascsel = array();
if ($_G['alloworder']) {
	if (!in_array($orderway,array('lastpost','postdate','hits','replies','favors'))) {
		$orderway = $forumset['orderway'] ? $forumset['orderway'] : 'lastpost';
	} else {
		$urladd .= "&orderway=$orderway";
	}
	$ordersel[$orderway] = 'selected';

	if (!in_array($asc,array('DESC','ASC'))) {
		$asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
	} else {
		$urladd .= "&asc=$asc";
	}
	$ascsel[$asc]='selected';
} else {
	$asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
	$orderway = $forumset['orderway'] ? $forumset['orderway'] : 'lastpost';
}
$condisel[$search] = 'selected';

$numofpage = ceil($count/$db_perpage);
$numofpage < 1 && $numofpage = 1;
if ($page > $numofpage) {
	$page  = $numofpage;
}
$start_limit = intval(($page-1) * $db_perpage);
$totalpage	= min($numofpage,$db_maxpage);

$pageUrl	= "thread.php?" . ($cyid ? "cyid=$cyid" : "fid=$fid");
$pages		= numofpage($count,$page,$numofpage,"{$pageUrl}{$urladd}$viewbbs&",$db_maxpage);
$attachtype	= array('1'=>'img','2'=>'txt','3'=>'zip');

$fcache = 0;
if ($db_fcachenum && $page <= $db_fcachenum && empty($urladd) && $topicsearch != 1 && !$cyid) {
	$fcachetime = pwFilemtime(D_P."data/bbscache/fcache_{$fid}_{$page}.php");
	$lastpost = explode("\t",$foruminfo['lastpost']);
	if (!file_exists(D_P."data/bbscache/fcache_{$fid}_{$page}.php") || $lastpost[2]>$fcachetime && $timestamp-$fcachetime>$db_fcachetime) {
		$fcache = 1;
	} else {
		$fcache = 2;
	}
}
$threaddb = array();

if ($fcache < 2) {
	$R = 0;
	if (!$theSpecialFlag && !$cyid) {
		$rows = (int)($foruminfo['top2'] + $foruminfo['top1']);
		if ($start_limit < $rows) {
			$L = (int)min($rows-$start_limit,$db_perpage);
			$limit  = pwLimit($start_limit,$L);
			$offset = 0;
			$limit2 = $L == $db_perpage ? '' : $db_perpage-$L;
			if ($toptids) {
				$query = $db->query("SELECT * FROM pw_threads WHERE tid IN($toptids) ORDER BY topped DESC,lastpost DESC $limit");
				while ($rt = $db->fetch_array($query)) {
					$tpcdb[] = $rt;
				}
				$db->free_result($query);
			}
			unset($toptids,$L,$limit);
		} else {
			list($st,$lt,$asc,$R) = getstart($start_limit-$rows,$asc,$count);
			$offset = $st; $limit2 = $lt;
		}
		unset($rows);
	} else {
		list($st,$lt,$asc,$R) = getstart($start_limit,$asc,$count);
		$offset = $st; $limit2 = $lt;
	}

	if ($cyid) {
		$tpcdb = $newColony->getArgument($searchadd, $offset, $limit2, $orderway, $asc);
		$R && $tpcdb = array_reverse($tpcdb);
	} elseif ($topicsearch == 1) {
		InitGP(array('searchname','new_searchname'));
		$pcsqladd = '';
		if ($search == 'digest') {
			$pcsqladd .= " AND digest>'0' AND ifcheck='1'";
		} elseif ($search == 'check') {
			if ($isGM || $pwSystem['viewcheck']) {
				$pcsqladd .= " AND ifcheck='0'";
			} else {
				$pcsqladd .= ' AND authorid='.pwEscape($winduid)." AND ifcheck='0'";
			}
		} elseif (is_numeric($search)) {
			$pcsqladd .= " AND lastpost>=".pwEscape($timestamp - $search*84600)." AND ifcheck='1'";
		}

		$searchname && $new_searchname = StrCode(serialize($searchname));
		if ($modelid > 0) {
			list($count,$tiddb,$alltiddb) = $postTopic->getSearchvalue($new_searchname,'one',true);
		} elseif($pcid > 0) {
			list($count,$tiddb,$alltiddb) = $postCate->getSearchvalue($new_searchname,'one',true);
		} elseif($actmid > 0 || $allactmid) {
			list($count,$tiddb,$alltiddb) = $postActForBbs->getSearchvalue($new_searchname,'one',true,true);
		}

		if ($search && $count && $alltiddb) {
			$count = $db->get_value("SELECT COUNT(*) as count FROM pw_threads WHERE tid IN (".pwImplode($alltiddb).") $pcsqladd");
		}

		$numofpage = ceil($count/$db_perpage);
		$numofpage < 1 && $numofpage = 1;
		if ($page > $numofpage) {
			$page  = $numofpage;
		}
		$totalpage	= min($numofpage,$db_maxpage);
		$count == -1 && $count = 0;
		$pages		= numofpage($count,$page,$numofpage,"thread.php?fid=$fid&pcid=$pcid&topicsearch=$topicsearch&new_searchname=$new_searchname&search=$search&orderway=$orderway&asc=$asc&",$db_maxpage);
		$tpcdb = array();
		if ($tiddb){
			$query = $db->query("SELECT * FROM pw_threads WHERE tid IN (".pwImplode($tiddb).") $pcsqladd ORDER BY $orderway $asc");
			while ($thread = $db->fetch_array($query)) {
				$tpcdb[] = $thread;
			}
			$db->free_result($query);
		}

	} elseif ($limit2) {
		if ($orderway == 'lastpost' && empty($urladd) && strtolower($db_datastore) == 'memcache' && !$R && $offset < 980) {
			$threadlist = L::loadClass("threadlist", 'forum');
			$tmpTpcdb = $threadlist->getThreads($fid,$offset,$limit2);
			$tpcdb = array_merge((array)$tpcdb,(array)$tmpTpcdb);
		} else {
			$query = $db->query("SELECT * FROM pw_threads t WHERE $sql $searchadd ORDER BY t.$orderway $asc ".pwLimit($offset,$limit2));
			while ($thread = $db->fetch_array($query)) {
				$tpcdb[] = $thread;
			}
			$db->free_result($query);
			$R && $tpcdb = array_reverse($tpcdb);
		}
	}

	//Start Here pwcache
	if (($db_ifpwcache&112) && pwFilemtime(D_P.'data/bbscache/hitsort_judge.php')<$timestamp-600) {
		include_once(D_P.'data/bbscache/hitsort_judge.php');
		$updatelist = $updatetype = array();
		foreach ($tpcdb as $thread) {
			if ($db_ifpwcache & 16) {
				if ($thread['hits']>$hitsort_judge['hitsort'][$fid] && $thread['fid']==$fid) {
					$updatelist[] = array('hitsort',$fid,$thread['tid'],$thread['hits'],'',0);
					$updatetype['hitsort'] = 1;
				}
			}
			if ($db_ifpwcache & 32 && $thread['postdate']>$timestamp-24*3600) {
				if ($thread['hits']>$hitsort_judge['hitsortday'][$fid] && $thread['fid']==$fid) {
					$updatelist[] = array('hitsortday',$fid,$thread['tid'],$thread['hits'],$thread['postdate'],0);
					$updatetype['hitsortday'] = 1;
				}
			}

			if ($db_ifpwcache & 64 && $thread['postdate']>$timestamp-7*24*3600) {
				if ($thread['hits']>$hitsort_judge['hitsortweek'][$fid] && $thread['fid']==$fid) {
					$updatelist[] = array('hitsortweek',$fid,$thread['tid'],$thread['hits'],$thread['postdate'],0);
					$updatetype['hitsortweek'] = 1;
				}
			}
		}
		if ($updatelist) {
			L::loadClass('elementupdate', '', false);
			$elementupdate = new ElementUpdate($fid);
			$elementupdate->setJudge('hitsort',$hitsort_judge);
			$elementupdate->setUpdateList($updatelist);
			$elementupdate->setUpdateType($updatetype);
			$elementupdate->updateSQL();
			unset($elementupdate);
		}
		unset($updatelist,$updatetype,$hitsort_judge);
	}
	//End Here
	$pwAnonyHide = $isGM || $pwSystem['anonyhide'];
	$rewids = $cyids = array();
	$arrStatus = array(1=>'vote',2=>'active',3=>'reward',4=>'trade',5=>'debate');
	foreach ($tpcdb as $key => $thread) {
		$foruminfo['allowhtm'] == 1 && $htmurl = $db_readdir.'/'.$fid.'/'.date('ym',$thread['postdate']).'/'.$thread['tid'].'.html';
		$thread['tpcurl'] = "read.php?tid={$thread[tid]}$viewbbs".($page>1 ? "&fpage=$page" : '');
		if ($managemode == 1) {
			$thread['tpcurl'] .= '&toread=1';
		} elseif (!$foruminfo['cms'] && $foruminfo['allowhtm']==1 && file_exists(R_P.$htmurl)) {
			$thread['tpcurl'] = "$htmurl";
		}
		if ($thread['toolfield']) {
			list($t,$e) = explode(',',$thread['toolfield']);
			$sqladd = '';
			if ($t && $t<$timestamp) {
				$sqladd .= ",toolinfo='',topped='0'";$t='';
				$thread['topped']>0 && $updatetop=1;
			}
			if ($e && $e<$timestamp) {
				$sqladd .= ",titlefont=''";$thread['titlefont']='';$e='';
			}
			if ($sqladd) {
				$thread['toolfield'] = $t.($e ? ','.$e : '');
				$db->update("UPDATE pw_threads SET toolfield=".pwEscape($thread['toolfield'])." $sqladd WHERE tid=".pwEscape($thread['tid']));
				/* clear thread cache*/
				$threads = L::loadClass('Threads', 'forum');
				$threads->delThreads($thread['tid']);
			}
		}
		$forumset['cutnums'] && $thread['subject'] = substrs($thread['subject'],$forumset['cutnums']);
		if ($thread['titlefont']) {
			$titledetail = explode("~",$thread['titlefont']);
			if ($titledetail[0]) $thread['subject'] = "<font color=$titledetail[0]>$thread[subject]</font>";
			if ($titledetail[1]) $thread['subject'] = "<b>$thread[subject]</b>";
			if ($titledetail[2]) $thread['subject'] = "<i>$thread[subject]</i>";
			if ($titledetail[3]) $thread['subject'] = "<u>$thread[subject]</u>";
		}
		if ($thread['ifmark']) {
			$thread['ifmark'] = $thread['ifmark']>0 ? " <span class='gray tpage'>( +$thread[ifmark] )</span> " : " <span class='gray tpage w'>( $thread[ifmark] )</span> ";
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
		$thread['topped'] && $ifsort=1;
		$thread['ispage'] = '';
		if ($thread['topreplays']+$thread['replies']+1>$db_readperpage) {
			$numofpage = ceil(($thread['topreplays']+$thread['replies']+1)/$db_readperpage);
			$fpage = $page > 1 ? "&fpage=$page" : "";
			$thread['ispage']=' ';
			$thread['ispage'].=" <img src=\"$imgpath/$stylepath/file/multipage.gif\" align=\"absmiddle\" alt=\"pages\"> <span class=\"tpage\">";
			for($j=1; $j<=$numofpage; $j++) {
				if ($j==6 && $j+1<$numofpage) {
					$thread['ispage'].=" .. <a href=\"read.php?tid=$thread[tid]$fpage&page=$numofpage\">$numofpage</a>";
					break;
				} elseif ($j == 1) {
					$thread['ispage'].=" <a href=\"read.php?tid=$thread[tid]$fpage\">$j</a>";
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
		if ($managecheck) {
			if ($thread['fid'] == $fid) {
				$thread['adminbox'] = "<input type=\"checkbox\" autocomplete=\"off\" name=\"tidarray[]\" id=tid_{$thread[tid]} value=\"$thread[tid]\" onclick=\"postManager.show(this,event)\" onmouseover=\"postManager.manager(this,event)\"/>";
			} else {
				$thread['adminbox'] = "&nbsp;&nbsp;&nbsp;";
			}
		}
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
		$threaddb[$thread['tid']] = $thread;
	}

	if ($rewids) {
		$rewids = pwImplode($rewids);
		$query = $db->query("SELECT tid,cbval,caval FROM pw_reward WHERE tid IN($rewids)");
		while ($rt = $db->fetch_array($query)) {
			$threaddb[$rt['tid']]['rewcredit'] = $rt['cbval'] + $rt['caval'];
		}
	}
	if ($cyids && !$cyid) {
		$query = $db->query("SELECT a.tid,a.cyid,c.cname FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE tid IN (" . pwImplode($cyids) . ')');
		while ($rt = $db->fetch_array($query)) {
			$threaddb[$rt['tid']]['colony'] = $rt;
		}
	}
	if ($topicids) {
		$topicvaluetable = GetTopcitable($modelid);
		$query = $db->query("SELECT * FROM $topicvaluetable WHERE tid IN (" .pwImplode($topicids). ")");
		while ($rt = $db->fetch_array($query)) {
			$threaddb[$rt['tid']]['topic'] = $rt;
		}
	}
	if ($postcatepcids) {//团购
		$pcvaluetable = GetPcatetable($pcid);
		$query = $db->query("SELECT * FROM $pcvaluetable WHERE tid IN (" .pwImplode($postcatepcids). ")");
		while ($rt = $db->fetch_array($query)) {
			$threaddb[$rt['tid']]['topic'] = $rt;
		}
	}
	if ($activitytiddb) {//活动
		$defaultValueTableName = getActivityValueTableNameByActmid();
		if ($actmid) {
			$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
			$query = $db->query("SELECT actmid,recommend,starttime,endtime,location,contact,telephone,picture1,picture2,picture3,picture4,picture5,signupstarttime,signupendtime,minparticipant,maxparticipant,userlimit,specificuserlimit,genderlimit,fees,feesdetail,paymethod,ut.* FROM $defaultValueTableName dt LEFT JOIN $userDefinedValueTableName ut USING(tid) WHERE dt.tid IN(".pwImplode($activitytiddb). ")");

		} else {
			$query = $db->query("SELECT * FROM $defaultValueTableName WHERE tid IN(".pwImplode($activitytiddb). ")");
		}
		while ($rt = $db->fetch_array($query)) {
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

	if ($updatetop) {
		require_once(R_P.'require/updateforum.php');
		updatetop();
	}
	if ($fcache == 1) {
		writeover(D_P."data/bbscache/fcache_{$fid}_{$page}.php", "<?php\r\n\$threaddb=".pw_var_export($threaddb).";\r\n?>");
	}
	unset($tpcdb,$query,$searchadd,$sql,$limit2,$R,$p_status,$updatetop,$rewids,$arrStatus);
} else {
	include_once Pcv(D_P."data/bbscache/fcache_{$fid}_{$page}.php");
	if ($page == 1 && !$ifsort) {
		foreach ($threaddb as $key => $value) {
			$value['topped'] && $ifsort = 1;
			break;
		}
	}
}


if ($groupid != 'guest') {
	$_G['allowpost'] && $db_threadshowpost == 1 && $fastpost = 'fastpost';
	if (!$pwforum->allowpost($winddb,$groupid)) {
		$fastpost = '';
	} else {
		$pwforum->foruminfo['allowpost'] && $db_threadshowpost && $fastpost = 'fastpost';
	}
}
$psot_sta = $titletop1 = '';

$t_exits  = 0;
$t_typedb = $t_subtypedb = array();
if ($t_db) {
	foreach ($t_db as $value) {
		if ($value['upid'] == 0) {
			$t_typedb[$value['id']] = $value;
		} else {
			$t_subtypedb[$value['upid']][$value['id']] = strip_tags($value['name']);
		}
		$t_exits = 1;
	}
}
$t_childtypedb = $t_subtypedb;
foreach ($t_typedb as $value) {
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
require_once PrintEot('thread');
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
?>
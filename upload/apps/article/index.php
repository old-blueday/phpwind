<?php
!defined('A_P') && exit('Forbidden');

$USCR = 'user_article';
//* require_once pwCache::getPath(D_P."data/bbscache/forum_cache.php");
pwCache::getData(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/showimg.php');
//* @include_once pwCache::getPath(D_P."data/bbscache/topic_config.php");
pwCache::getData(D_P."data/bbscache/topic_config.php");
//* @include_once pwCache::getPath(D_P."data/bbscache/postcate_config.php");
pwCache::getData(D_P."data/bbscache/postcate_config.php");
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
!$winduid && Showmsg('not_login');
S::gp(array('a','uid','space'),null,1);
$page = (int)S::getGP('page');
$db_perpage = 20;
//$u = (int)S::getGP('u');
!$u && $u = $winduid;
$isU = false;
$u = !isset($uid) ? $u : $uid;
$u == $winduid && $isU = true;
$basename = 'apps.php?q='.$q.'&';
if ($uid) {
	$basename .= 'uid='.$uid.'&';
}
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
$space =& $newSpace->getInfo();
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');
require_once(R_P.'require/functions.php');
$fids = trim(getSpecialFid() . ",'0'",',');
$shortcutforum = pwGetShortcut();

$force = $where = '';
$article = array();
$ordertype = 'postdate';


$a = isset($a) ? $a : 'list';
if ($a == 'list' &&$indexRight) {
	S::gp(array('ordertype'));
	S::gp(array('see'));
	$see = !isset($see) ? 'topic' : $see;
	if ($see == 'topic') {
		!in_array($ordertype,array('lastpost','postdate')) && $ordertype = 'postdate';
		S::gp(array('posttime'));
		if ($u!=$winduid) {
			$where .= 'authorid='.S::sqlEscape($u).' AND anonymous=0 AND ifhide=0 AND fid<>0 ';
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($u);
			$thisbase = $basename.'ordertype='.$ordertype.'&';
		} else {
			$where .= 'authorid='.S::sqlEscape($winduid);
			$thisbase = $basename.'ordertype='.$ordertype.'&posttime='.$posttime.'&';
			$username = $windid;
			$u = $winduid;
		}
		if (is_numeric($posttime) && $posttime) {
			if ($posttime != '366') {
				$where .= " AND postdate>=".S::sqlEscape($timestamp - $posttime*84600);
			} elseif ($posttime == '366') {
				$where .= " AND postdate<=".S::sqlEscape($timestamp - 365*84600);
			}
		}
	} elseif ($see == 'post') {
		S::gp(array('ptable'), 'GP', 2);
		$ischeck = (int) $ischeck;
		$username = $windid;
		if ($u != $winduid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($u);
		}
		!isset($ptable) && $ptable = $db_ptable;
		$pw_posts = !$db_merge_posts ? GetPtable($ptable) : 'pw_merge_posts';		
		$fidoff = $isU ? array(0) : getFidoff($groupid);
		$sqloff = ' AND p.fid NOT IN(' . S::sqlImplode($fidoff) . ')';
		$count = $db->get_value("SELECT COUNT(*) AS count FROM $pw_posts p WHERE p.authorid=" . S::sqlEscape($u) . " $sqloff");
		$nurl = $basename.'see=post&';
		$p_list = S::isArray($db_plist) ? $db_plist : array();
		if (!$db_merge_posts && $p_list) {	
			foreach ($p_list as $key => $val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$p_table .= "<li id=\"up_post$key\"><a href=\"{$nurl}ptable=$key\">".$name."</a></li>";
			}
			$nurl .= "ptable=$ptable&";
		}
		list($pages, $limit) = pwLimitPages($count, $page, $nurl);
		$query = $db->query("SELECT p.pid,p.postdate,t.tid,t.fid,t.subject,t.authorid,t.author,t.replies,t.hits,t.titlefont,t.anonymous,t.lastpost,t.lastposter FROM $pw_posts p LEFT JOIN pw_threads t USING(tid) WHERE p.authorid=" . S::sqlEscape($u) . " $sqloff ORDER BY p.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)){
			$rt['subject']	= substrs($rt['subject'], 45);
			if ($rt['anonymous'] && $rt['authorid'] != $winduid && !$isGM) {
				$rt['author'] = $db_anonymousname;
				$rt['authorid'] = 0;
			}
			$rt['forum']	= strip_tags($forum[$rt['fid']]['name']);
			$rt['postdate']	= formateDate($rt['postdate']);
			$rt['lastpost']	= formateDate($rt['lastpost']);
			$article[]		= $rt;
		}

	}
} elseif ($a == 'friend') {
	$thisbase = $basename.'a=friend&';
	if ($friends = getFriends($winduid)) {
		$uids = array_keys($friends);
		!empty($fids) && $where .= "fid NOT IN($fids) AND ";
		$where .= "authorid IN(".S::sqlImplode($uids).") AND ifcheck=1 AND ifhide=0 AND anonymous=0";
		$where .= " AND postdate>".S::sqlEscape($timglimit);
	} else {
		//require_once(M_P.'require/header.php');
		//require_once PrintEot('m_article');
		//footer();
		list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_article",true);
	}
	$username = $windid;

} elseif ($a == 'pc') {
	S::gp(array('pcid'),GP,2);
	S::gp(array('see'));
	$pcid = (int)$pcid;
	!$pcid && $pcid = $db->get_value("SELECT pcid FROM pw_postcate ORDER BY vieworder");
	!$see && $see = 'myjoin';
	$article = array();
	$special = $pcid + 20;
	$tablename = GetPcatetable($pcid);

	if ($see == 'mypost') {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_threads t WHERE t.authorid=".S::sqlEscape($winduid)." AND t.special=".S::sqlEscape($special)." AND t.fid!=0");
		if ($count) {
			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=pc&pcid=$pcid&see=mypost&");
			$query = $db->query("SELECT t.tid,t.authorid,t.author,t.lastposter,t.subject,pv.begintime,pv.endtime,pv.limitnum,SUM(pm.nums) as nums FROM pw_threads t LEFT JOIN $tablename pv ON t.tid=pv.tid LEFT JOIN pw_pcmember pm ON t.tid=pm.tid WHERE t.authorid=".S::sqlEscape($winduid)." AND t.special=".S::sqlEscape($special)." AND t.fid!=0 GROUP BY t.tid ORDER BY t.postdate DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$article[] = $rt;
			}
		}
	} elseif ($see == 'myjoin') {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_pcmember pm LEFT JOIN $tablename pv ON pm.tid=pv.tid LEFT JOIN pw_threads t ON pm.tid=t.tid WHERE pm.uid=".S::sqlEscape($winduid)." AND t.special=".S::sqlEscape($special)." AND t.fid != 0");
		if ($count) {
			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=pc&pcid=$pcid&see=myjoin&");
			$query = $db->query("SELECT t.tid,t.authorid,t.author,t.lastposter,t.subject,pv.endtime,pv.payway,pm.nums,pm.ifpay,pm.pcmid FROM pw_pcmember pm LEFT JOIN $tablename pv ON pm.tid=pv.tid LEFT JOIN pw_threads t ON pm.tid=t.tid WHERE pm.uid=".S::sqlEscape($winduid)." AND t.special=".S::sqlEscape($special)." ORDER BY t.postdate DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$article[] = $rt;
			}
		}
	}

} elseif ($a == 'goods') {

		S::gp(array('job'));
		$job = empty($job) ? 'trade' : $job;
		if ($u != $winduid && empty($job)){
			$job = 'onsale';
		}
		if ($u!=$winduid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($u);
		} else {
			$username = $windid;
		}

		if ($job == 'trade') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_tradeorder WHERE buyer=".S::sqlEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=$a&");

			$trade = array();
			$query = $db->query("SELECT td.*,t.icon,m.username FROM pw_tradeorder td LEFT JOIN pw_trade t ON td.tid=t.tid LEFT JOIN pw_members m ON td.seller=m.uid WHERE td.buyer=".S::sqlEscape($u).' ORDER BY td.oid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		} elseif ($job == 'onsale') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_trade WHERE uid=".S::sqlEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=$a&job=$job&");

			$trade = array();
			$query = $db->query("SELECT * FROM pw_trade WHERE uid=".S::sqlEscape($u).' ORDER BY tid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		} elseif ($job == 'saled') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_tradeorder WHERE seller=".S::sqlEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=$a&job=$job&");

			$trade = array();
			$query = $db->query("SELECT td.*,t.icon,m.username FROM pw_tradeorder td LEFT JOIN pw_trade t ON td.tid=t.tid LEFT JOIN pw_members m ON td.buyer=m.uid WHERE td.seller=".S::sqlEscape($u).' ORDER BY td.oid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		}


}


if ($where) {
	$u != $winduid && $where .= ' AND fid NOT IN(' . S::sqlImplode(getFidoff($groupid)) . ')';
	$count = $db->get_value("SELECT count(*) as count FROM pw_threads WHERE $where AND fid != 0");
	if ($count) {
		list($pages,$limit) = pwLimitPages($count,$page,$thisbase);
		!isset($userService) && $userService = L::loadClass('UserService', 'user'); 
		$rs = $db->query("SELECT tid,fid,author,authorid,subject,postdate,lastpost,lastposter,replies,hits,titlefont,anonymous,modelid,special FROM pw_threads $force WHERE $where AND fid != 0 ORDER BY $ordertype DESC $limit");
		while ($rt = $db->fetch_array($rs)) {
			$rt['subject'] = substrs($rt['subject'],45);
			$rt['forum'] = strip_tags($forum[$rt['fid']]['name']);
			$rt['postdate'] = formateDate($rt['postdate']);
			$rt['lastpost']	= formateDate($rt['lastpost']);
	//		$rt['authorid'] == $winduid && $rt['lastposter'] = $windid;
			$rt['pcid'] = $rt['special'] > 20 ? $rt['special'] - 20 : 0;
			if (!$isGM && $rt['anonymous']) {
				$rt['author'] = $rt['authorid'] = '';
			}
			$userInfo = $userService->get($rt['authorid']);
			if($userInfo['groupid'] == 6 && !$isGM){
				$rt['subject']	= '<span style=\"color:black;background-color:#ffff66\">用户被禁言,该主题自动屏蔽!</span>';
			}
			$article[] = $rt;
		}
	}
}

/*用户被禁言情况*/
/*if($u != $winduid){
	!isset($userService) && $userService = L::loadClass('UserService', 'user'); 
	$userInfo = $userService->get($u);
	$gid = $userInfo['groupid'];
}else{
	$gid = $groupid;
}
if($gid == 6 && !$isGM){
	foreach($article as $k=>$v){
		$article[$k]['subject']	= '<span style=\"color:black;background-color:#ffff66\">用户被禁言,该主题自动屏蔽!</span>';
	}	
}*/
/*end*/

if ($uid) {
	$isSpace = true;
	$USCR = 'space_article';
	require_once(R_P.'require/credit.php');
	require_once PrintEot('m_space_article');
	pwOutPut();
} else {
	//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
	pwCache::getData(D_P . 'data/bbscache/o_config.php');

	require_once PrintEot('m_article');
	pwOutPut();
}


function goodsicon($icon) {
	global $attachpath,$imgpath;
	if (empty($icon)) {
		return $imgpath.'/noproduct.gif';
	}
	if (file_exists($attachpath.'/thumb/'.$icon)) {
		return $attachpath.'/thumb/'.$icon;
	}
	if (file_exists($attachpath.'/'.$icon)) {
		return $attachpath.'/'.$icon;
	}
	return $imgpath.'/noproduct.gif';
}
function getFidoff($gid) {
	global $db;
	$fidoff = array(0);
	$query = $db->query("SELECT fid FROM pw_forums WHERE type<>'category' AND (password!='' OR forumsell!='' OR allowvisit!='' AND allowvisit NOT LIKE '%,$gid,%')");
	while ($rt = $db->fetch_array($query)) {
		$fidoff[] = $rt['fid'];
	}
	return $fidoff;
}
function formateDate($time) {
	$temp = getLastDate($time,0);
	return $temp[0];
}
?>
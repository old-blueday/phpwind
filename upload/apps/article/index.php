<?php
!defined('A_P') && exit('Forbidden');

$USCR = 'user_article';
require_once(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/showimg.php');
@include_once(D_P."data/bbscache/topic_config.php");
@include_once(D_P."data/bbscache/postcate_config.php");
include_once(D_P . 'data/bbscache/o_config.php');

$isGM = CkInArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
!$winduid && Showmsg('not_login');
InitGP(array('a','uid','space'),null,1);
$page = (int)GetGP('page');
$db_perpage = 20;
//$u = (int)GetGP('u');
!$u && $u = $winduid;
$isU = false;
$u == $winduid && $isU = true;

$u = !isset($uid) ? $u : $uid;

$basename = 'apps.php?q='.$q.'&';
if ($uid) {
	$basename .= 'uid='.$uid.'&';
}
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
$isGM = CkInArray($windid, $manager);
!$isGM && $groupid==3 && $isGM=1;
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
$space =& $newSpace->getInfo();
include_once(D_P.'data/bbscache/forum_cache.php');
require_once(R_P.'require/functions.php');
$fids = trim(getSpecialFid() . ",'0'",',');
$shortcutforum = pwGetShortcut();

$force = $where = '';
$article = array();
$ordertype = 'postdate';


$a = isset($a) ? $a : 'list';
if ($a == 'list' &&$indexRight) {
	InitGP(array('ordertype'));
	InitGP(array('see'));
	$see = !isset($see) ? 'topic' : $see;
	if ($see == 'topic') {
		!in_array($ordertype,array('lastpost','postdate')) && $ordertype = 'postdate';
		InitGP(array('posttime'));
		if ($u!=$winduid) {
			$where .= 'authorid='.pwEscape($u).' AND anonymous=0 AND ifhide=0 AND fid<>0 ';
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($u);
			$thisbase = $basename.'ordertype='.$ordertype.'&';
		} else {
			$where .= 'authorid='.pwEscape($winduid);
			$thisbase = $basename.'ordertype='.$ordertype.'&posttime='.$posttime.'&';
			$username = $windid;
			$u = $winduid;
		}
		if (is_numeric($posttime) && $posttime) {
			if ($posttime != '366') {
				$where .= " AND postdate>=".pwEscape($timestamp - $posttime*84600);
			} elseif ($posttime == '366') {
				$where .= " AND postdate<=".pwEscape($timestamp - 365*84600);
			}
		}
	} elseif ($see == 'post') {
		InitGP(array('ptable'));
		if ($u!=$winduid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($u);
		} else {
			$username = $windid;
		}

		!isset($ptable) && $ptable = $db_ptable;
		$pw_posts = GetPtable($ptable);

		$fidoff = $isU ? array(0) : getFidoff($groupid);
		$sqloff = ' AND p.fid NOT IN('.pwImplode($fidoff).')';

		$count = $db->get_value("SELECT COUNT(*) AS count FROM $pw_posts p WHERE p.authorid=".pwEscape($u)." $sqloff");
		$nurl = $basename.'see=post&';
		$p_list = $db_plist && count($db_plist)>1 ? $db_plist : array();
		if ($p_list) {
			if ($space == 1 && defined('F_M')) {
				foreach ($p_list as $key => $val) {
					$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
					$p_table .= "<li id=\"up_post$key\"><a href=\"{$nurl}ptable=$key\">".$name."</a></li>";
				}
			} else {
				foreach ($p_list as $key => $val) {
					$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
					$p_table .= "<li><a id=\"up_post$key\" href=\"{$nurl}ptable=$key\">".$name."</a></li>";
				}
			}

			$nurl .= "ptable=$ptable&";
		}

		list($pages,$limit) = pwLimitPages($count,$page,$nurl);

		$query = $db->query("SELECT p.pid,p.postdate,t.tid,t.fid,t.subject,t.authorid,t.author,t.replies,t.hits,t.titlefont,t.anonymous FROM $pw_posts p LEFT JOIN pw_threads t USING(tid) WHERE p.authorid=".pwEscape($u)." $sqloff ORDER BY p.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)){
			$rt['subject']	= substrs($rt['subject'],45);
			if ($rt['anonymous'] && $rt['authorid'] != $winduid && !$isGM) {
				$rt['author'] = $db_anonymousname;
				$rt['authorid'] = 0;
			}
			$rt['forum']	= strip_tags($forum[$rt['fid']]['name']);
			$rt['postdate']	= get_date($rt['postdate'],'Y-m-d H:i:s');
			$article[]		= $rt;
		}

	}
} elseif ($a == 'friend') {
	$thisbase = $basename.'a=friend&';
	if ($friends = getFriends($winduid)) {
		$uids = array_keys($friends);
		!empty($fids) && $where .= "fid NOT IN($fids) AND ";
		$where .= "authorid IN(".pwImplode($uids).") AND ifcheck=1 AND ifhide=0 AND anonymous=0";
		$where .= " AND postdate>".pwEscape($timglimit);
	} else {
		//require_once(M_P.'require/header.php');
		//require_once PrintEot('m_article');
		//footer();
		list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_article",true);
	}
	$username = $windid;

} elseif ($a == 'pc') {
	InitGP(array('pcid'),GP,2);
	InitGP(array('see'));
	$pcid = (int)$pcid;
	!$pcid && $pcid = $db->get_value("SELECT pcid FROM pw_postcate ORDER BY vieworder");
	!$see && $see = 'myjoin';
	$article = array();
	$special = $pcid + 20;
	$tablename = GetPcatetable($pcid);

	if ($see == 'mypost') {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_threads t WHERE t.authorid=".pwEscape($winduid)." AND t.special=".pwEscape($special)." AND t.fid!=0");
		if ($count) {
			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=pc&pcid=$pcid&see=mypost&");
			$query = $db->query("SELECT t.tid,t.authorid,t.author,t.subject,pv.begintime,pv.endtime,pv.limitnum,SUM(pm.nums) as nums FROM pw_threads t LEFT JOIN $tablename pv ON t.tid=pv.tid LEFT JOIN pw_pcmember pm ON t.tid=pm.tid WHERE t.authorid=".pwEscape($winduid)." AND t.special=".pwEscape($special)." AND t.fid!=0 GROUP BY t.tid ORDER BY t.postdate DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$article[] = $rt;
			}
		}
	} elseif ($see == 'myjoin') {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_pcmember pm LEFT JOIN $tablename pv ON pm.tid=pv.tid LEFT JOIN pw_threads t ON pm.tid=t.tid WHERE pm.uid=".pwEscape($winduid)." AND t.special=".pwEscape($special)." AND t.fid != 0");
		if ($count) {
			list($pages,$limit) = pwLimitPages($count,$page,$basename."a=pc&pcid=$pcid&see=myjoin&");
			$query = $db->query("SELECT t.tid,t.authorid,t.author,t.subject,pv.endtime,pv.payway,pm.nums,pm.ifpay,pm.pcmid FROM pw_pcmember pm LEFT JOIN $tablename pv ON pm.tid=pv.tid LEFT JOIN pw_threads t ON pm.tid=t.tid WHERE pm.uid=".pwEscape($winduid)." AND t.special=".pwEscape($special)." ORDER BY t.postdate DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$article[] = $rt;
			}
		}
	}

} elseif ($a == 'goods') {

		InitGP(array('job'));
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
			$count = $db->get_value("SELECT COUNT(*) FROM pw_tradeorder WHERE buyer=".pwEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."see=$see&");

			$trade = array();
			$query = $db->query("SELECT td.*,t.icon,m.username FROM pw_tradeorder td LEFT JOIN pw_trade t ON td.tid=t.tid LEFT JOIN pw_members m ON td.seller=m.uid WHERE td.buyer=".pwEscape($u).' ORDER BY td.oid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		} elseif ($job == 'onsale') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_trade WHERE uid=".pwEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."see=$see&job=$job&");

			$trade = array();
			$query = $db->query("SELECT * FROM pw_trade WHERE uid=".pwEscape($u).' ORDER BY tid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		} elseif ($job == 'saled') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_tradeorder WHERE seller=".pwEscape($u));

			list($pages,$limit) = pwLimitPages($count,$page,$basename."see=$see&job=$job&");

			$trade = array();
			$query = $db->query("SELECT td.*,t.icon,m.username FROM pw_tradeorder td LEFT JOIN pw_trade t ON td.tid=t.tid LEFT JOIN pw_members m ON td.buyer=m.uid WHERE td.seller=".pwEscape($u).' ORDER BY td.oid DESC '.$limit);
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = goodsicon($rt['icon']);
				$trade[] = $rt;
			}

		}


}


if ($where) {
	$count = $db->get_value("SELECT count(*) as count FROM pw_threads WHERE $where AND fid != 0");
	if ($count) {
		list($pages,$limit) = pwLimitPages($count,$page,$thisbase);

		$rs = $db->query("SELECT tid,fid,author,authorid,subject,postdate,lastpost,replies,hits,titlefont,anonymous,modelid,special FROM pw_threads $force WHERE $where AND fid != 0 ORDER BY $ordertype DESC $limit");
		while ($rt = $db->fetch_array($rs)) {
			$rt['subject'] = substrs($rt['subject'],45);
			$rt['forum'] = strip_tags($forum[$rt['fid']]['name']);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['pcid'] = $rt['special'] > 20 ? $rt['special'] - 20 : 0;
			if (!$isGM && $rt['anonymous']) {
				$rt['author'] = $rt['authorid'] = '';
			}
			$article[] = $rt;
		}
	}
}

if ($uid) {
	$isSpace = true;
	$USCR = 'space_article';
	require_once(R_P.'require/credit.php');
	require_once PrintEot('m_space_article');
	pwOutPut();
} else {
	include_once(D_P . 'data/bbscache/o_config.php');
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
	return $imgpath.'/goods_none.gif';
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
?>
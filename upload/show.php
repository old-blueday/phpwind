<?php
define('SCR','show');
require_once('global.php');
require_once(R_P.'require/header.php');
require_once(R_P.'require/forum.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forumcache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

!$_G['show'] && Showmsg('groupright_show');
$db_showperpage = 16;
S::gp(array('pwuser','uid','action','type','page','aid'));
$fidoff= array();

$query = $db->query("SELECT fid,allowvisit,password,f_type,forumsell FROM pw_forums WHERE type<>'category'");
while ($rt = $db->fetch_array($query)) {
	if ($rt['f_type'] == 'hidden' || $rt['password'] || $rt['forumsell'] || ($rt['allowvisit'] && strpos($rt['allowvisit'],",$groupid,") === false)) {
		$fidoff[] = $rt['fid'];
	}
}

$sqladd = "1";
if ($pwuser || is_numeric($uid)) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if ($pwuser) {
		$userInfo = $userService->getByUserName($pwuser);
	} elseif (is_numeric($uid)) {
		$userInfo = $userService->get($uid);
	}

	if (!$userInfo) {
		$errorname = $pwuser;
		Showmsg('user_not_exists');
	} else {
		$uid     = $userInfo['uid'];
		$owner   = $userInfo['username'];
		$sqladd .= " AND a.uid=".S::sqlEscape($uid);
	}
}

if (is_numeric($fid) && $fid > 0) {
	if (in_array($fid,$fidoff)) {
		Showmsg('forum_not_allow');
	}
	$sqladd .= " AND a.fid=".S::sqlEscape($fid);
	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
}
$type_1 = $type_2 = '';
if ($type == 1) {
	$sqladd .= " AND a.type='img'";
	$type_1  = "selected";
} elseif ($type == 2) {
	$sqladd .= " AND a.type!='img'";
	$type_2  = "selected";
}
if (empty($action)) {

	$url = "show.php?uid=$uid&fid=$fid&type=$type&";
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_showperpage,$db_showperpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_attachs a WHERE $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_showperpage),$url);

	$pids  = $showdb= $ttable_a = $ptable_a = $read = $repost = array();
	$query = $db->query("SELECT a.aid,a.uid,a.attachurl,a.type,a.fid,a.tid,a.pid,a.name,a.needrvrc,a.descrip,a.ifthumb FROM pw_attachs a WHERE $sqladd ORDER BY aid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$showdb[] = $rt;
		if ($rt['pid']) {
			$pids[] = $rt['pid'];
		}
		$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
	}
	foreach ($ttable_a as $pw_tmsgs => $value){
		$value = S::sqlImplode($value);
		if ($value) {
			$query = $db->query("SELECT t.tid,t.fid,t.authorid,t.author as username,t.subject,t.ifcheck,t.ifshield,t.ptable,tm.content,tm.buy FROM pw_threads t LEFT JOIN $pw_tmsgs tm USING(tid) WHERE t.tid IN($value)");
			while ($rt = $db->fetch_array($query)) {
				$read[$rt['tid']] = $rt;
				$ptable_a[$rt['ptable']] = 1;
			}
		}
	}
	if ($pids) {
		$pids = S::sqlImplode($pids);
		foreach ($ptable_a as $ptable => $value) {
			$pw_posts = GetPtable($ptable);
			$query = $db->query("SELECT pid,tid,fid,authorid,author as username,subject,ifcheck,ifshield,content,buy FROM $pw_posts WHERE pid IN($pids)");
			while ($rt = $db->fetch_array($query)) {
				$repost[$rt['pid']] = $rt;
			}
		}
	}

	foreach ($showdb as $key => $rt) {
		$flag = false;
		if ($read[$rt['tid']]['fid']) {
			$flag = true;
			if ($rt['pid'] && $repost[$rt['pid']]) {
				 $rt = array_merge($rt,$repost[$rt['pid']]);
			} else {
				 $rt = array_merge($rt,$read[$rt['tid']]);
			}
			if (empty($rt['fid']) || empty($rt['tid']) || in_array($rt['fid'],$fidoff) || $rt['ifshield']=='2') {
				$flag = false;
			} elseif ($groupid!='3' && $groupid!='4') {
				if (!$rt['ifcheck'] || $rt['ifshield']) {
					$flag = false;
				} elseif ($rt['authorid'] == $winduid) {
					$flag = true;
				} elseif ((int)$rt['needrvrc'] > (int)$userrvrc) {
					$flag = false;
				} elseif (strpos($rt['content'],"[post]") !== false && strpos($rt['content'],"[/post]") !== false) {
					$flag = false;
				} elseif (strpos($rt['content'],"[hide") !== false && strpos($rt['content'],"[/hide]") !== false) {
					preg_match("/\[hide=(.+?)\].+?\[\/hide\]/eis",$rt['content'],$rtu);
					if ($userrvrc < $rtu[1]) {
						$flag = false;
					}
				} elseif (strpos($rt['content'],"[sell") !== false && strpos($rt['content'],"[/sell]") !== false) {
					if (strpos(','.$rt['buy'],','.$windid) === false) {
						$flag = false;
					}
				}
			}
		}

		if ($flag == false){
			$rt['a_url'] = 'none';
		} else {
			$a_url = geturl($rt['attachurl'],'show');
			$rt['a_url'] = is_array($a_url) ? $a_url[0] : $a_url;
			$rt['ifthumb']==1 && $rt['a_url'] = str_replace($rt['attachurl'],'thumb/'.$rt['attachurl'],$rt['a_url']);
			!$rt['descrip'] && $rt['descrip'] = substrs($rt['subject'],20);
		}
		!$rt['pid'] && $rt['pid'] = 'tpc';
		$rt['fname'] = $forum[$rt['fid']]['name'];
		$showdb[$key] = $rt;
	}
	require_once PrintEot('show');footer();

} else {

	$pw_attachs = L::loadDB('attachs', 'forum');
	$rt = $pw_attachs->get($aid);
	if ($rt && $rt['tid'] && $rt['fid']) {
		$pw_tmsgs = GetTtable($rt['tid']);
		$rtinfo = $db->get_one("SELECT t.fid,t.subject,t.ifcheck,t.ifshield,tm.content,m.username
			FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid
			LEFT JOIN pw_members m ON m.uid=t.authorid
			WHERE t.tid=".S::sqlEscape($rt['tid'],false));
		if (in_array($rtinfo['fid'],$fidoff) || $rtinfo['ifshield']=='2' || $groupid!='3' && $groupid!='4' && ($rtinfo['needrvrc']>$userrvrc || !$rtinfo['ifcheck'] || $rtinfo['ifshield'] || (strpos($rtinfo['content'],"[post]") !== false && strpos($rtinfo['content'],"[/post]") !== false) || (strpos($rtinfo['content'],"[hide") !== false && strpos($rtinfo['content'],"[/hide]") !== false) || (strpos($rtinfo['content'],"[sell") !== false && strpos($rtinfo['content'],"[/sell]") !== false))) {
			Showmsg('pic_not_exists');
		}
		$rt['subject'] = $rtinfo['subject'];
		$rt['username'] = $rtinfo['username'];
		$a_url = geturl($rt['attachurl'],'show');
		$rt['a_url'] = is_array($a_url) ? $a_url[0] : $a_url;
		$uid  = $rt['uid'];
		$type = 1;
		$owner= $rt['username'];
		!$rt['pid'] && $rt['pid']='tpc';
		!$rt['descrip'] && $rt['descrip'] = substrs(stripWindCode($rtinfo['content']),120);
	} else {
		Showmsg('pic_not_exists');
	}
	require_once PrintEot('show');footer();
}
?>
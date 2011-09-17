<?php
!defined('W_P') && exit('Forbidden');
InitGP ( array ('page', 'fid', 'type', 'wind_action', 'wind_pwd' ) );
$fid = ( int ) $fid;
$page = ( int ) $page;
$page < 1 && $page = 1;
$page > 500 && $page = 500;
$thisp = 0;
$forumName = $db_bbsname;

if (empty ( $fid ) && empty ( $type )) {
	//我的书签
	$wapfids = pwGetShortcut ();
	if (empty ( $wapfids ) && $db_wapfids) {
		$tmpwapfids = explode ( ',', $db_wapfids );
		foreach ( $tmpwapfids as $val ) {
			$wapfids [$val] = wap_cv ( strip_tags ( $forum [$val] ['name'] ) );
		}
	}
	wap_header ();
	require_once PrintWAP ( 'forum' );
	wap_footer ();
}
if ($fid) {
	$foruminfo = L::forum ( $fid );
	! $foruminfo && wap_msg ( 'data_error' );
	$foruminfo ['type'] == 'category' && wap_msg ( 'forum_category', 'index.php?a=list&fid=' . $fid );
	$rt = $db->get_one ( "SELECT fd.topic,fd.top1,fd.top2,fd.lastpost,fd.aid,fd.aids,fd.aidcache,fd.tpost,fd.topic,fd.article ,a.ifconvert,a.author,a.startdate,a.enddate,a.subject,a.content,fd.topthreads FROM pw_forumdata fd LEFT JOIN pw_announce a ON fd.aid=a.aid WHERE fd.fid=" . pwEscape ( $fid ) );
	$rt && $foruminfo += $rt; #版块信息合并
	$forumset = $foruminfo ['forumset'];
	! $forumset ['commend'] && $foruminfo ['commend'] = array ();
	if ($forumset ['link']) {
		$flink = str_replace ( "&amp;", "&", $forumset ['link'] );
		wap_msg ( 'forum_link' );
	}
	forumcheck ( $fid, 'list' );
	$forumName = wap_cv ( strip_tags ( $forum [$fid] ['name'] ) );
}

//版块浏览及管理权限
$pwSystem = array ();
$isGM = $isBM = $admincheck = $ajaxcheck = $managecheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
if ($groupid != 'guest') {
	$isGM = CkInArray ( $windid, $manager );
	$isBM = admincheck ( $foruminfo ['forumadmin'], $foruminfo ['fupadmin'], $windid );
	$admincheck = ($isGM || $isBM) ? 1 : 0;
	if (! $isGM) {
		$pwSystem = pwRights ( $isBM );
		if ($pwSystem && ($pwSystem ['tpccheck'] || $pwSystem ['digestadmin'] || $pwSystem ['lockadmin'] || $pwSystem ['pushadmin'] || $pwSystem ['coloradmin'] || $pwSystem ['downadmin'] || $pwSystem ['delatc'] || $pwSystem ['moveatc'] || $pwSystem ['copyatc'] || $pwSystem ['topped'] || $pwSystem ['unite'] || $pwSystem ['tpctype'])) { //system rights
			$managecheck = 1;
		}
		if (($groupid == 3 || $isBM) && $pwSystem ['deltpcs']) {
			$ajaxcheck = 1;
		}
		$pwPostHide = $pwSystem ['posthide'];
		$pwSellHide = $pwSystem ['sellhide'];
		$pwEncodeHide = $pwSystem ['encodehide'];
		$pwAnonyHide = $pwSystem ['anonyhide'];
	} else {
		$managecheck = $ajaxcheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
	}
}
if (! $admincheck) {
	! $foruminfo ['allowvisit'] && forum_creditcheck (); #积分限制浏览
	$foruminfo ['forumsell'] && forum_sell ( $fid ); #出售版块
}

$per = 5;
$start = ($page - 1) * $per;
$tids = array ();

/* 置顶贴处理 */
$topTids = $foruminfo ['topthreads'];
$topTids = explode(',',$topTids);
if ($topTids && $start < count ( $topTids )) {
	$L = ( int ) min ( count ( $topTids ) - $start, $per );
	$limit = pwLimit ( $start, $L );
	$query = $db->query ( "SELECT * FROM pw_threads WHERE  fid!=0 AND tid IN(". pwImplode($topTids) .") ORDER BY specialsort DESC,lastpost DESC $limit" );
	while ( $rt = $db->fetch_array ( $query ) ) {
		$id ++;
		if ($rt ['anonymous'] && $rt ['authorid'] != $winduid && ! $pwAnonyHide) {
			$rt ['author'] = $db_anonymousname;
			$rt ['authorid'] = 0;
		}
		$rt ['postdate'] = get_date ( $rt ['postdate'] );
		$rt ['id'] = $id;
		$rt ['subject'] = wap_cv ( str_replace ( '&nbsp;', '', $rt ['subject'] ) );
		$tids [] = $rt;
	}
	$db->free_result ( $query );
}
InitGP ( array ('t'), 'GP' );
/* 查询帖子缓存 */
$_filedir = D_P . "data/wapcache/";
if (! is_dir ( $_filedir )) {
	wap_createFolder ( $_filedir );
}
if (empty ( $fid )) {
	$_filename = $_filedir . "wap_all_cache.php";
} else {
	$_filename = Pcv($_filedir . "wap_" . $fid . "_cache.php");
}
empty ( $type ) && $type = 'all';
if (count($tids) != 0 && count ( $tids ) <= $per) {
	$tidsCatche = getTidsCache ( $type, 0, ($per - count ( $tids )) );
} else {
	$tidsCatche = getTidsCache ( $type, $start, $per );
}
if (! empty ( $tidsCatche )) {
	$orderby = '';
	if ($type == 'digest') {
		$orderby = "ORDER BY specialsort DESC,lastpost DESC";
	} elseif ($type == 'hot') {
		$orderby = "ORDER BY replies DESC";
	} elseif ($type == 'new') {
		$orderby = "ORDER BY postdate DESC";
	} else {
		$orderby = "ORDER BY specialsort DESC,lastpost DESC, postdate DESC";
	}
	$result = $db->query ( "SELECT * FROM pw_threads WHERE fid != 0 and tid IN (" . pwImplode ( $tidsCatche ) . ") $orderby" );
	while ( $rt = $db->fetch_array ( $result ) ) {
		$id ++;
		if ($rt ['anonymous'] && $rt ['authorid'] != $winduid && ! $pwAnonyHide) {
			$rt ['author'] = $db_anonymousname;
			$rt ['authorid'] = 0;
		}
		$rt ['postdate'] = get_date ( $rt ['postdate'] );
		$rt ['id'] = $id;
		$rt ['subject'] = wap_cv ( str_replace ( '&nbsp;', '', $rt ['subject'] ) );
		$tids [] = $rt;
	}
}
if (!$tids && $page>1) header("Location: index.php?a=forum" . ($fid ? "&fid=$fid" : "") . ($type ? "&type=$type" : ""));

$nextp = $page + 1;
$prevp = $page - 1;
if (count ( $tids ) < $per)
	$nextp = 1;
$prevurl = "index.php?a=forum&" . ($fid ? "&amp;fid=$fid" : "") . ($prevp ? "&amp;page=$prevp" : "") . ($type ? "&amp;type=$type" : "");
$nexturl = "index.php?a=forum&" . ($fid ? "&amp;fid=$fid" : "") . ($nextp ? "&amp;page=$nextp" : "") . ($type ? "&amp;type=$type" : "");
if($t){
	$prevurl .='&amp;t='.$t;
	$nexturl .='&amp;t='.$t;
}
Cookie("wap_scr", serialize(array("page"=>"forum","extra"=>array("fid"=>$fid))));
wap_header ();
require_once PrintWAP ( 'forum' );
wap_footer ();

function forum_creditcheck() {
	global $db, $winddb, $userrvrc, $forumset, $groupid;
	
	$forumset ['rvrcneed'] = ( int ) $forumset ['rvrcneed'];
	$forumset ['moneyneed'] = ( int ) $forumset ['moneyneed'];
	$forumset ['creditneed'] = ( int ) $forumset ['creditneed'];
	$forumset ['postnumneed'] = ( int ) $forumset ['postnumneed'];
	$check = 1;
	if ($forumset ['rvrcneed'] && $userrvrc < $forumset ['rvrcneed']) {
		$check = 0;
	} elseif ($forumset ['moneyneed'] && $winddb ['money'] < $forumset ['moneyneed']) {
		$check = 0;
	} elseif ($forumset ['creditneed'] && $winddb ['credit'] < $forumset ['creditneed']) {
		$check = 0;
	} elseif ($forumset ['postnumneed'] && $winddb ['postnum'] < $forumset ['postnumneed']) {
		$check = 0;
	}
	if (! $check) {
		if ($groupid == 'guest') {
			wap_msg ( 'forum_guestlimit', 'index.php?a=list' );
		} else {
			wap_msg ( 'forum_creditlimit', 'index.php?a=list' );
		}
	}
}

function setTidsCache($type) {
	global $db, $fid, $timestamp, $_filename, $t;
	$useIndex = "";
	$orderby = '';
	$where = "WHERE " . ($fid ? " fid= ".pwEscape($fid) : "fid IN (" . getFidsForWap () . ")") . " AND specialsort=0 AND ifcheck=1";
	if ($type == 'digest') {
		$where .= " AND digest>0";
		$orderby = "ORDER BY topped DESC,lastpost DESC";
	} elseif ($type == 'hot') {
		
		$time = ( int ) 3600 * 24 * 30;
		if ($t == '1') {
			$time = ( int ) 3600 * 24;
		} elseif ($t == '2') {
			$time = ( int ) 3600 * 24 * 3;
		} elseif ($t == '3') {
			$time = ( int ) 3600 * 24 * 7;
		} elseif ($t == '4') {
			$time = ( int ) 3600 * 24 * 30;
		}
		$where .= " AND postdate>" . (int)($timestamp - $time);
		$orderby = "ORDER BY replies DESC";
	} elseif ($type == 'new') {
		$useIndex =  'USE INDEX ('.getForceIndex('idx_postdate').')';
		$orderby = "ORDER BY postdate DESC";
	} else {
		$orderby = "ORDER BY specialsort DESC,lastpost DESC";
	}
	$limit = "LIMIT 0,500";
	$query = $db->query ( "SELECT tid FROM pw_threads $useIndex $where $orderby $limit" );
	$result = array ();
	$result ['uptime'] = $timestamp;
	while ( $rt = $db->fetch_array ( $query ) ) {
		$result ['tids'] .= $rt ['tid'] . ',';
	}
	if (is_file( $_filename )) {
		include $_filename;
	}
	$tidsCache [$type] = $result;
	writeover ( $_filename, "<?php\r\n\$tidsCache=" . pw_var_export ( $tidsCache ) . "\r\n?>" );
	return $result ['tids'];
}

function getTidsCache($type, $start, $per) {
	global $timestamp, $_filename;
	if (is_file( $_filename ) && $type != 'hot') {
		include Pcv($_filename);
		$tids = $tidsCache [$type] ['tids'];
		$uptime = $tidsCache [$type] ['uptime'];
	}
	$overtime = $timestamp - 60 * 2; //3分钟更新一次
	if (! $tids || ! $uptime || $uptime <= $overtime) {
		$tids = setTidsCache ( $type );
	}
	$tids = explode ( ',', trim ( $tids, ',' ) );
	$tids = array_splice ( $tids, $start, $per );
	return $tids;
}

function forum_sell($fid) {
	global $db, $winduid, $timestamp;
	$rt = $db->get_one ( "SELECT MAX(overdate) AS u FROM pw_forumsell WHERE uid=" . pwEscape ( $winduid ) . ' AND fid=' . pwEscape ( $fid ) );
	if ($rt ['u'] < $timestamp) {
		wap_msg ( '本版块为出售版块', 'index.php?a=list' );
	}
}
?>

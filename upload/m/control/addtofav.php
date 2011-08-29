<?php
!defined('W_P') && exit('Forbidden');
! $windid && wap_msg ( 'not_login' );
InitGP ( array ('action', 'tid' ) );

$rts = array();
$result = $db->query("SELECT typeid FROM pw_collection WHERE type = 'postfavor' AND typeid = " . S::sqlEscape($tid) . " AND uid = " . S::sqlEscape($winduid));
while ($rt = $db->fetch_array($result)) {
	$rts[] = $rt['typeid'];
}
$rs['tids'] = implode(',',$rts);
$rs['type'] = '';
if ($rs['tids']) {
	$count = 0;
	$tiddb = getFavor ( $rs ['tids'] );
	foreach ( $tiddb as $key => $t ) {
		if (is_array ( $t )) {
			if (CkInArray ( $tid, $t )) {
				favShowMsg ( 'job_favor_error' );
			}
			$count += count ( $t );
		} else {
			unset ( $tiddb [$key] );
		}
	}
	$count > $_G ['maxfavor'] && favShowMsg ( 'job_favor_full' );
	
	InitGP ( array ('type' ), 2 );
	$typeid = array ('0' => 'default' );
	if ($rs ['type']) {
		$typeid = array_merge ( $typeid, explode ( ',', $rs ['type'] ) );
		if (! isset ( $type )) {
			echo 'type' . $type;
		}
	} else {
		$type = 0;
	}
	! isset ( $typeid [$type] ) && favShowMsg ( 'data_error' );
	$read = $db->get_one ( 'SELECT subject FROM pw_threads WHERE tid=' . pwEscape ( $tid ) );
	! $read && favShowMsg ( 'data_error' );
	require_once (R_P . 'require/posthost.php');
	PostHost ( "http://push.phpwind.net/push.php?type=collect&url=" . rawurlencode ( "$db_bbsurl/index.php?a=read&tid=$tid" ) . "&tocharset=$db_charset&title=" . rawurlencode ( $read ['subject'] ) . "&bbsname=" . rawurlencode ( $db_bbsname ), "" );
	$tiddb [$type] [] = $tid;
	$newtids = makefavor ( $tiddb );
	$db->update("UPDATE pw_collection SET typeid=" . S::sqlEscape($newtids) . " WHERE type = 'postfavor' AND typeid = " . S::sqlEscape($tid) . " AND uid = " . S::sqlEscape($winddb['uid']));
} else {
	$_cacheService = Perf::gatherCache('pw_threads');
	$favor = $_cacheService->getThreadByThreadId($tid);
	empty($favor) && favShowMsg('data_error');
	$collection['uid'] = $favor['authorid'];
	$collection['username']	= $favor['author'];
	$collection['lastpost'] = $favor['lastpost'];
	$collection['link'] = $db_bbsurl.'/index.php?a=read&tid='.$tid;
	$collection['postfavor']['subject'] = $favor['subject'];
	$collectionDate = array(
					'typeid'	=> 	$tid,
					'type'		=> 	'postfavor',
					'uid'		=>	$winduid,
					'username'	=> $windid,
					'content'	=>	serialize($collection),
					'postdate'	=>	$timestamp
				);
	$collectionService = L::loadClass('Collection', 'collection');
	$collectionService->insert($collectionDate);
}

$db->update ( "UPDATE pw_threads SET favors=favors+1 WHERE tid=" . pwEscape ( $tid ) );

// Start Here pwcache
require_once (R_P . 'lib/elementupdate.class.php');
$elementupdate = new ElementUpdate ( );
$elementupdate->newfavorUpdate ( $tid, $fid );
if ($db_ifpwcache & 1024) {
	$elementupdate->hotfavorUpdate ( $tid, $fid );
}
// End Here
favShowMsg ( 'job_favor_success' );

function favShowMsg($s) {
	global $tid;
	wap_msg ( $s, 'index.php?a=read&tid=' . $tid );
}

function getFavor($tids) {
	$tids = explode ( '|', $tids );
	$tiddb = array ();
	foreach ( $tids as $key => $t ) {
		if ($t) {
			$v = explode ( ',', $t );
			foreach ( $v as $k => $v1 ) {
				$tiddb [$key] [$v1] = $v1;
			}
		}
	}
	return $tiddb;
}
function makefavor($tiddb) {
	$newtids = $ex = '';
	$k = 0;
	ksort ( $tiddb );
	foreach ( $tiddb as $key => $val ) {
		$new_tids = '';
		rsort ( $val );
		if ($key != $k) {
			$s = $key - $k;
			for($i = 0; $i < $s; $i ++) {
				$newtids .= '|';
			}
		}
		foreach ( $val as $k => $v ) {
			is_numeric ( $v ) && $new_tids .= $new_tids ? ',' . $v : $v;
		}
		$newtids .= $ex . $new_tids;
		$k = $key + 1;
		$ex = '|';
	}
	return $newtids;
}

function getNum($fid) {
	if ($forum [$fid] ['type'] = 'category') {
		$fidnum = 1;
	} elseif ($forum [$fid] ['type'] = 'forum') {
		$fidnum = 2;
	} elseif ($forum [$fid] ['type'] = 'sub') {
		$fup = $forum [$fid] ['fup'];
		if ($forum [$fup] ['type'] = 'forum') {
			$fidnum = 3;
		} else {
			$fidnum = 4;
		}
	}
	return $fidnum;
}
?>

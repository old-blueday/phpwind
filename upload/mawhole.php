<?php
if (isset($_GET['ajax'])) {
	define('AJAX', '1');
}
require_once ('global.php');
require_once (R_P . 'require/functions.php');
$groupid == 'guest' && Showmsg('not_login');
S::gp(array('action', 'tidarray', 'seltid', 'viewbbs', 'overprint','type'));
$viewbbs = $viewbbs ? "&viewbbs=$viewbbs" : "";

$singleAction = false;
if ((empty($tidarray) || !is_array($tidarray)) && is_numeric($seltid)) {
	$tidarray = array($seltid);
	$singleAction = true;
}
/*
!is_numeric($seltid) && (0 == count($tidarray)) && Showmsg('id_error');
if (!$tidarray) {
	$tidarray = is_numeric($seltid) ? array($seltid) : explode(',',$seltid);
}
*/
($action == "delall") && deleteThreadsHander($tidarray); //搜索删除操作

if (!in_array($action, array('type', 'check', 'del', 'move', 'copy', 'headtopic', 'digest', 'lock', 'pushtopic',
	'downtopic', 'edit', 'unite', 'push', 'overprint','batch')) || empty($fid) || empty($tidarray)) {
	Showmsg('undefined_action');
}

L::loadClass('forum', 'forum', false);
require_once (R_P . 'require/updateforum.php');
require_once (R_P . 'require/writelog.php');
include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');

L::loadClass('forum', 'forum', false);
$pwforum = new PwForum($fid);

if (!$pwforum->isForum()) {
	Showmsg('data_error');
}
$pwforum->forumcheck($winddb, $groupid);
$foruminfo =& $pwforum->foruminfo;
$forumset =& $pwforum->forumset;

$isGM = S::inArray($windid, $manager);
$isBM = $pwforum->isBM($windid);
if (!$isGM) {
	switch ($action) {
		case 'type' :
			$admincheck = pwRights($isBM, 'tpctype');
			break;
		case 'del' :
			$admincheck = pwRights($isBM, 'delatc');
			break;
		case 'check' :
			$admincheck = pwRights($isBM, 'tpccheck');
			break;
		case 'move' :
			$admincheck = pwRights($isBM, 'moveatc');
			break;
		case 'copy' :
			$admincheck = pwRights($isBM, 'copyatc');
			break;
		case 'headtopic' :
			$admincheck = pwRights($isBM, 'topped');
			break;
		case 'unite' :
			$admincheck = pwRights($isBM, 'unite');
			break;
		case 'push' :
			$admincheck = $isBM || $groupid == '3' || $groupid == '4';
			break;
		case 'digest' :
			$admincheck = pwRights($isBM, 'digestadmin');
			break;
		case 'lock' :
			$admincheck = pwRights($isBM, 'lockadmin');
			break;
		case 'pushtopic' :
			$admincheck = pwRights($isBM, 'pushadmin');
			break;
		case 'edit' :
			$admincheck = pwRights($isBM, 'coloradmin');
			break;
		case 'downtopic' :
			$admincheck = pwRights($isBM, 'downadmin');
			break;
		case 'overprint' :
			$admincheck = pwRights($isBM, 'overprint');
			break;
		case 'batch' :
			$admincheck = true;
			break;
		default :
			$admincheck = false;
	}
	!$admincheck && Showmsg('mawhole_right');
}

$tids = $threaddb = array();
$mgdate = get_date($timestamp, 'Y-m-d');
$template = 'ajax_mawhole';

if (empty($_POST['step']) && !in_array($action,array('batch'))) {
	$reason_sel = '';
	$reason_a = explode("\n", $db_adminreason);
	foreach ($reason_a as $k => $v) {
		if ($v = trim($v)) {
			$reason_sel .= "<option value=\"$v\">$v</option>";
		} else {
			$reason_sel .= "<option value=\"\">-------</option>";
		}
	}
	foreach ($tidarray as $k => $v) {
		is_numeric($v) && $tids[] = $v;
	}
	if ($tids) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$tids = S::sqlImplode($tids);
		$query = $db->query("SELECT * FROM pw_threads WHERE tid IN($tids)");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['fid'] != $fid && $groupid == 5) {
				Showmsg('admin_forum_right');
			}
			if ($winduid != $rt['authorid']) {
				$authordb = $userService->get($rt['authorid']);
				$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
				if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
					Showmsg('modify_admin');
				}
			}
			$rt['date'] = get_date($rt['postdate']);
			$threaddb[] = $rt;
		}
	}
	empty($threaddb) && Showmsg('data_error');

	if (!defined('AJAX')) {
		require_once (R_P . 'require/header.php');
		$template = 'mawhole';
	}
} else {

	S::gp(array('atc_content'), 'P');
	if ($SYSTEM['enterreason'] && !$atc_content) {
		!in_array($action,array('batch')) && Showmsg('enterreason');
	}
}

Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
if ($action == 'type') {

	include_once pwCache::getPath(D_P . 'data/bbscache/cache_post.php');
	include_once pwCache::getPath(D_P . 'data/bbscache/forum_typecache.php');
	$t_db = (array) $topic_type_cache[$fid];

	if (empty($_POST['step'])) {

		$typesel = '';
		if ($t_db) {
			foreach ($t_db as $value) {
				$value['name'] = substrs(strip_tags($value['name']),12);
				if ($value['upid'] == 0) {
					$t_typedb[$value['id']] = $value;
				} else {
					$t_subtypedb[$value['upid']][$value['id']] = $value['name'];
				}
				$t_exits = 1;
			}
		} else {
			Showmsg('mawhole_notype');
		}

		if ($t_subtypedb) {
			$t_subtypedb = pwJsonEncode($t_subtypedb);
			$t_sub_exits = 1;
		}

		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('type', 'subtype', 'ifmsg'));
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $key => $value) {
				is_numeric($value) && $tids[] = $value;
			}
		}
		$type = $subtype ? $subtype : $type;
		!$tids && Showmsg('mawhole_nodata');
		//$db->update('UPDATE pw_threads SET type=' . S::sqlEscape($type) . " WHERE tid IN(" . S::sqlImplode($tids) . ") AND fid=" . S::sqlEscape($fid));
		pwQuery::update('pw_threads', 'tid IN (:tid) AND fid=:fid', array($tids, $fid), array('type'=>$type));

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tids);

		if ($ifmsg) {
			$query = $db->query("SELECT tid,fid,author,authorid,subject,postdate FROM pw_threads WHERE tid IN(" . S::sqlImplode($tids) . ")");
			while (@extract($db->fetch_array($query))) {
				M::sendNotice(array($author), array('title' => getLangInfo('writemsg', 'change_type_title'),
					'content' => getLangInfo('writemsg', 'change_type_content', array('manager' => $windid,
						'fid' => $fid, 'tid' => $tid, 'postdate' => get_date($postdate), 'subject' => $subject,
						'forum' => strip_tags($forum[$fid]['name']), 'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content), 'type' => $t_db[$type]['name']))));
			}
		}
		if (!defined('AJAX')) {
			refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
		} else {
			Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
		}
	}
} elseif ($action == 'check') {

	if (empty($_POST['step'])) {

		require_once PrintEot('mawhole');
		footer();

	} else {

		PostCheck();
		S::gp(array('ifmsg'));
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = array();
		$count = 0;
		if (is_array($tidarray)) {
			foreach ($tidarray as $key => $value) {
				if (is_numeric($value)) {
					$tids[] = $value;
					$count++;
				}
			}
		}
		!$tids && Showmsg('mawhole_nodata');
		//$db->update("UPDATE pw_threads SET ifcheck='1' WHERE tid IN(" . S::sqlImplode($tids) . ") AND fid=" . S::sqlEscape($fid));
		pwQuery::update('pw_threads', 'tid IN (:tid) AND fid=:fid', array($tids, $fid), array('ifcheck'=>1));
		// $threadList = L::loadClass("threadlist", 'forum');
		// $threadList->refreshThreadIdsByForumId($fid);
		// $threads = L::loadClass('Threads', 'forum');
		// $threads->delThreads($tids);
		//* Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		$rt = $db->get_one("SELECT tid,author,postdate,subject,lastpost,lastposter FROM pw_threads WHERE fid='$fid' AND ifcheck='1' AND topped='0' AND lastpost>0 ORDER BY lastpost DESC LIMIT 0,1");
		if ($rt['postdate'] == $rt['lastpost']) {
			$subject = substrs($rt['subject'], 21);
			$author = $rt['author'];
		} else {
			$subject = 'Re:' . substrs($rt['subject'], 21);
			$author = $rt['lastposter'];
		}
		$new_url = "read.php?tid=$rt[tid]&page=e#a";
		$lastpost = $subject . "\t" . $author . "\t" . $rt['lastpost'] . "\t" . $new_url;
		$db->update('UPDATE pw_forumdata ' . ' SET lastpost=' . S::sqlEscape($lastpost, false) . ',tpost=tpost+' . $count . ',article=article+' . $count . ',topic=topic+' . $count . ' WHERE fid=' . S::sqlEscape($fid));
		//* P_unlink(D_P . 'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P . 'data/bbscache/c_cache.php');

		if ($ifmsg) {
			$query = $db->query("SELECT tid,fid,author,authorid,subject,postdate FROM pw_threads WHERE tid IN(" . S::sqlImplode($tids) . ")");
			while (@extract($db->fetch_array($query))) {
				M::sendNotice(array($author), array('title' => getLangInfo('writemsg', 'check_title'),
					'content' => getLangInfo('writemsg', 'check_content', array('manager' => $windid, 'fid' => $fid,
						'tid' => $tid, 'postdate' => get_date($postdate), 'subject' => $subject,
						'forum' => strip_tags($forum[$fid]['name']), 'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content)))));
			}
		}
		refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
	}
} elseif ($action == 'del') {

	if (empty($_POST['step'])) {
		S::gp(array('jumptype'));
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('ifdel', 'ifmsg', 'jumptype'));/* ifdel 是否删除积分 ,ifmsg 是否发送消息 */
		count($tidarray) > 500 && Showmsg('mawhole_count');

		$delids = $msgdb = array();
		foreach ($tidarray as $key => $value) {
			if (is_numeric($value)) {
				$delids[] = $value;
			}
		}
		!$delids && Showmsg('mawhole_nodata');

		require_once (R_P . 'require/credit.php');
		$creditset = $credit->creditset($foruminfo['creditset'], $db_creditset);
		$msg_delrvrc = $ifdel ? abs($creditset['Delete']['rvrc']) : 0;
		$msg_delmoney = $ifdel ? abs($creditset['Delete']['money']) : 0;

		$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
		$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($delids));

		foreach ($readdb as $key => $read) {
			$read['fid'] != $fid && Showmsg('admin_forum_right');
			if ($ifmsg) {
				$msg_delrvrc && $msg_delrvrc="-{$msg_delrvrc}";
				$msg_delmoney && $msg_delmoney="-{$msg_delmoney}";
				$msgdb[] = array(
					'toUser' => $read['author'],
					'title' => getLangInfo('writemsg', 'del_title'),
					'content' => getLangInfo('writemsg', 'del_content', array(
						'manager' => $windid,
						'fid' => $read['fid'],
						'tid' => $read['tid'],
						'subject' => $read['subject'],
						'postdate' => get_date($read['postdate']),
						'forum' => strip_tags($forum[$fid]['name']),
						'affect' => "{$db_rvrcname}:{$msg_delrvrc},{$db_moneyname}:{$msg_delmoney}",
						'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content)
					))
				);
			}
		}
		$delarticle->delTopic($readdb, $db_recycle, $ifdel, array('reason' => $atc_content));

		sendMawholeMessages($msgdb);
		
		if (!defined('AJAX')) {
			$url = ($jumptype == 'forumcp') ? "forumcp.php?action=edit&type=thread&fid=$fid{$viewbbs}" : "thread.php?fid=$fid{$viewbbs}";
			refreshto($url, 'operate_success');
		} else {
			Showmsg('ajax_mawhole_operate_success');
		}
	}
} elseif ($action == 'move') {

	if (empty($_POST['step'])) {

		include_once pwCache::getPath(D_P . 'data/bbscache/cache_post.php');
		include_once pwCache::getPath(D_P . 'data/bbscache/forum_typecache.php');
		//zhuli
		$re = $db->query("SELECT fid,t_type FROM pw_forums ");
		$forumArr = array();
		while($row = $db->fetch_array($re)) {
			$forumArr[$row['fid']] = $row;
		}
		
		if ($topic_type_cache) {
			foreach ($topic_type_cache as $key => $value) {
				foreach ($value as $k => $v) {
					$v['name'] = strip_tags($v['name']);
					if ($v['upid'] == 0) {
						$t_typedb[$key][$k] = $v['name'];
						$t_typedb[$key][0]  = $forumArr[$key]['t_type']; //zhuli
					} else {
						$t_subtypedb[$key][$v['upid']][$k] = $v['name'];
					}
				}
			}
		}
		
		if ($forum) {
			$forum_temp = array();
			foreach($forum as $val){
				$forum_temp[$val['fid']] = $val['fup'];
			}
			$forum_temp = pwJsonEncode($forum_temp);
		}
	
		if ($t_typedb) {
			$t_typedb = pwJsonEncode($t_typedb);
		}
		if ($t_subtypedb) {
			$t_subtypedb = pwJsonEncode($t_subtypedb);
		}
		$forumadd = '';
		$query = $db->query("SELECT fid,t_type,name,allowvisit,f_type FROM pw_forums");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['f_type'] == 'hidden' && strpos($rt['allowvisit'], ',' . $groupid . ',') !== false) {
				$forumadd .= "<option value='$rt[fid]'> &nbsp;|- $rt[name]</option>";
			}
		}
		@include_once pwCache::getPath(D_P . 'data/bbscache/forumcache.php');
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('to_id', 'ifmsg', 'to_threadcate', 'to_subtype'));

		if ($forum[$to_id]['type'] == 'category') {
			Showmsg('mawhole_error');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$mids = $ttable_a = $ptable_a = $msgdb = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $key => $value) {
				if (is_numeric($value)) {
					$mids[] = $value;
					$ttable_a[GetTtable($value)][] = $value;
				}
			}
		}
		!$mids && Showmsg('mawhole_nodata');

		$pw_attachs = L::loadDB('attachs', 'forum');
		$pw_attachs->updateByTid($mids, array('fid' => $to_id));

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($mids);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$mids));

		//$mids = S::sqlImplode($mids);
		$updatetop = $todaypost = $topic_all = $replies_all = 0;

		$cy_tids = array();
		$query = $db->query("SELECT tid,fid as tfid,author,postdate,subject,replies,topped,ptable,ifcheck,tpcstatus,modelid,special FROM pw_threads WHERE tid IN(" . S::sqlImplode($mids) . ")");
		while ($rt = $db->fetch_array($query)) {
			S::slashes($rt);
			@extract($rt);
			$tfid != $fid && Showmsg('admin_forum_right');
			$ptable_a[$ptable] = 1;
			$postdate > $tdtime && $todaypost += ($replies + 1);
			$ifcheck && $topic_all++;
			$replies_all += $replies;
			if ($rt['tpcstatus'] && getstatus($rt['tpcstatus'], 1)) {
				$cy_tids[$rt['tid']] = $rt['tid'];
			}
			// 静态模版更新
			if ($foruminfo['allowhtm'] == 1) {
				$date = date('ym', $postdate);
				$htmurldel = R_P . $db_readdir . '/' . $fid . '/' . $date . '/' . $tid . '.html';
				P_unlink($htmurldel);
			}
			$toname = strip_tags($forum[$to_id]['name']);
			if ($ifmsg) {
				$msgdb[] = array('toUser' => $author, 'title' => getLangInfo('writemsg', 'move_title'),
					'content' => getLangInfo('writemsg', 'move_content', array('manager' => $windid, 'fid' => $fid,
						'tid' => $tid, 'tofid' => $to_id, 'subject' => $subject, 'postdate' => get_date($postdate),
						'forum' => strip_tags($forum[$fid]['name']), 'toforum' => $toname,
						'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
			}
			$logdb[] = array('type' => 'move', 'username1' => $author, 'username2' => $windid, 'field1' => $fid,
				'field2' => $tid, 'field3' => '', 'descrip' => 'move_descrip', 'timestamp' => $timestamp,
				'ip' => $onlineip, 'tid' => $tid, 'subject' => substrs($subject, 28), 'tofid' => $to_id,
				'toforum' => $toname, 'forum' => $forum[$fid]['name'], 'reason' => stripslashes($atc_content));

			//分类信息处理
			if ($modelid > 0) {
				$tablename = GetTopcitable($modelid);
				$db->update("UPDATE $tablename SET fid=" . S::sqlEscape($to_id) . " WHERE tid=" . S::sqlEscape($tid));
			}
			//分类信息处理


			//团购处理
			if ($special > 20) {
				$pcid = $special - 20;
				$pcid = (int) $pcid;
				$tablename = GetPcatetable($pcid);
				$db->update("UPDATE $tablename SET fid=" . S::sqlEscape($to_id) . " WHERE tid=" . S::sqlEscape($tid));
			}
			//团购处理

			//置顶帖处理
			if ($topped) {
				$updatetop = 1;
				$_topped = $db->get_one("SELECT * FROM pw_poststopped WHERE fid=" . S::sqlEscape($fid) . " AND tid=" . S::sqlEscape($tid) . " AND pid='0'");
				if ($_topped) {
					$db->update("UPDATE pw_poststopped SET uptime = " . S::sqlEscape($to_id) . " WHERE tid= " . S::sqlEscape($tid) . " AND pid = '0' AND fid = " . S::sqlEscape($fid));
					$db->update("REPLACE INTO pw_poststopped (fid,tid,pid,floor,uptime,overtime) values
						(" . S::sqlEscape($to_id) . "," . S::sqlEscape($tid) . ",'0'," . S::sqlEscape($_topped['floor']) . "," . S::sqlEscape($to_id) . "," . S::sqlEscape($_topped['overtime']) . ") ");
				}
			}
			//置顶帖处理
		}
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		sendMawholeMessages($msgdb);

		$remindinfo = strip_tags(getLangInfo('other', 'mawhole_move'));
		$to_threadcate = $to_subtype ? $to_subtype : $to_threadcate;

		//$db->update("UPDATE pw_threads SET fid=" . S::sqlEscape($to_id) . ",type=" . S::sqlEscape($to_threadcate) . " WHERE tid IN($mids)");
		pwQuery::update('pw_threads', 'tid IN (:tid)', array($mids), array('fid'=>$to_id, 'type'=>$to_threadcate));

		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . " WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		foreach ($ptable_a as $key => $val) {
			$pw_posts = GetPtable($key);
			//$db->update("UPDATE $pw_posts SET fid=" . S::sqlEscape($to_id) . " WHERE tid IN(" . S::sqlImplode($mids) . ")");
			pwQuery::update($pw_posts, 'tid IN(:tid)', array($mids), array('fid' => $to_id));
		}
		updateForumCount($fid, -$topic_all, -$replies_all, -$todaypost);
		updateForumCount($to_id, $topic_all, $replies_all, $todaypost);

		// $threadList = L::loadClass("threadlist", 'forum');
		// $threadList->refreshThreadIdsByForumId($fid);
		// $threadList->refreshThreadIdsByForumId($to_id);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>array($fid, $to_id)));

		if (!empty($cy_tids)) {
			$db->update("DELETE FROM pw_argument WHERE tid IN(" . S::sqlImplode($cy_tids) . ')');
			pwQuery::update('pw_threads', 'tid IN (:tid)', array($cy_tids), array('tpcstatus'=>0));
		}

		if ($updatetop) {
			updatetop();
		}

		//* P_unlink(D_P . 'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P . 'data/bbscache/c_cache.php');
		if (!defined('AJAX')) {
			refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
		} else {
			Showmsg('ajax_mawhole_operate_success');
		}
	}
} elseif ($action == 'copy') {

	if (empty($_POST['step'])) {

		include_once pwCache::getPath(D_P . 'data/bbscache/cache_post.php');
		include_once pwCache::getPath(D_P . 'data/bbscache/forum_typecache.php');
		if ($topic_type_cache) {
			foreach ($topic_type_cache as $key => $value) {
				foreach ($value as $k => $v) {
					$v['name'] = strip_tags($v['name']);
					if ($v['upid'] == 0) {
						$t_typedb[$key][$k] = $v['name'];
					} else {
						$t_subtypedb[$key][$v['upid']][$k] = $v['name'];
					}
				}
			}
		}

		if ($t_typedb) {
			$t_typedb = pwJsonEncode($t_typedb);
		}
		if ($t_subtypedb) {
			$t_subtypedb = pwJsonEncode($t_subtypedb);
		}

		$forumadd = '';

		$query = $db->query("SELECT fid,t_type,name,allowvisit,f_type FROM pw_forums");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['f_type'] == 'hidden' && strpos($rt['allowvisit'], ',' . $groupid . ',') !== false) {
				$forumadd .= "<option value='$rt[fid]'> &nbsp;|- $rt[name]</option>";
			}
		}
		@include_once pwCache::getPath(D_P . 'data/bbscache/forumcache.php');
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('to_id', 'ifmsg', 'to_threadcate', 'to_subtype'));
		if ($forum[$to_id]['type'] == 'category') {
			Showmsg('mawhole_error');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		$readdb = $ttable_a = array();
		foreach ($tidarray as $k => $v) {
			if (is_numeric($v)) {
				$selids .= $selids ? ',' . $v : $v;
				$ttable_a[GetTtable($v)][] = $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');
		//$updatetop = 0;
		$ufid = $fid;
		$to_threadcate = $to_subtype ? $to_subtype : $to_threadcate;
		foreach ($ttable_a as $pw_tmsgs => $val) {
			$val = S::sqlImplode($val);
			$query = $db->query("SELECT * FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid IN($val)");
			while ($rt = $db->fetch_array($query)) {
				$ufid != $rt['fid'] && Showmsg('admin_forum_right');
				$readdb[] = $rt;
			}
		}
		foreach ($readdb as $key => $read) {
			@extract($read);
			//$topped > 1 && $updatetop = 1;
			$toname = $forum[$to_id]['name'];
			if ($ifmsg) {
				$msgdb[] = array(
					'toUser' => $author,
					'title' => getLangInfo('writemsg', 'copy_title'),
					'content' => getLangInfo('writemsg', 'copy_content', array(
						'manager' => $windid,
						'fid' => $fid,
						'tid' => $tid,
						'tofid' => $to_id,
						'subject' => $subject,
						'postdate' => get_date($postdate),
						'forum' => strip_tags($forum[$fid]['name']),
						'toforum' => $toname,
						'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content)
					))
				);
			}
			$logdb[] = array(
				'type' => 'copy',
				'username1' => $author,
				'username2' => $windid,
				'field1' => $fid,
				'field2' => $tid,
				'field3' => '',
				'descrip' => 'copy_descrip',
				'timestamp' => $timestamp,
				'ip' => $onlineip,
				'tid' => $tid,
				'subject' => substrs($subject, 28),
				'tofid' => $to_id,
				'toforum' => $toname,
				'forum' => $forum[$fid]['name'],
				'reason' => stripslashes($atc_content)
			);
			$pwSQL = array(
				'fid' => $to_id,
				'icon' => $icon,
				'titlefont' => ''/*$titlefont*/,
				'author' => $author,
				'authorid' => $authorid,
				'subject' => $subject,
				'ifcheck' => $ifcheck,
				'type' => $to_threadcate,
				'postdate' => $postdate,
				'lastpost' => ($lastpost > $timestamp ? $timestamp : $lastpost),
				'lastposter' => $lastposter,
				'hits' => $hits,
				'replies' => $replies,
				'topped' => 0/*$topped*/,
				'locked' => $locked,
				'digest' => 0/*$digest*/,
				'special' => $special,
				'state' => $state,
				'ifupload' => $ifupload,
				'ifmail' => $ifmail,
				'ifshield' => $ifshield,
				'anonymous' => $anonymous,
				'ptable' => $db_ptable,
				'modelid' => $modelid
			);

			//$db->update("INSERT INTO pw_threads SET $pwSQL");
			pwQuery::insert('pw_threads', $pwSQL);

			$newtid = $db->insert_id();

			//分类信息处理
			if ($modelid > 0) {
				$modeliddb = $mSqldb = array();
				$tablename = GetTopcitable($modelid);
				$modeliddb = $db->get_one("SELECT * FROM $tablename WHERE tid=" . S::sqlEscape($tid));
				foreach (array_keys($modeliddb) as $value) {
					if ($value != 'fid' && $value != 'tid') {
						$mSqldb[$value] = $modeliddb[$value];
					}
				}
				$mSqldb['tid'] = $newtid;
				$mSqldb['fid'] = $to_id;
				$pwSQL = S::sqlSingle($mSqldb);
				$db->update("INSERT INTO $tablename SET $pwSQL");
			}
			//分类信息处理


			//团购处理
			if ($special > 20) {
				$pcid = $special - 20;
				$pcid = (int) $pcid;
				$tablename = GetPcatetable($pcid);
				$pcdb = $mSqldb = array();
				$pcdb = $db->get_one("SELECT * FROM $tablename WHERE tid=" . S::sqlEscape($tid));

				foreach (array_keys($pcdb) as $value) {
					if ($value != 'fid' && $value != 'tid') {
						$mSqldb[$value] = $pcdb[$value];
					}
				}
				$mSqldb['tid'] = $newtid;
				$mSqldb['fid'] = $to_id;
				$pwSQL = S::sqlSingle($mSqldb);
				$db->update("INSERT INTO $tablename SET $pwSQL");
			}
			//团购处理

			# memcache refresh
			// $threadList = L::loadClass("threadlist", 'forum');
			// $threadList->updateThreadIdsByForumId($to_id, $newtid);
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$to_id));

			$aid = str_replace("'", "\'", $aid);

			if ($special == 1) {
				$rs = $db->get_one("SELECT voteopts,modifiable,previewable,multiple,mostvotes,voters,timelimit,leastvotes,regdatelimit,creditlimit,postnumlimit FROM pw_polls WHERE tid=" . S::sqlEscape($tid));
				$pwSQL = S::sqlSingle(array('tid' => $newtid, 'voteopts' => $rs['voteopts'],
					'modifiable' => $rs['modifiable'], 'previewable' => $rs['previewable'],
					'multiple' => $rs['multiple'], 'mostvotes' => $rs['mostvotes'], 'voters' => $rs['voters'],
					'timelimit' => $rs['timelimit'], 'leastvotes' => $rs['leastvotes'],
					'regdatelimit' => $rs['regdatelimit'], 'creditlimit' => $rs['creditlimit'],
					'postnumlimit' => $rs['postnumlimit']), false);
				$db->update("INSERT INTO pw_polls SET $pwSQL");

				$query = $db->query("SELECT * FROM pw_voter WHERE tid=" . S::sqlEscape($tid));
				while ($rt = $db->fetch_array($query)) {
					$rt['tid'] = $newtid;
					$voterdb[] = $rt;
				}
				$voterdb && $db->update("INSERT INTO pw_voter(tid,uid,username,vote,time) VALUES" . S::sqlMulti($voterdb));
			} elseif ($special == 3) {
				$db->update("INSERT INTO pw_reward (tid,cbtype,catype,cbval,caval,timelimit,author,pid) SELECT $newtid,cbtype,catype,cbval,caval,timelimit,author,pid FROM pw_reward WHERE tid=" . S::sqlEscape($tid));
			}

			$remindinfo = strip_tags(getLangInfo('other', 'mawhole_copy'));
			$pw_tmsgs = GetTtable($newtid);

			/*插入帖子附件处理*/
			if ($aid && ($aidRelation = copyAttachs($tid, 0, $content, $newtid, 0, $to_id))) {
				$content = resetContentByAttach($content, $aidRelation);
			}
			/* 插入帖子附件处理 */

			$pwSQL = S::sqlSingle(array('tid' => $newtid, 'aid' => $aid, 'userip' => $userip, 'ifsign' => $ifsign,
				'buy' => $buy, 'ipfrom' => $ipfrom, 'remindinfo' => $remindinfo, 'ifconvert' => $ifconvert,
				'content' => $content), false);
			$db->update("INSERT INTO $pw_tmsgs SET $pwSQL");

			$pw_posts = GetPtable($ptable);
			$query2 = $db->query("SELECT * FROM $pw_posts WHERE tid=" . S::sqlEscape($tid));
			$pw_posts = GetPtable($db_ptable);
			while ($rt = $db->fetch_array($query2)) {
				@extract($rt);
				if ($db_plist && count($db_plist) > 1) {
					//* $db->update("INSERT INTO pw_pidtmp(pid) values(null)");
					//* $pid = $db->insert_id();
					$uniqueService = L::loadClass ('unique', 'utility');
					$pid = $uniqueService->getUnique('post');					
				} else {
					$pid = '';
				}
				/*$pwSQL = S::sqlSingle(array('pid' => $pid, 'fid' => $to_id, 'tid' => $newtid, 'aid' => $aid,
					'author' => $author, 'authorid' => $authorid, 'icon' => $icon, 'postdate' => $postdate,
					'subject' => $subject, 'userip' => $userip, 'ifsign' => $ifsign, 'alterinfo' => $alterinfo,
					'remindinfo' => $remindinfo, 'ipfrom' => $ipfrom, 'ifconvert' => $ifconvert,
					'ifcheck' => $ifcheck, 'content' => $content, 'ifshield' => $ifshield,
					'anonymous' => $anonymous), false);
				$db->update("INSERT INTO $pw_posts SET $pwSQL");
				*/
				$pwSQL =array('pid' => $pid, 'fid' => $to_id, 'tid' => $newtid, 'aid' => $aid,
					'author' => $author, 'authorid' => $authorid, 'icon' => $icon, 'postdate' => $postdate,
					'subject' => $subject, 'userip' => $userip, 'ifsign' => $ifsign, 'alterinfo' => $alterinfo,
					'remindinfo' => $remindinfo, 'ipfrom' => $ipfrom, 'ifconvert' => $ifconvert,
					'ifcheck' => $ifcheck, 'content' => $content, 'ifshield' => $ifshield,
					'anonymous' => $anonymous);
				pwQuery::insert($pw_posts, $pwSQL);
				$pid || $pid = $db->insert_id();

				if ($aid && ($aidRelation = copyAttachs($tid, $rt['pid'], $content, $newtid, $pid, $to_id))) {
					$content = resetContentByAttach($content, $aidRelation);
					//$db->update("UPDATE $pw_posts SET content=" . S::sqlEscape($content) . ' WHERE pid=' . S::sqlEscape($pid));
					pwQuery::update($pw_posts, 'pid=:pid', array($pid), array('content' => $content));
				}
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		updateforum($to_id);
		/*
		if ($updatetop) {
			updatetop();
		}
		*/
		if (! defined ( 'AJAX' )) {
			refreshto ( "thread.php?fid=$fid{$viewbbs}", 'operate_success' );
		} else {
			Showmsg ( $singleAction ? 'operate_success' : 'ajaxma_success' );
		}
	}
} elseif ($action == 'headtopic') {

	if (empty($_POST['step'])) {

		include_once pwCache::getPath(D_P . 'data/bbscache/cache_post.php');
		require_once pwCache::getPath(R_P . 'require/updateforum.php');
		$selforums = '';
		if (is_numeric($seltid)) {
			$rt = $db->get_one('SELECT fid,topped,toolfield FROM pw_threads WHERE tid=' . S::sqlEscape($seltid));
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			${'topped_' . $rt['topped']} = 'checked';

			$deftopped = ($isGM || (int) pwRights($isBM, 'topped') > $rt['topped']) ? $rt['topped'] : (int) pwRights($isBM, 'topped');
			list($timelimit) = explode(',', $rt['toolfield']);
			$timelimit && $timelimit = get_date($timelimit, 'Y-m-d H:i');
			$query = $db->query("SELECT distinct fid FROM pw_poststopped WHERE fid != '0' AND pid = '0' AND tid = " . $seltid);
			while ($rt = $db->fetch_array($query)) {
				$selforums .= $rt['fid'] . ',';
			}
			$selforums && $selforums = ',' . $selforums;
		}
		list($catedbs, $top_1, $top_2, $top_3) = getForumListForHeadTopic($fid);

		if ($top_1) {
			$top_1_index = pwJsonEncode(array_keys($top_1));
			$top_1 = pwJsonEncode($top_1);
		}
		if ($top_2) {
			$top_2_index = pwJsonEncode(array_keys($top_2));
			$top_2 = pwJsonEncode($top_2);
		}
		if ($top_3) {
			$top_3_index = pwJsonEncode(array_keys($top_3));
			$top_3 = pwJsonEncode($top_3);
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('topped', 'ifmsg', 'timelimit', 'nextto', 'selForums', 'defaultSelForums'));
		(is_null($topped)) && Showmsg('请选择置顶操作管理选项');
		$topped = intval($topped);
		$pwTopped = $isGM ? '3' : pwRights($isBM, 'topped');
		if ($topped > $pwTopped) {
			Showmsg('masigle_top');
		}
		empty($selForums) && $selForums = explode(',', trim($defaultSelForums, ','));
		if (empty($selForums)) {
			Showmsg('masigle_top_error');
		}
		!in_array($fid, $selForums) && $selForums[] = $fid;
		!checkForHeadTopic($topped, $fid, $selForums) && Showmsg('admin_forum_right');
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = $selids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$selids[] = $v;
					$threadIds[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
		}
		empty($selids) && Showmsg('mawhole_nodata');

		$msgdb = $logdb = array();
		$timelimit = PwStrtoTime($timelimit);
		$toolfield = $timelimit > $timestamp && $topped ? $timelimit : '';

		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,topped,toolfield FROM pw_threads WHERE tid IN(" . S::sqlImplode($selids) . ")");
		$tid_fid = array();
		while ($rt = $db->fetch_array($query)) {
			$tid_fid[$rt['tid']] = $rt['fid'];
			if ($rt['topped'] > $pwTopped) {
				Showmsg('masigle_top');
			}
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			if ($topped && $topped != $rt['topped']) {
				if ($ifmsg) {
					$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'top_title'),
						'content' => getLangInfo('writemsg', 'top_content', array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$logdb[] = array('type' => 'topped', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $tid, 'field3' => '', 'descrip' => 'topped_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip, 'topped' => $topped, 'tid' => $rt['tid'],
					'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			} elseif ($rt['topped'] && !$topped) {
				if ($ifmsg) {
					$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'untop_title'),
						'content' => getLangInfo('writemsg', 'untop_content', array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$logdb[] = array('type' => 'topped', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'untopped_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip, 'tid' => $rt['tid'],
					'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			}
			if ($toolfield || $rt['toolfield']) {
				$t = explode(',', $rt['toolfield']);
				$rt['toolfield'] = $toolfield . ',' . $t[1];
				//$pwSQL = S::sqlSingle(array('topped' => $topped, 'toolfield' => $rt['toolfield']));
				//$db->update("UPDATE pw_threads SET $pwSQL WHERE tid=" . S::sqlEscape($rt['tid']));
				$pwSQL = array('topped' => $topped, 'toolfield' => $rt['toolfield']);
				pwQuery::update('pw_threads', "tid=:tid", array($rt['tid']), $pwSQL);
				//* $threads = L::loadClass('Threads', 'forum');
				//* $threads->delThreads($rt['tid']);
			} else {
				$tids[] = $rt['tid'];
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = $topped ? getLangInfo('other', 'mawhole_top_2') : getLangInfo('other', 'mawhole_top_1');
		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . "WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		if ($tids) {
			//$db->update("UPDATE pw_threads SET topped=" . S::sqlEscape($topped) . "WHERE tid IN(" . S::sqlImplode($tids) . ")");
			pwQuery::update('pw_threads', "tid IN (:tid)", array($tids), array("topped"=>$topped));
			// $threadList = L::loadClass("threadlist", 'forum');
			// $threadList->refreshThreadIdsByForumId($fid);
			// $threads = L::loadClass('Threads', 'forum');
			// $threads->delThreads($tids);
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		}
		if (!empty($selids)) {
			if ($topped) {
				if (!empty($selForums)) {
					$_topped = array();
					foreach ($selForums as $_forum) {
						foreach ($selids as $_tid) {
							$_topped[] = array('fid' => $_forum, 'tid' => $_tid, 'pid' => '0', 'floor' => $topped,
								'uptime' => $tid_fid[$_tid], 'overtime' => $toolfield);
						}
					}
					$db->update("DELETE FROM pw_poststopped WHERE pid = '0' AND fid != '0' AND tid IN (" . S::sqlImplode($selids) . ")");
					if ($_topped) $db->update("REPLACE INTO pw_poststopped (fid,tid,pid,floor,uptime,overtime) values " . S::sqlMulti($_topped));
				}
			} else {
				$db->update("DELETE FROM pw_poststopped WHERE tid IN (" . S::sqlImplode($selids) . ") AND pid = '0' ");
			}
		}
		updatetop();
		delfcache($fid, $db_fcachenum);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		/*置顶印戳*/
		if ($overprint) { /*过滤取消置顶*/
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'headtopic');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'headtopic', 1, $nextto);
		}
		if ($nextto) {
			$selids = implode(',',$tidarray);
			if (!defined('AJAX')) {
				refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$selids{$viewbbs}", 'operate_success');
			} else {
				Showmsg("ajax_nextto");
			}
		} else {
			if (!defined('AJAX')) {
				refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
			} else {
				Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
			}
		}
	}
} elseif ($action == 'digest') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$rt = $db->get_one('SELECT fid,digest FROM pw_threads WHERE tid=' . S::sqlEscape($seltid));
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			${'digest_' . intval($rt['digest'])} = 'checked';
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('digest', 'ifmsg', 'nextto'));
		(is_null($digest)) && Showmsg('mawhole_nodigest');
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = $selids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$tids[] = $v;
					$threadIds[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
		}
		!$tids && Showmsg('mawhole_nodata');

		$tmpTids = $tids;
		$selids = S::sqlImplode($tids);
		require_once (R_P . 'require/credit.php');
		$creditset = $credit->creditset($foruminfo['creditset'], $db_creditset);

		$add_rvrc = (int) $creditset['Digest']['rvrc'];
		$add_money = (int) $creditset['Digest']['money'];
		$del_rvrc = abs($creditset['Undigest']['rvrc']);
		$del_money = abs($creditset['Undigest']['money']);

		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,digest FROM pw_threads WHERE tid IN($selids)");
		while ($rt = $db->fetch_array($query)) {
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			if (!$rt['digest'] && $digest) {
				if ($ifmsg) {
					$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'digest_title'),
						'content' => getLangInfo('writemsg', 'digest_content', array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'affect' => "{$db_rvrcname}：+{$add_rvrc}，{$db_moneyname}：+{$add_money}",
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$credit->addLog('topic_Digest', $creditset['Digest'], array('uid' => $rt['authorid'],
					'username' => $rt['author'], 'ip' => $onlineip, 'fname' => strip_tags($forum[$fid]['name']),
					'operator' => $windid));
				$credit->sets($rt['authorid'], $creditset['Digest'], false);
				$credit->setMdata($rt['authorid'], 'digests', 1);

				$logdb[] = array('type' => 'digest', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'digest_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip, 'digest' => $digest,
					'affect' => "{$db_rvrcname}：+{$add_rvrc}，{$db_moneyname}：+{$add_money}", 'tid' => $rt['tid'],
					'digest' => $digest, 'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			} elseif ($rt['digest'] && !$digest) {
				if ($ifmsg) {
					$msgdb[] = array('toUser' => $rt['author'],
						'title' => getLangInfo('writemsg', 'undigest_title'),
						'content' => getLangInfo('writemsg', 'undigest_content', array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'affect' => "{$db_rvrcname}：-{$del_rvrc}，{$db_moneyname}：-{$del_money}",
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$credit->addLog('topic_Undigest', $creditset['Undigest'], array('uid' => $rt['authorid'],
					'username' => $rt['author'], 'ip' => $onlineip, 'fname' => strip_tags($forum[$fid]['name']),
					'operator' => $windid));
				$credit->sets($rt['authorid'], $creditset['Undigest'], false);
				$credit->setMdata($rt['authorid'], 'digests', -1);

				$logdb[] = array('type' => 'digest', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'undigest_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip,
					'affect' => "{$db_rvrcname}：-{$del_rvrc}，{$db_moneyname}：-{$del_money}", 'tid' => $rt['tid'],
					'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			}
		}
		$credit->runsql();

		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = $digest ? getLangInfo('other', 'mawhole_digest_2') : getLangInfo('other', 'mawhole_digest_1');
		//$db->update("UPDATE pw_threads SET digest=" . S::sqlEscape($digest) . " WHERE tid IN($selids)", 0);
		pwQuery::update('pw_threads', 'tid IN (:tid)', array($tmpTids), array('digest'=>$digest));
		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . " WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tids);

		delfcache($fid, $db_fcachenum);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));

		/*精华印戳*/
		if ($overprint) {
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'digest');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'digest', 1, $nextto);
		}
		if ($nextto) {
			$selids = implode(',',$tidarray);
			if (!defined('AJAX')) {
				refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$selids{$viewbbs}", 'operate_success');
			} else {
				Showmsg("ajax_nextto");
			}
		} else {
			if (!defined('AJAX')) {
				refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
			} else {
				Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
			}
		}
	}
} elseif ($action == 'lock') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$rt = $db->get_one('SELECT fid,locked FROM pw_threads WHERE tid=' . S::sqlEscape($seltid));
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			$rt['locked'] %= 3;
			${'lock_' . $rt['locked']} = 'checked';
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('ifmsg'), 'P', 2);
		S::gp(array('locked'), 'P');
		(!is_string($locked) || !S::inArray($locked,array('0','1','2'))) && Showmsg('请选择锁定操作管理选项');
		$locked = intval($locked);
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = $selids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$tids[] = $v;
					$threadIds[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
		}
		!$tids && Showmsg('mawhole_nodata');

		$selids = S::sqlImplode($tids);
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,locked FROM pw_threads WHERE tid IN($selids)");
		while ($rt = $db->fetch_array($query)) {
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			if ($rt['locked'] % 3 != $locked && $locked) {
				if ($locked == 2) {
					P_unlink(R_P . "$db_readdir/$fid/" . date('ym', $rt['postdate']) . "/$tid.html");
				}
				$s = $rt['locked'] > 2 ? $locked + 3 : $locked;
				//$db->update('UPDATE pw_threads SET locked=' . S::sqlEscape($s) . ' WHERE tid=' . S::sqlEscape($rt['tid']));
				pwQuery::update('pw_threads', "tid=:tid", array($rt['tid']), array("locked"=>$s));
				if ($ifmsg) {
					if ($locked == 2) {
						$temp['title'] = 'lock_title_2';
						$temp['content'] = 'lock_content_2';
					} else {
						$temp['title'] = 'lock_title';
						$temp['content'] = 'lock_content';
					}
					$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', $temp['title']),
						'content' => getLangInfo('writemsg', $temp['content'], array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$logdb[] = array('type' => 'locked', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'lock_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip, 'tid' => $rt['tid'],
					'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			} elseif ($rt['locked'] % 3 != 0 && !$locked) {
				$s = $rt['locked'] > 2 ? 3 : 0;
				//$db->update("UPDATE pw_threads SET locked='$s' WHERE tid=" . S::sqlEscape($rt['tid']));
				pwQuery::update('pw_threads', "tid=:tid", array($rt['tid']), array("locked"=>$s));
				if ($ifmsg) {
					$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'unlock_title'),
						'content' => getLangInfo('writemsg', 'unlock_content', array('manager' => $windid,
							'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
							'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
							'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
				}
				$logdb[] = array('type' => 'locked', 'username1' => $rt['author'], 'username2' => $windid,
					'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'unlock_descrip',
					'timestamp' => $timestamp, 'ip' => $onlineip, 'tid' => $rt['tid'],
					'subject' => substrs($rt['subject'], 28), 'forum' => $forum[$fid]['name'],
					'reason' => stripslashes($atc_content));
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = getLangInfo('other', 'mawhole_locked_' . $locked);
		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . "WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		/*锁定印戳*/
		if ($overprint) {
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'lock');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'lock', 1, $nextto);
		}

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tids);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$tids));
		if (! defined ( 'AJAX' )) {
			refreshto ( "thread.php?fid=$fid{$viewbbs}", 'operate_success' );
		} else {
			Showmsg ( $singleAction ? 'operate_success' : 'ajaxma_success' );
		}
	}
} elseif ($action == 'pushtopic') {

	$pushtime_top = (int) pwRights(false, 'pushtime');

	if (empty($_POST['step'])) {

		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('ifmsg', 'nextto', 'pushtime'));
		if (!is_numeric($pushtime)) {
			Showmsg('mawhole_erropushtime');
		}
		if ($pushtime_top && $pushtime > $pushtime_top) {
			Showmsg('mawhole_beyondpushtime');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$selids[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
			$threadIds = $selids;
			$tmpSelids = $selids;
			$selids = S::sqlImplode($selids);
		}
		!$selids && Showmsg('mawhole_nodata');
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject FROM pw_threads WHERE tid IN($selids)");
		while ($rt = $db->fetch_array($query)) {
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			if ($ifmsg) {
				$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'push_title'),
					'content' => getLangInfo('writemsg', 'push_content', array('manager' => $windid, 'fid' => $fid,
						'tid' => $rt['tid'], 'subject' => $rt['subject'], 'postdate' => get_date($rt['postdate']),
						'forum' => strip_tags($forum[$fid]['name']), 'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content))));
			}
			$logdb[] = array('type' => 'push', 'username1' => $rt['author'], 'username2' => $windid, 'field1' => $fid,
				'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'push_descrip', 'timestamp' => $timestamp,
				'ip' => $onlineip, 'tid' => $rt['tid'], 'subject' => substrs($rt['subject'], 28),
				'forum' => $forum[$fid]['name'], 'reason' => stripslashes($atc_content));
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = getLangInfo('other', 'mawhole_push');
		$pushtime < 0 && $pushtime = 1;
		$uptime = $timestamp + $pushtime * 3600;
		//$db->update("UPDATE pw_threads SET lastpost=" . S::sqlEscape($uptime) . "WHERE tid IN($selids)");
		pwQuery::update('pw_threads', "tid IN (:tid)", array($tmpSelids), array('lastpost'=>$uptime));

		# memcache refresh
		// $threadList = L::loadClass("threadlist", 'forum');
		//$threadList->refreshThreadIdsByForumId($fid);
		/*
		foreach ($threadIds as $tid) {
			$threadList->updateThreadIdsByForumId($fid, $tid, $pushtime * 3600);
		}*/
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));

		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . "WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		delfcache($fid, $db_fcachenum);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($threadIds);

		/*提前印戳*/
		if ($overprint) {
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'pushtopic');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'pushtopic', 1, $nextto);
		}
		if ($nextto) {
			$selids = implode(',',$tidarray);
			if (!defined('AJAX')) {
				refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$selids{$viewbbs}", 'operate_success');
			} else {
				Showmsg("ajax_nextto");
			}
		} else {
			if (!defined('AJAX')) {
				refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
			} else {
				Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
			}
		}
	}
} elseif ($action == 'downtopic') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$rt = $db->get_one("SELECT locked FROM pw_threads WHERE tid=" . S::sqlEscape($seltid));
			if ($rt['locked'] > 2) {
				$lock_1 = 'checked';
			} else {
				$lock_0 = 'checked';
			}
		} else {
			$lock_0 = 'checked';
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('ifmsg', 'nextto', 'timelimit', 'ifpush'));
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$selids[] = $v;
					$threadIds[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
			$selids = S::sqlImplode($selids);
		}
		!$selids && Showmsg('mawhole_nodata');
		$timelimit < 0 && $timelimit = 24;
		$downtime = $timelimit * 3600;
		$msgdb = $logdb = array();
		//* $threadList = L::loadClass("threadlist", 'forum');
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,locked FROM pw_threads WHERE tid IN($selids)");
		while ($rt = $db->fetch_array($query)) {
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			$sql = ",locked='" . ($ifpush ? ($rt['locked'] % 3 + 3) : $rt['locked'] % 3) . "'";
			//$db->update("UPDATE pw_threads SET lastpost=lastpost-" . S::sqlEscape($downtime) . " $sql WHERE tid=" . S::sqlEscape($rt['tid']));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET lastpost=lastpost-:lastpost $sql WHERE tid=:tid", array('pw_threads', $downtime, $rt['tid'])));
			if ($ifmsg) {
				$msgdb[] = array('toUser' => $rt['author'], 'title' => getLangInfo('writemsg', 'down_title'),
					'content' => getLangInfo('writemsg', 'down_content', array('manager' => $windid,
						'timelimit' => $timelimit, 'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
						'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
						'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
			}
			$logdb[] = array('type' => 'down', 'username1' => $rt['author'], 'username2' => $windid, 'field1' => $fid,
				'field2' => $rt['tid'], 'field3' => '', 'descrip' => 'down_descrip', 'timestamp' => $timestamp,
				'ip' => $onlineip, 'tid' => $rt['tid'], 'subject' => substrs($rt['subject'], 28),
				'forum' => $forum[$fid]['name'], 'reason' => stripslashes($atc_content));
			//* $threadList->updateThreadIdsByForumId($fid, $rt['tid'], $downtime);
				Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = getLangInfo('other', 'mawhole_down');

		foreach ($ttable_a as $pw_tmsgs => $val) {
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . " WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}
		delfcache($fid, $db_fcachenum);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($threadIds);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$threadIds));

		/*压帖印戳*/
		if ($overprint) {
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'downtopic');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'downtopic', 1, $nextto);
		}
		if (!defined('AJAX')) {
			refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
		} else {
			Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
		}
	}
} elseif ($action == 'edit') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$rt = $db->get_one("SELECT fid,titlefont,author FROM pw_threads WHERE tid=" . S::sqlEscape($seltid));
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			$titledetail = explode("~", $rt['titlefont']);
			$titlecolor = $titledetail[0];
			if ($titlecolor && !preg_match('/\#[0-9A-F]{6}/is', $titlecolor)) {
				$titlecolor = '';
			}
			if ($titledetail[1] == '1') {
				$stylename[1] = 'b one';
			} else {
				$stylename[1] = 'b';
			}
			if ($titledetail[2] == '1') {
				$stylename[2] = 'u one';
			} else {
				$stylename[2] = 'u';
			}
			if ($titledetail[3] == '1') {
				$stylename[3] = 'one';
			} else {
				$stylename[3] = '';
			}
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('title1', 'title2', 'title3', 'title4', 'nextto', 'ifmsg', 'timelimit'));
		count($tidarray) > 500 && Showmsg('mawhole_count');

		$selids = $tids = $ttable_a = $threadIds = array();
		if (is_array($tidarray)) {
			foreach ($tidarray as $k => $v) {
				if (is_numeric($v)) {
					$selids[] = $v;
					$threadIds[] = $v;
					$ttable_a[GetTtable($v)][] = $v;
				}
			}
		}
		$selids = S::sqlImplode($selids);
		if ($title1 && !preg_match('/#[0-9A-F]{6}/is', $title1)) {
			Showmsg('mawhole_nodata');
		}
		!$selids && Showmsg('mawhole_nodata');

		$titlefont = S::escapeChar("$title1~$title2~$title3~$title4~$title5~$title6~");
		$ifedit = (!$title1 && !$title2 && !$title3 && !$title4) ? 0 : 1;
		$toolfield = $timelimit > 0 && $ifedit ? $timelimit * 86400 + $timestamp : '';

		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,toolfield FROM pw_threads WHERE tid IN($selids)");
		while ($rt = $db->fetch_array($query)) {
			if ($fid != $rt['fid']) {
				Showmsg('admin_forum_right');
			}
			if ($ifmsg) {
				$msgdb[] = array('toUser' => $rt['author'],
					'title' => getLangInfo('writemsg', $ifedit ? 'highlight_title' : 'unhighlight_title'),
					'content' => getLangInfo('writemsg', $ifedit ? 'highlight_content' : 'unhighlight_content', array(
						'manager' => $windid, 'fid' => $fid, 'tid' => $rt['tid'], 'subject' => $rt['subject'],
						'postdate' => get_date($rt['postdate']), 'forum' => strip_tags($forum[$fid]['name']),
						'admindate' => get_date($timestamp), 'reason' => stripslashes($atc_content))));
			}
			$logdb[] = array('type' => 'highlight', 'username1' => $rt['author'], 'username2' => $windid,
				'field1' => $fid, 'field2' => $rt['tid'], 'field3' => '',
				'descrip' => $ifedit ? 'highlight_descrip' : 'unhighlight_descrip', 'timestamp' => $timestamp,
				'ip' => $onlineip, 'tid' => $rt['tid'], 'subject' => substrs($rt['subject'], 28),
				'forum' => $forum[$fid]['name'], 'reason' => stripslashes($atc_content));
			if ($toolfield || $rt['toolfield']) {
				$t = explode(',', $rt['toolfield']);
				$rt['toolfield'] = $t[0] . ',' . $toolfield;
				//$db->update("UPDATE pw_threads SET titlefont=" . S::sqlEscape($titlefont) . ',toolfield=' . S::sqlEscape($rt['toolfield']) . ' WHERE tid=' . S::sqlEscape($rt['tid']));
				pwQuery::update('pw_threads', 'tid=:tid', array($rt['tid']), array('titlefont'=>$titlefont, 'toolfield'=>$rt['toolfield']));
				/* clear thread cache*/
				//* $threads = L::loadClass('Threads', 'forum');
				//* $threads->delThreads($rt['tid']);
			} else {
				$tids[] = $rt['tid'];
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$remindinfo = getLangInfo('other', 'mawhole_edit_' . $ifedit);

		if ($tids) {
			//$db->update("UPDATE pw_threads SET titlefont=" . S::sqlEscape($titlefont) . " WHERE tid IN(" . S::sqlImplode($tids) . ")");
			pwQuery::update('pw_threads', 'tid IN(:tid)', array($tids), array('titlefont'=>$titlefont));
		}
		$_arrThreadIds = array();
		foreach ($ttable_a as $pw_tmsgs => $val) {
			$_arrThreadIds[] = $val;
			//* $val = S::sqlImplode($val);
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo) . " WHERE tid IN($val)");
			pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($val), array('remindinfo'=>$remindinfo));
		}

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tids);

		delfcache($fid, $db_fcachenum);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		
		/*加亮印戳*/
		if ($overprint) {
			$seltid = $seltid ? $seltid : $threadIds;
			overPrint($overprint, $seltid, 'headlight');
			defined('AJAX') && showOverPrint($overprint, $seltid, 'headlight', 1, $nextto);
		}
		if ($nextto) {
			$selids = implode(',',$tidarray);
			if (!defined('AJAX')) {
				refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$selids{$viewbbs}", 'operate_success');
			} else {
				Showmsg("ajax_nextto");
			}
		} else {
			if (!defined('AJAX')) {
				refreshto("thread.php?fid=$fid{$viewbbs}", 'operate_success');
			} else {
				Showmsg($singleAction ? 'operate_success' : 'ajaxma_success');
			}
		}
	}
} elseif ($action == 'unite') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$unitetype = 'from';
		} else {
			foreach ($threaddb as $key => $value) {
				if ($value['topped'] || $value['special'] || $value['digest'] || $value['toolinfo']) {
					Showmsg('unite_limit');
				}
			}
			$unitetype = 'to';
		}
		require_once PrintEot($template);
		footer();

	} else {

		PostCheck();
		S::gp(array('unitetid', 'unitetype', 'ifmsg'));
		$totid = '';
		$ttable_a = $readdb = array();

		if ($unitetype == 'to') {
			$totid = (int) $unitetid;
			$tidarray[] = $totid;
		} else {
			count($tidarray) > 500 && Showmsg('mawhole_count');
			$totid = $tidarray[0];
			$tidarray = array_merge($tidarray, explode(',', $unitetid));
		}
		foreach ($tidarray as $k => $v) {
			if (is_numeric($v)) {
				$ttable_a[GetTtable($v)][] = $v;
			}
		}
		if (empty($totid) || count($tidarray) < 2) {
			Showmsg('请输入合并帖tid');
		}

		foreach ($ttable_a as $pw_tmsgs => $val) {
			if (empty($val)) continue;
			$val = S::sqlImplode($val);
			$query = $db->query("SELECT * FROM pw_threads t LEFT JOIN $pw_tmsgs tm USING(tid) WHERE t.tid IN($val)");
			while ($rt = $db->fetch_array($query)) {
				$rt['fid'] != $fid && Showmsg('unite_fid_error');
				if ($rt['tid'] != $totid && ($rt['topped'] || $rt['special'] || $rt['digest'] || $rt['toolinfo'])) {
					Showmsg('unite_limit');
				}
				$readdb[$rt['tid']] = $rt;
			}
		}
		$todb = $readdb[$totid];
		unset($readdb[$totid]);

		if (!$todb || !$readdb) {
			//Showmsg('data_error');
			Showmsg('帖子tid输入有误');
		}
		$fromArticleIds = $fromColonyIds = array();
		$pw_attachs = L::loadDB('attachs', 'forum');
		$pw_posts = GetPtable($todb['ptable']);
		$remindinfo = getLangInfo('other', 'mawhole_unite');
		$replies = 0;
		foreach ($readdb as $key => $fromdb) {
			getstatus($fromdb['tpcstatus'], 1) ? $fromColonyIds[] = $key : $fromArticleIds[] = $key;
			if ($db_plist && count($db_plist) > 1) {
				//* $db->update("INSERT INTO pw_pidtmp(pid) values(null)");
				//* $pid = $db->insert_id();
				$uniqueService = L::loadClass ('unique', 'utility');
				$pid = $uniqueService->getUnique('post');				
			} else {
				$pid = '';
			}
			/*$pwSQL = S::sqlSingle(array('pid' => $pid, 'fid' => $fid, 'tid' => $totid, 'aid' => $fromdb['aid'],
				'author' => $fromdb['author'], 'authorid' => $fromdb['authorid'], 'icon' => $fromdb['icon'],
				'postdate' => $fromdb['postdate'], 'subject' => $fromdb['subject'], 'userip' => $fromdb['userip'],
				'ifsign' => $fromdb['ifsign'], 'alterinfo' => $fromdb['alterinfo'], 'ipfrom' => $fromdb['ipfrom'],
				'ifconvert' => $fromdb['ifconvert'], 'ifcheck' => $fromdb['ifcheck'],
				'content' => $fromdb['content'], 'ifmark' => $fromdb['ifmark'], 'ifshield' => $fromdb['ifshield']), false);
			$db->update("INSERT INTO $pw_posts SET $pwSQL");
			*/
			$pwSQL = array('pid' => $pid, 'fid' => $fid, 'tid' => $totid, 'aid' => $fromdb['aid'],
				'author' => $fromdb['author'], 'authorid' => $fromdb['authorid'], 'icon' => $fromdb['icon'],
				'postdate' => $fromdb['postdate'], 'subject' => $fromdb['subject'], 'userip' => $fromdb['userip'],
				'ifsign' => $fromdb['ifsign'], 'alterinfo' => $fromdb['alterinfo'], 'ipfrom' => $fromdb['ipfrom'],
				'ifconvert' => $fromdb['ifconvert'], 'ifcheck' => $fromdb['ifcheck'],
				'content' => $fromdb['content'], 'ifmark' => $fromdb['ifmark'], 'ifshield' => $fromdb['ifshield']);
			pwQuery::insert($pw_posts, $pwSQL);
			!$pid && $pid = $db->insert_id();
			$replies += $fromdb['replies'] + 1;

			# $db->update('DELETE FROM pw_threads WHERE tid='.S::sqlEscape($fromdb['tid']));
			# ThreadManager
			//* $threadManager = L::loadClass("threadmanager", 'forum');
			//* $threadManager->deleteByThreadId($fromdb['fid'], $fromdb['tid']);
			$threadService = L::loadclass('threads', 'forum');
			$threadService->deleteByThreadId($fromdb['tid']);
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fromdb['fid']));

			$pw_tmsgsf = GetTtable($fromdb['tid']);
			//* $db->update("DELETE FROM $pw_tmsgsf WHERE tid=" . S::sqlEscape($fromdb['tid']));
			pwQuery::delete($pw_tmsgsf, 'tid=:tid', array($fromdb['tid']));
			
			if ($db_guestread) {
				require_once (R_P . 'require/guestfunc.php');
				clearguestcache($fromdb['tid'], $replies);
			}
			if ($todb['ptable'] == $fromdb['ptable']) {
				//$db->update("UPDATE $pw_posts SET tid=" . S::sqlEscape($totid) . ' WHERE tid=' . S::sqlEscape($fromdb['tid']));
				pwQuery::update($pw_posts, 'tid=:tid', array($fromdb['tid']), array('tid' => $totid));
			} else {
				$pw_postsf = GetPtable($fromdb['ptable']);
				$db->update("INSERT INTO $pw_posts SELECT * FROM $pw_postsf WHERE tid=" . S::sqlEscape($fromdb['tid']));
				//$db->update("UPDATE $pw_posts SET tid=" . S::sqlEscape($totid) . " WHERE tid=" . S::sqlEscape($fromdb['tid']));
				pwQuery::update($pw_posts, 'tid=:tid', array($fromdb['tid']), array('tid' => $totid));
				//$db->update("DELETE FROM $pw_postsf WHERE tid=" . S::sqlEscape($fromdb['tid']));
				pwQuery::delete($pw_postsf, 'tid=:tid', array($fromdb['tid']));
			}
			if ($fromdb['aid']) {
				$pw_attachs->updateByTid($fromdb['tid'], 0, array('pid' => $pid, 'tid' => $totid));
			}
			$pw_attachs->updateByTid($fromdb['tid'], array('tid' => $totid));
			if ($ifmsg) {
				$msgdb[] = array('toUser' => $fromdb['author'],
					'title' => getLangInfo('writemsg', 'unite_title', array('manager' => $windid)),
					'content' => getLangInfo('writemsg', 'unite_content', array('manager' => $windid, 'fid' => $fid,
						'tid' => $totid, 'subject' => $todb['subject'], 'postdate' => get_date($todb['postdate']),
						'forum' => strip_tags($forum[$fid]['name']), 'admindate' => get_date($timestamp),
						'reason' => stripslashes($atc_content))));
			}
			$log = array('type' => 'unite', 'username1' => $fromdb['author'], 'username2' => $windid,
				'field1' => $fid, 'field2' => '', 'field3' => '', 'descrip' => 'unite_descrip',
				'timestamp' => $timestamp, 'ip' => $onlineip, 'tid' => $totid,
				'subject' => substrs($todb['subject'], 28), 'forum' => $forum[$fid]['name'],
				'reason' => stripslashes($atc_content));
			writelog($log);
		}
		//$db->update("UPDATE pw_threads SET replies=replies+" . S::sqlEscape($replies, false) . " WHERE tid=" . S::sqlEscape($totid));
		$db->update(pwQuery::buildClause('UPDATE :pw_table SET replies=replies+:replies WHERE tid=:tid', array('pw_threads', $replies, $totid)));
		$pw_tmsgs = GetTtable($totid);
		//* $db->update("UPDATE $pw_tmsgs SET remindinfo=" . S::sqlEscape($remindinfo, false) . " WHERE tid=" . S::sqlEscape($totid));
		pwQuery::update($pw_tmsgs, 'tid=:tid', array($totid), array('remindinfo'=>$remindinfo));

		updateforum($fid);
		$weiboService = L::loadClass('weibo', 'sns');
		$fromColonyIds && $weiboService->deleteWeibosByObjectIdsAndType($fromColonyIds,'group_article');
		$fromArticleIds && $weiboService->deleteWeibosByObjectIdsAndType($fromArticleIds,'article');
		$tousername = $db->get_value("SELECT author FROM pw_threads WHERE tid=" . S::sqlEscape($totid));
		M::sendNotice(array($tousername), array('title' => getLangInfo('writemsg', 'unite_title'),
			'content' => getLangInfo('writemsg', 'unite_content', array('manager' => $windid, 'fid' => $fid,
				'tid' => $totid, 'subject' => $todb['subject'], 'postdate' => get_date($todb['postdate']),
				'forum' => strip_tags($forum[$fid]['name']), 'admindate' => get_date($timestamp),
				'reason' => stripslashes($atc_content)))));

		if ($ifmsg) sendMawholeMessages($msgdb);

		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($totid);
		//* $threads->delThreads($fromdb['tid']);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$fromdb['tid']));
		if (!defined('AJAX')) {
			refreshto("read.php?tid=$totid{$viewbbs}", 'operate_success');
		} else {
			Showmsg('ajax_unite_success');
		}
	}
} elseif ($action == 'getthreadcates') {

	$fid = (int) S::getGP('fid');
	if (!($foruminfo = L::forum($fid))) {
		Showmsg('data_error');
	}
	$tcoptions = '';
	if ($foruminfo['t_type']) {
		$t_typedb = explode("\t", $foruminfo['t_type']);
		$t_typedb = array_unique($t_typedb);
		foreach ($t_typedb as $key => $value) {
			$value && $tcoptions .= '<option value="' . $key . '">' . $value . '</option>';
		}
	}
	echo $tcoptions;
	exit();

} elseif ($action == 'overprint') {

	S::gp(array('step', 'oid'));
	if ($step == 2) {
		$oid = intval($oid);
		$seltid = intval($seltid);
		if ($oid < 0 || $seltid < 1) {
			defined('AJAX') && showOverPrint(1, $seltid, '', 0, '', "数据有误，请重试");
		}
		overPrint(1, $seltid, '', $oid);
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($seltid);
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$seltid));
		defined('AJAX') && showOverPrint(1, $seltid, '', 1, '', "恭喜，设置印戳完成", $oid);
	}
	$overPrintService = L::loadclass("overprint", 'forum');
	echo $overPrintService->getunRelatedsHTML($fid, $seltid);
	footer();

} elseif ($action == 'batch'){

	if ($cyid) {
		!$db_groups_open && Showmsg('groups_close');
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		include_once(D_P . 'data/bbscache/o_config.php');
		$newColony = new PwColony($cyid);
		if (!$colony =& $newColony->getInfo()) {
			Showmsg('data_error');
		}
		$ifadmin = $newColony->getIfadmin();
	}
	//版块浏览及管理权限
	$pwSystem = array();
	$admincheck = $ajaxcheck = $managecheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
	if ($groupid != 'guest') {
		L::loadClass('forum', 'forum', false);

		if ($colony) {//群组论坛浏览方式
			$ifcolonyadmin = $newColony->getColonyAdmin();
			$ifbbsadmin = $newColony->getBbsAdmin($isGM);
			$fid = $newColony->info['classid'];
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
	require_once PrintEot('ajax_mawholebatch');
	ajax_footer();
}

function showOverPrint($overprint, $tid, $operate, $status = 1, $nextto = '', $message = '', $oid = '-1') {
	if (!in_array($overprint, array(1, 2))) {return false;}
	if (!$status) {
		Showmsg($message);
		footer();
	}
	$overPrintService = L::loadclass("overprint", 'forum'); /* @var $overPrintService PW_OverPrint */
	$message = $message ? $message : "操作成功!\treload";
	if ($operate) {
		$related = $overPrintService->getOperatesMaps($operate);
	} else {
		$related = $oid;
	}
	if ($overprint == 2 || $oid == 0) { /*移除印戳*/
		$icon = "";
	} else {
		$icon = $overPrintService->getOverPrintIcon($related);
	}
	/*过滤*/
	if ($old_related = $overPrintService->checkThreadRelated($overprint, $operate, $tid)) {
		$icon = $overPrintService->getOverPrintIcon($old_related);
	}
	/*后续操作*/
	if ($nextto) {
		$GLOBALS['selids'] = $tid;
		Showmsg('ajax_nextto');
	}
		$action = !$nextto ? '' : "nextto\tmawhole.php?action=" . $GLOBALS[nextto] . "&fid=" . $GLOBALS[fid] . "&ajax=1\tseltid=" . $tid . "\t" . $GLOBALS[nextto];
	$message = $message . "\toverprint\t" . $icon . "\t" . $action;
	Showmsg($message);
	footer();
}

function checkRelated($operate) {
	$overPrintService = L::loadclass("overprint", 'forum');
	return $overPrintService->checkRelated($operate);
}

function deleteThreadsHander($tidarray) {
	global $windid, $manager, $groupid, $SYSTEM;
	PostCheck();
	(!$SYSTEM['superright'] || !$SYSTEM['delatc']) && Showmsg('mawhole_right');
	if ($tidarray == "") {
		Showmsg('data_error');
	}
	$tidarray = explode("|", $tidarray);
	if (!is_array($tidarray)) {
		Showmsg('data_error');
	}
	$forums = $threadIds = array();
	foreach ($tidarray as $v) {
		if ($v == "") {
			continue;
		}
		if (intval($v) < 0) {
			continue;
		}
		$threadIds[] = $v;
	}
	/**
	$threadManager = L::loadclass('threadmanager', 'forum');
	foreach($forums as $fid=>$threadIds){
		$threadManager->deleteByThreadIds($fid,$threadIds);
	}**/
	$threadService = L::loadclass('threads', 'forum');
	foreach ($forums as $fid=>$_threadIds){
		$threadService->deleteByThreadIds($_threadIds);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
	}

	$delarticle = L::loadClass('DelArticle', 'forum');
	$delarticle->delTopicByTids($threadIds,true);
	echo getLangInfo('other', 'search_manager_success');
	ajax_footer();
}

function checkForHeadTopic($toptype, $fid, $selForums) {
	require_once (R_P . 'require/updateforum.php');
	list($catedbs, $top_1, $top_2, $top_3) = getForumListForHeadTopic($fid);
	$topAll = '';
	if ($toptype == 0) {return true;}
	if ($toptype == 1) {
		$topAll = ',' . implode(',', array_keys((array) $top_1)) . ',';
	} elseif ($toptype == 2) {
		$topAll = ',' . implode(',', array_keys((array) $top_2)) . ',';
	} elseif ($toptype == 3) {
		$topAll = ',' . implode(',', array_keys((array) $top_3)) . ',';
	}
	foreach ((array) $selForums as $key => $value) {
		if (strpos($topAll, ',' . $value . ',') !== false) {return true;}
	}
	return false;
}

function sendMawholeMessages($msgdb) {
	foreach ($msgdb as $key => $val) {
		M::sendNotice(array($val['toUser']), array('title' => $val['title'], 'content' => $val['content']));
	}
}

function copyAttachs($tid, $pid, $content, $newtid, $newpid, $newfid) {
	global $db,$db_ifpwcache;
	$_aids = $array = array();
	$newtid = (int)$newtid;
	$newpid = (int)$newpid;
	$newfid = (int)$newfid;
	$query = $db->query("SELECT * FROM pw_attachs WHERE tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid));
	$count = $ifAttach = 0;
	while ($rt = $db->fetch_array($query)) {
		if (!$count && !$pid && $rt['type']=='img') {
			$ifAttach = true;
			$count++;
		}
		if (strpos($content, "[attachment=" . $rt['aid'] . "]") !== false) {
			$db->update("INSERT INTO pw_attachs
			(fid,uid,tid,pid,did,name,type,size,attachurl,hits,needrvrc,special,ctype,uploadtime,descrip,ifthumb)
			SELECT $newfid,uid,$newtid,$newpid,did,name,type,size,attachurl,hits,needrvrc,special,ctype,uploadtime,descrip,ifthumb
			FROM pw_attachs WHERE aid=" . S::sqlEscape($rt['aid']));
			$array[$rt['aid']] = $db->insert_id();
		} else {
			$_aids[] = $rt['aid'];
		}
	}
	if ($_aids) {
		$db->update("INSERT INTO pw_attachs (fid,uid,tid,pid,did,name,type,size,attachurl,hits,needrvrc,special,ctype,uploadtime,descrip,ifthumb) SELECT $newfid,uid,$newtid,$newpid,did,name,type,size,attachurl,hits,needrvrc,special,ctype,uploadtime,descrip,ifthumb FROM pw_attachs WHERE tid=" . S::sqlEscape($tid) . ' AND pid=' . S::sqlEscape($pid) . ' AND aid IN (' . S::sqlImplode($_aids) . ')');
	}
	//Start elementupdate
	if ((($db_ifpwcache & 512) && $ifAttach)) {
		L::loadClass('elementupdate', '', false);
		$elementAttach = $db->get_one("SELECT * FROM pw_attachs WHERE tid=" . S::sqlEscape($newtid) . " AND pid=" . S::sqlEscape($pid));
		$elementupdate = new ElementUpdate($newfid);
		$elementupdate->newPicUpdate($elementAttach['aid'], $newfid, $newtid, $elementAttach['attachurl'], $elementAttach['ifthumb'], $content);
		$elementupdate->updateSQL();
	}
	//End elementupdate
	return $array;
}

function resetContentByAttach($content, $aidRelation) {
	$searchArr = $replaceArr = array();
	foreach ($aidRelation as $k => $v) {
		$searchArr[]  = "[attachment=$k]";
		$replaceArr[] = "[attachment=$v]";
	}
	return str_replace($searchArr, $replaceArr, $content);
}
?>
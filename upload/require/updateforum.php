<?php
!function_exists('readover') && exit('Forbidden');

function updateforum($fid,$lastinfo='') {//TODO 慢查询
	global $db,$db_fcachenum,$todayTopicNum;
	$fm = $db->get_one("SELECT fup,type,password,allowvisit,f_type FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	if ($fm['type'] != 'category') {
		$subtopics = $subrepliess = 0;
		$query = $db->query("SELECT fid FROM pw_forums WHERE fup=".S::sqlEscape($fid));
		while ($subinfo = $db->fetch_array($query)) {
			@extract($db->get_one("SELECT COUNT(*) AS subtopic,SUM( replies ) AS subreplies FROM pw_threads WHERE fid=".S::sqlEscape($subinfo['fid'])." AND ifcheck='1'"));
			$subtopics   += $subtopic;
			$subrepliess += $subreplies;
			$query2 = $db->query("SELECT fid FROM pw_forums WHERE fup=".S::sqlEscape($subinfo['fid']));
			while ($subinfo2 = $db->fetch_array($query2)) {
				@extract($db->get_one("SELECT COUNT(*) AS subtopic,SUM( replies ) AS subreplies FROM pw_threads WHERE fid=".S::sqlEscape($subinfo2['fid'])." AND ifcheck='1'"));
				$subtopics   += $subtopic;
				$subrepliess += $subreplies;
			}
		}
		$rs       = $db->get_one("SELECT COUNT(*) AS topic,SUM( replies ) AS replies FROM pw_threads WHERE fid=".S::sqlEscape($fid)."AND ifcheck='1' AND topped<=3");
		$topic    = $rs['topic'];
		$replies  = $rs['replies'];
		$article  = $topic + $replies + $subtopics + $subrepliess;
		if (!$lastinfo) {
			$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid)." AND specialsort=0 AND ifcheck=1 AND lastpost>0 ORDER BY lastpost DESC LIMIT 1");
			if ($lt['postdate'] == $lt['lastpost']) {
				$subject = addslashes(substrs($lt['subject'],26));
			} else {
				$subject = 'Re:'.addslashes(substrs($lt['subject'],26));
			}
			$author  = addslashes($lt['lastposter']);
			$lastinfo = $lt['tid'] ? $subject."\t".$author."\t".$lt['lastpost']."\t"."read.php?tid=$lt[tid]&page=e#a" : '' ;
		}
		/**
		$db->update("UPDATE pw_forumdata"
			. " SET ".S::sqlSingle(array(
					'topic'		=> $topic,
					'article'	=> $article,
					'subtopic'	=> $subtopics,
					'lastpost'	=> $lastinfo
				))
			. " WHERE fid=".S::sqlEscape($fid));
		**/
		pwQuery::update('pw_forumdata', 'fid=:fid', array($fid), array(
					'topic'		=> $topic,
					'article'	=> $article,
					'subtopic'	=> $subtopics,
					'lastpost'	=> $lastinfo
				));
		$todayTopicNum && $db->update("UPDATE pw_forumdata SET tpost = tpost+$todayTopicNum WHERE fid	=	" . S::sqlEscape($fid));
		if ($fm['password'] != '' || $fm['allowvisit'] != '' || $fm['f_type'] == 'hidden') {
			$lastinfo = '';
		}
		delfcache($fid,$db_fcachenum);
		if ($fm['type'] == 'sub' || $fm['type'] == 'sub2') {
			updateforum($fm['fup'],$lastinfo);
		}
	}
}

function updateForumCount($fid, $topic, $replies, $tpost = 0) {
	global $db,$db_fcachenum;
	$fm = $db->get_one("SELECT fup,type,password,allowvisit,f_type FROM pw_forums WHERE fid=" . S::sqlEscape($fid));
	if ($fm['type'] == 'category') {
		return false;
	}
	delfcache($fid,$db_fcachenum);
	$topic = intval($topic);
	$article = $topic + intval($replies);
	$tpost = intval($tpost);

	$lastpost = '';
	$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid=" . S::sqlEscape($fid) . " AND specialsort='0' AND ifcheck='1' AND lastpost>0 ORDER BY lastpost DESC LIMIT 1");
	if ($lt) {
		if ($lt['postdate'] == $lt['lastpost']) {
			$subject = substrs($lt['subject'], 26);
		} else {
			$subject = 'Re:'.substrs($lt['subject'],26);
		}
		$lastpost = ",lastpost=" . S::sqlEscape($subject."\t".$lt['lastposter']."\t".$lt['lastpost']."\t"."read.php?tid=$lt[tid]&page=e#a");
	}
	$db->update("UPDATE pw_forumdata SET article=article+'$article',topic=topic+'$topic',tpost=tpost+'$tpost'{$lastpost} WHERE fid=" . S::sqlEscape($fid));
	Perf::gatherInfo('changeForumData', array('fid'=>$fid));
	
	if (($fm['type'] == 'sub' || $fm['type'] == 'sub2') && $fids = getUpFids($fid)) {
		if ($fm['password'] != '' || $fm['allowvisit'] != '' || $fm['f_type'] == 'hidden') {
			$lastpost = '';
		}
		$db->update("UPDATE pw_forumdata SET article=article+'$article',subtopic=subtopic+'$topic',tpost=tpost+'$tpost'{$lastpost} WHERE fid IN(" . S::sqlImplode($fids) . ')');
		Perf::gatherInfo('changeForumData', array('fid'=>$fids));
	}
}

function getUpFids($fid) {
	global $forum;
	//* isset($forum) || include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
	isset($forum) || extract(pwCache::getData(D_P . 'data/bbscache/forum_cache.php', false));
	$upfids = array();
	$fid = $forum[$fid]['fup'];
	while (in_array($forum[$fid]['type'], array('sub2','sub','forum'))) {
		$upfids[] = $fid;
		$fid = $forum[$fid]['fup'];
	}
	return $upfids;
}

function updateshortcut() {
	global $db,$db_shortcutforum;
	PwNewDB();
	$array = array();
	$query = $db->query("SELECT f.fid,f.name FROM pw_forums f LEFT JOIN pw_forumdata fd ON f.fid=fd.fid WHERE f.f_type='forum' AND f.password='' AND f.allowvisit='' ORDER BY fd.tpost DESC LIMIT 6");
	while ($rt = $db->fetch_array($query)) {
		$array[$rt['fid']] = strip_tags($rt['name']);
	}
	empty($array) && $array[0] = '';

	if ($db_shortcutforum <> $array) {
		require_once(R_P.'admin/cache.php');
		setConfig('db_shortcutforum', $array);
		updatecache_c();
	}
	return $array;
}
function delfcache($fid,$num) {
	if ($num < 1) return;
	for ($i=1;$i<=$num;$i++) {
		//* P_unlink(D_P."data/bbscache/fcache_{$fid}_{$i}.php");
		pwCache::deleteData(D_P."data/bbscache/fcache_{$fid}_{$i}.php");
	}
}

function updatetop() {
	global $db,$timestamp;
	if ($timestamp) {
		$db->update("DELETE FROM pw_poststopped WHERE pid = '0' AND fid != '0' AND overtime != '0' AND overtime < $timestamp ");
	}
	$fids = array();
	$query = $db->query("SELECT t.* FROM pw_poststopped t WHERE t.pid = '0' AND t.fid != '0' ");
	$fids = array();
	while ($rt = $db->fetch_array($query)) {
		$fids[$rt['fid']]['topthreads'] .= $fids[$rt['fid']]['topthreads'] ? ','.$rt['tid'] : $rt['tid'];
		if ($rt['uptime'] == $rt['fid']) {
			$fids[$rt['fid']]['top1']++;
		} else {
			$fids[$rt['fid']]['top2']++;
		}
	}
	//update kmd threads
	$kmdService = L::loadClass('kmdservice', 'forum');
	$kmdInfo = $kmdService->getKmdInfosByStatus(KMD_THREAD_STATUS_OK);
	if (is_array($kmdInfo)){
		$kmdTids = array();
		foreach ($kmdInfo as $v){
			if(!$v['tid'] || !$v['fid']) continue;
			$kmdTids[$v['fid']][$v['tid']] = null;
		}
		foreach ($kmdTids as $fid=>$v){
			$fids[$fid]['topthreads'] .= $fids[$fid] ? ','.implode(',', array_keys($v)) : implode(',', array_keys($v));
		}
	}
	
	//* $db->update("UPDATE pw_forumdata SET top1 = '', top2 = '' , topthreads = '' ");
	pwQuery::update('pw_forumdata', null, null, array('top1'=>'', 'top2'=>'', 'topthreads'=>''));
	$_fids = array();
	foreach ($fids as $key => $value) {
		if (is_array($value)) {
			$_fids[] = $key;
			//* $db->update("UPDATE pw_forumdata SET  " . S::sqlSingle($value) . " WHERE fid =  ".S::sqlEscape($key));
			pwQuery::update('pw_forumdata', 'fid=:fid', array($key), $value);
		}
	}
	if (!empty($_fids)) {
		require_once(R_P.'admin/cache.php');
		$_fids = S::sqlEscape($_fids);
		updatecache_forums($_fids);
	}
}

function setForumsTopped($tid,$fid,$topped,$t=0){
	if ($tid && $fid && $topped > 0) {
		global $db;
		list($catedbs,$top_1,$top_2,$top_3) = getForumListForHeadTopic($fid);
		$topAll = array();
		if ($topped == 1) {
			$topAll = array_keys((array)$top_1);
		} elseif ($topped == 2) {
			$topAll = array_keys((array)$top_2);
		} elseif ($topped == 3) {
			$topAll = array_keys((array)$top_3);
		}
		$_topped = array();
		foreach ($topAll as $key => $value) {
			$_topped[] = array('fid'=>$value,
							   'tid'=>$tid,
							   'pid'=>'0',
							   'floor'=>$topped,
							   'uptime'=>$fid,
							   'overtime'=>$t);
		}
		!empty($_topped) && $db->update("REPLACE INTO pw_poststopped (fid,tid,pid,floor,uptime,overtime) values ".S::sqlMulti($_topped));
	}
	return $topAll;
}

function getForumListForHeadTopic($fid){
	global $db,$groupid;
	$sub1 = $sub2 = $forumdb = array();
	$query = $db->query("SELECT fid,t_type,type,fup,name,allowvisit,f_type FROM pw_forums ORDER BY vieworder ASC");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['f_type'] != 'hidden' || ( $rt['f_type'] == 'hidden' && strpos($rt['allowvisit'],','.$groupid.',') !== false )) {
			$rt['fid'] == $fid && $currentForum = $rt;
			if ($rt['type'] == 'category') {
				$catedb[] = $rt;
			} elseif ($rt['type'] == 'forum') {
				$forumdb[$rt['fup']] || $forumdb[$rt['fup']] = array();
				$forumdb[$rt['fup']][] = $rt;
			} elseif ($rt['type'] == 'sub') {
				$sub1[$rt['fup']] || $sub1[$rt['fup']] = array();
				$sub1[$rt['fup']][] = $rt;
			} else {
				$sub2[$rt['fup']] || $sub2[$rt['fup']] = array();
				$sub2[$rt['fup']][] = $rt;
			}
		}
	}
	$top_3 = $top_2 = $top_1 = $catedbs = array();
	foreach ((array)$catedb as $k1 => $v1) {
		$catedbs[$v1['fid']] = array();
		foreach ((array)$forumdb[$v1['fid']] as $k2 => $v2) {
			$catedbs[$v1['fid']][] = $v2['fid'];
			foreach ((array)$sub1[$v2['fid']] as $k3 => $v3) {
				$catedbs[$v1['fid']][] = $v3['fid'];
				foreach ((array)$sub2[$v3['fid']] as $k4 => $v4) {
					$catedbs[$v1['fid']][] = $v4['fid'];
				}
			}
		}
	}
	foreach ((array)$catedb as $k1 => $v1) {
		$v1['name'] = htmlspecialchars(strip_tags($v1['name']),ENT_QUOTES);
		$top_3[$v1['fid']] = "&gt;&gt;".$v1['name'];
		if (in_array($currentForum['fid'],$catedbs[$v1['fid']])) {
			$top_2[$v1['fid']] = "&gt;&gt;".$v1['name'];
		}
		foreach ((array)$forumdb[$v1['fid']] as $k2 => $v2) {
			$v2['name'] = htmlspecialchars(strip_tags($v2['name']),ENT_QUOTES);
			if ($v2['fid'] == $currentForum['fid']) {
				$top_1[$v2['fid']] = "&nbsp;|-".$v2['name'];
			}
			if (in_array($currentForum['fid'],$catedbs[$v1['fid']])) {
				$top_2[$v2['fid']] = "&nbsp;|-".$v2['name'];
			}
			$top_3[$v2['fid']] = "&nbsp;|-".$v2['name'];
			if (!is_array($sub1[$v2['fid']])) {
				continue;
			}
			foreach ((array)$sub1[$v2['fid']] as $k3 => $v3) {
				$_subs = array();
				$v3['name'] = htmlspecialchars(strip_tags($v3['name']),ENT_QUOTES);
				if ($v3['fid'] == $currentForum['fid']) {
					$top_1[$v3['fid']] = "&nbsp;|-".$v3['name'];
				}
				if ($v3['fup'] == $currentForum['fid']) {
					$_subs[] = $v3['fid'];
					$top_1[$v3['fid']] = "&nbsp;&nbsp;&nbsp;|-".$v3['name'];
				}
				$v1['fid'] == $currentForum['fup'] && $top_2[$v3['fid']] = "&nbsp;&nbsp;&nbsp;|-".$v3['name'];
				if (in_array($currentForum['fid'],$catedbs[$v1['fid']])) {
					$top_2[$v3['fid']] = "&nbsp;&nbsp;&nbsp;|-".$v3['name'];
				}
				$top_3[$v3['fid']] = "&nbsp;&nbsp;&nbsp;|-".$v3['name'];
				if (!is_array($sub2[$v3['fid']])) {
					continue;
				}
				foreach ((array)$sub2[$v3['fid']] as $k4 => $v4) {
					$v4['name'] = htmlspecialchars(strip_tags($v4['name']),ENT_QUOTES);
					if ($v4['fid'] == $currentForum['fid']) {
						$top_1[$v4['fid']] = "&nbsp;|-".$v4['name'];
					}
					if (in_array($v4['fup'],$_subs)) {
						$top_1[$v4['fid']] =  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-".$v4['name'];
					}
					if (in_array($currentForum['fid'],$catedbs[$v1['fid']])) {
						$top_2[$v4['fid']] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-".$v4['name'];
					}
					$top_3[$v4['fid']] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-".$v4['name'];
				}
			}
		}
	}
	return array($catedbs,$top_1,$top_2,$top_3);
}

function getattachtype($tid) {
	global $db;
	$type = $db->get_value("SELECT type FROM pw_attachs WHERE tid=".S::sqlEscape($tid,false)."ORDER BY aid LIMIT 1");
	if ($type) {
		switch($type) {
			case 'img': $r=1;break;
			case 'txt': $r=2;break;
			case 'zip': $r=3;break;
			default:$r=0;
		}
	} else {
		$r = false;
	}
	return $r;
}
function delete_tag($tids) {
	global $db;
	if ($tids) {
		$tagdb = array();
		$query = $db->query("SELECT tagid FROM pw_tagdata WHERE tid IN($tids)");
		while (@extract($db->fetch_array($query))) {
			$tagdb[$tagid]++;
		}
		foreach ($tagdb as $tagid=>$num) {
			$db->update("UPDATE pw_tags SET num=num-".S::sqlEscape($num)." WHERE tagid=".S::sqlEscape($tagid));
		}
		$db->update("DELETE FROM pw_tagdata WHERE tid IN($tids)");
	}
}
function delete_att($attachdb,$ifdel = true) {
	require_once(R_P.'require/functions.php');
	$delaids = array();
	foreach ($attachdb as $key => $value) {
		is_numeric($key) && $delaids[] = $key;
		if ($ifdel) {
			pwDelatt($value['attachurl'],$GLOBALS['db_ifftp']);
			$value['ifthumb'] && pwDelatt("thumb/$value[attachurl]",$GLOBALS['db_ifftp']);
		}
	}
	if ($delaids) {
		$pw_attachs = L::loadDB('attachs', 'forum');
		if ($ifdel) {
			$pw_attachs->delete($delaids);
		} else {
			$pw_attachs->updateById($delaids,array('fid'=>0));
		}
	}
	return $delaids;
}
?>
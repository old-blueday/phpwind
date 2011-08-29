<?php
!function_exists('adminmsg') && exit('Forbidden');

require_once (R_P . 'require/forum.php');

require_once (R_P . 'require/updateforum.php');
$basename = "$admin_file?adminjob=thread&admintype=article";

if ($admin_gid == 5) {
	list($allowfid, $forumcache) = GetAllowForum($admin_name);
	$sql = $allowfid ? "fid IN($allowfid)" : '0';
} else {
	//* include pwCache::getPath(D_P . 'data/bbscache/forumcache.php');
	pwCache::getData(D_P . 'data/bbscache/forumcache.php');
	list($hidefid, $hideforum) = GetHiddenForum();
	if ($admin_gid == 3) {
		$forumcache .= $hideforum;
		$sql = '1';
	} else {
		$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
	}
}

if (empty($action)) {
	S::gp(array('fid', 'ifkeep', 'pstarttime', 'pendtime', 'lstarttime', 'lendtime', 'author', 'authorid', 'keyword', 'userip', 'lines', 'searchDisplay', 'direct', 'ttable'));
	S::gp(array('tstart', 'tend', 'hits', 'replies', 'tcounts', 'counts', 'page', 'sphinx', 'sphinxRange'), 'GP', 2);
	ifcheck($ifkeep, 'ifkeep');
	
	$forumcache = str_replace("<option value=\"$fid\">", "<option value=\"$fid\" selected>", $forumcache);
	$pStartString = $pstarttime && is_numeric($pstarttime) ? get_date($pstarttime, 'Y-m-d') : $pstarttime;
	$pEndString = $pendtime && is_numeric($pendtime) ? get_date($pendtime, 'Y-m-d') : $pendtime;
	$lStartString = $lstarttime && is_numeric($lstarttime) ? get_date($lstarttime, 'Y-m-d') : $lstarttime;
	$lEndString = $lendtime && is_numeric($lendtime) ? get_date($lendtime, 'Y-m-d') : $lendtime;
	$tstart = $tstart ? (int) $tstart : '';
	$tend = $tend ? (int) $tend : '';
	$replies = $replies ? (int) $replies : '';
	$hits = $hits ? (int) $hits : '';
	$tcounts = $tcounts ? (int) $tcounts : '';
	$counts = $counts ? (int) $counts : '';
	$threadMsgChecked = $sphinxRange == 2 ? 'checked' : '';
	$threadChecked = !$threadChecked ? 'checked' : '';
	$sphinxChecked = $sphinx ? 'checked' : '';
	$fid = $fid ? (int) $fid : '-1';
	$lines = intval($lines) < 1 ? $db_perpage : (int) $lines;
	$searchDisplay === null && $searchDisplay = 'none';
	$page = max(intval($page), 1);
	
	$threadTableList = array();
	if ($db_tlist) {
		foreach ($db_tlist as $key => $val) {
			$name = !empty($val['2']) ? $val['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs' . $key);
			$threadTableList[$key] = $name;
		}
	}
	$threadTableSelections = $threadTableList ? formSelect('ttable', $ttable, $threadTableList, 'class="select_wa"') : '';
	
	if ($fid == '-1' && !$pstarttime && !$pendtime && !$tcounts && !$counts && !$lstarttime && !$lendtime && !$hits && !$replies && !$author && !$authorid && !$keyword && !$userip && !$tstart && !$tend) {
		$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		include PrintEot('thread');
		exit();
	}
	$pstarttime = $pstarttime && !is_numeric($pstarttime) ? PwStrtoTime($pstarttime) : $pstarttime;
	$pendtime = $pendtime && !is_numeric($pendtime) ? PwStrtoTime($pendtime) : $pendtime;
	$lstarttime = $lstarttime && !is_numeric($lstarttime) ? PwStrtoTime($lstarttime) : $lstarttime;
	$lendtime = $lendtime && !is_numeric($lendtime) ? PwStrtoTime($lendtime) : $lendtime;
	$pendtime && $sqlPendtime = $pendtime + 86400;
	$lendtime && $sqlLendtime = $lendtime + 86400;
	if (is_numeric($fid) && $fid > 0) {
		pwCache::getData(S::escapePath(D_P . "data/forums/fid_{$fid}.php"));
		if(is_array($foruminfo) && isset($foruminfo['type']) && $foruminfo['type'] == 'category'){
			pwCache::getData(D_P . "data/bbscache/forumlist_cache.php");
			if(is_array($pwForumList[$fid]['child'])){
				$fids = array_keys($pwForumList[$fid]['child']);
				isset($pwForumAllList[$fid]['child']) && $fids = array_merge($fids, array_keys($pwForumAllList[$fid]['child']));
			}
		}
		$sql .= count($fids) > 0 ? " AND t.fid IN(" . S::sqlImplode($fids) . ')' : " AND t.fid=" . S::sqlEscape($fid);
	}
	$ifkeep && $sql .= " AND t.topped=0 AND t.digest=0";
	$pstarttime && $sql .= " AND t.postdate>" . S::sqlEscape($pstarttime);
	$sqlPendtime && $sql .= " AND t.postdate<" . S::sqlEscape($sqlPendtime);
	$lstarttime && $sql .= " AND t.lastpost>" . S::sqlEscape($lstarttime);
	$sqlLendtime && $sql .= " AND t.lastpost<" . S::sqlEscape($sqlLendtime);
	$tstart && $sql .= " AND t.tid>" . S::sqlEscape($tstart);
	$tend && $sql .= " AND t.tid<" . S::sqlEscape($tend);
	$hits && $sql .= " AND t.hits<" . S::sqlEscape($hits);
	$replies && $sql .= " AND t.replies<" . S::sqlEscape($replies);
	$tcounts && $sql .= " AND char_length(tm.content)>" . S::sqlEscape($tcounts);
	$counts && $sql .= " AND char_length(tm.content)<" . S::sqlEscape($counts);
	if ($author || $authorid) {
		$authorarray = array_unique(explode(",", $authorid ? $authorid : $author));
		if (!S::inArray('*', $authorarray)) {
			$tmpAuthorid = $authorids = array();
			foreach ($authorarray as $value) {
				if($authorid){
					intval($value) > 0 && $tmpAuthorid[] = (int) $value;
					continue;
				}
				$value = str_replace('*','%',$value);
				$authorwhere .= " OR username LIKE " . S::sqlEscape($value, false);
			}
			if ($tmpAuthorid || $authorwhere) {
				$authorwhere = $authorid ? ' uid IN (' . S::sqlImplode($tmpAuthorid) . ')' : substr_replace($authorwhere, '', 0, 3);
				$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt = $db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
			}
			!$authorids && adminmsg('author_nofind');
			$sql .= " AND t.authorid IN(" . S::sqlImplode($authorids) . ")";
		}
	}
	if ($keyword) {
		$keyword = trim($keyword);
		$keywordarray = explode(",", $keyword);
		foreach ($keywordarray as $value) {
			$value = str_replace('*', '%', $value);
			$keywhere .= "OR tm.content LIKE " . S::sqlEscape("%$value%") . "OR t.subject LIKE " . S::sqlEscape("%$value%");
		}
		$keywhere = substr_replace($keywhere, "", 0, 3);
		$sql .= " AND ($keywhere) ";
	}
	if ($userip) {
		$userip = str_replace('*', '%', $userip);
		$userip != '%' && $sql .= " AND (tm.userip LIKE " . S::sqlEscape($userip) . ') ';
	}
	$sql .= " AND tm.tid!=''";
	$db_merge_tmsgs && $pw_tmsgs = 'pw_merge_tmsgs';
	if (!$db_merge_tmsgs && $ttable == 'auto') {
		$rt = $db->get_one("SELECT MAX(tid) AS mtid FROM pw_threads");
		$pw_tmsgs = GetTtable($rt['mtid']);
	} elseif (!$db_merge_tmsgs) {
		$pw_tmsgs = $ttable > 0 ? 'pw_tmsgs' . intval($ttable) : 'pw_tmsgs';
	}
	if ($sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword, '*') === false) {
		$keyword = trim($keyword);
		$forumIds = ( $fid > 0 ) ? array($fid) : array();
		$sphinxServer = L::loadclass('searcher','search');
		$result = $sphinxServer->manageThreads($keyword, $sphinxRange, $authorarray, $pstarttime, $sqlPendtime, $forumIds, $page, $lines);
		$result === false && adminmsg('search_keyword_empty');
		$count = $result[0];
		$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE tm.tid IN (" . $result[1] . ") ORDER BY tid DESC");
	} else {
		$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql LIMIT 1");
		$count = $rs['count'];
		$start = ($page - 1) * $lines;
		$limit = S::sqlLimit($start, $lines);
		$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql ORDER BY tid DESC $limit");
	}
	
	$numofpage = ceil($count / $lines);
	$page = validatePage($page, $numofpage);
	$pages = pagerforjs($count, $page, $numofpage, "onclick=\"manageclass.superdel(this,'superdel_tpc','')\"");
	
	$delid = $topicdb = array();
	pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
	while ($topic = $db->fetch_array($query)) {
		if ($direct) {
			$delid[$topic['tid']] = $topic['fid'];
			continue;
		}
		$topic['forumname'] = $forum[$topic['fid']]['name'];
		$topic['postdate'] = get_date($topic['postdate']);
		$topic['lastpost'] = get_date($topic['lastpost']);
		$topicdb[] = $topic;
	}
	$userip = str_replace('%', '*', $userip);
	include PrintEot('thread');
	exit();
} elseif ($action == 'deltpc') {
	
	S::gp(array(
		'fid',
		'ifkeep',
		'pstarttime',
		'pendtime',
		'lstarttime',
		'lendtime',
		'author',
		'authorid',
		'keyword',
		'userip',
		'lines',
		'step',
		'direct',
		'fid',
		'ttable'
	));
	S::gp(array(
		'tstart',
		'tend',
		'hits',
		'replies',
		'tcounts',
		'counts',
		'page',
		'sphinx',
		'sphinxRange',
		'delcredit'
	), 'GP', 2);

	if ($step == 2 || $direct) {

		!$direct && S::gp(array('delid'), 'P');
		!$delid && adminmsg('operate_error');
		$delids = array();
		foreach ($delid as $key => $value) {
			is_numeric($key) && $delids[] = $key;
		}
		$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
		$delarticle->delTopicByTids($delids, false, $delcredit);

		adminmsg('operate_success', "$basename&fid=$fid&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$hits&replies=$replies&author=" . rawurlencode($author) . "&authorid=$authorid&keyword=" . rawurlencode($keyword) . "&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&page=$page");
	
	}
	
} elseif ($action == 'delrpl') {
	
	S::gp(array(
		'ptable'
	));
	is_numeric($ptable) && $dbptable = $ptable;
	$pw_posts = GetPtable($dbptable);
	
	S::gp(array(
		'fid',
		'tid',
		'author',
		'keyword',
		'tcounts',
		'counts',
		'userip',
		'nums',
		'step',
		'direct'
	));
	S::gp(array(
		'pstart',
		'pend',
		'page',
		'sphinx',
		'sphinx_range',
		'delcredit'
	), 'GP', 2);
	
	if ($step == 2 || $direct) {

		//if (!$direct) {
			S::gp(array('delid'), 'P');
		//}
		!$delid && adminmsg('operate_error');
				
		$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
		$delarticle->delReplyByPids($delid, false, $delcredit);

		//* P_unlink(D_P . 'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P . 'data/bbscache/c_cache.php');
		adminmsg('operate_success', "$basename&action=replylist&fid=$fid&tid=$tid&pstart=$pstart&pend=$pend&author=" . rawurlencode($author) . "&authorid=$authorid&keyword=" . rawurlencode($keyword) . "&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&page=$page");
	}
	
} elseif ('replylist' == $action) {
	S::gp(array('fid', 'tid', 'author', 'authorid', 'keyword', 'tcounts', 'counts', 'userip', 'nums', 'searchDisplay', 'ptable', 'direct'));
	S::gp(array('pstart', 'pend', 'page', 'sphinx', 'sphinx_range'), 'GP', 2);

	$forumcache = str_replace("<option value=\"$fid\">", "<option value=\"$fid\" selected>", $forumcache);
	$pstart = $pstart ? (int) $pstart : '';
	$pend = $pend ? (int) $pend : '';
	$tcounts = $tcounts ? (int) $tcounts : '';
	$counts = $counts ? (int) $counts : '';
	$fid = $fid ? (int) $fid : '-1';
	$sphinxChecked = $sphinx ? 'checked' : '';
	$nums = intval($nums) && intval($nums) > 0 ? (int) $nums : $db_perpage;
	$searchDisplay === null && $searchDisplay = 'none';
	
	$postTableList = array();
	if ($db_plist) {
		foreach ($db_plist as $key => $val) {
			$name = $val ? $val : ($key != 0 ? getLangInfo('other', 'posttable') . $key : getLangInfo('other', 'posttable'));
			$postTableList[$key] = $name;
		}
	}
	$postTableSelections = $postTableList ? formSelect('ptable', $ptable, $postTableList, 'class="select_wa"') : '';

	if (!$counts && !$tcounts && $fid == '-1' && !$keyword && !$tid && !$author && !$authorid && !$userip && !$pstart && !$pend) {
		$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		include PrintEot('thread');
		exit();
	}
	is_numeric($fid) && $fid > 0 && $sql .= " AND fid=" . S::sqlEscape($fid);
	if ($tid) {
		$tids = array();
		$tid_array = explode(",", $tid);
		foreach ($tid_array as $value) {
			is_numeric($value) && $tids[] = (int) $value;
		}
		$tids && $sql .= " AND tid IN(" . S::sqlImplode($tids) . ")";
	}
	$pstart && $sql .= " AND pid>" . S::sqlEscape($pstart);
	$pend && $sql .= " AND pid<" . S::sqlEscape($pend);
	$forceIndex = $skipAuthorCondition = '';
	if ($author || $authorid) {
		$authorarray = array_unique(explode(",", $authorid ? $authorid : $author));
		$tmpAuthorid = $authorids = array();
		if (!S::inArray('*', $authorarray)) {
			foreach ($authorarray as $value) {
				if($authorid){
					intval($value) > 0 && $tmpAuthorid[] = (int) $value;
					continue;
				}
				$value = str_replace('*','%',$value);
				$authorwhere .= " OR username LIKE " . S::sqlEscape($value, false);
			}
			if ($tmpAuthorid || $authorwhere) {
				$authorwhere = $authorid ? ' uid IN (' . S::sqlImplode($tmpAuthorid) . ')' : substr_replace($authorwhere, "", 0, 3);
				$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt = $db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
			}
			!$authorids && adminmsg('author_nofind');
			$sql .= " AND authorid IN(" . S::sqlImplode($authorids) . ")";
		}
	}
	$forceIndex = " FORCE INDEX(".getForceIndex('idx_postdate').") ";
	if ($keyword) {
		$keyword = trim($keyword);
		$keywordarray = explode(",", $keyword);
		foreach ($keywordarray as $value) {
			$value = str_replace('*', '%', $value);
			$keywhere .= " OR content LIKE " . S::sqlEscape("%$value%");
		}
		$keywhere = substr_replace($keywhere, "", 0, 3);
		$sql .= " AND ($keywhere) ";
	}
	if ($userip) {
		$userip = str_replace('*', '%', $userip);
		$userip != '%' && $sql .= " AND (userip LIKE " . S::sqlEscape($userip) . ")";
	}
	$tcounts && $sql .= " AND char_length(content)>" . S::sqlEscape($tcounts);
	$counts && $sql .= " AND char_length(content)<" . S::sqlEscape($counts);
	(!isset($ptable)) && $ptable = $db_ptable;
	$pw_posts = !$db_merge_posts ? GetPtable($ptable) : 'pw_merge_posts';
	
	if ($sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword, '*') === false) {
		$forumIds = ($fid > 0) ? array($fid) : array();
		$sphinxServer = L::loadclass('searcher','search');
		$result = $sphinxServer->manageThreads($keyword,3,$authorarray,$pstart,$pend,$forumIds,$page,$nums);
		$result === false && adminmsg('search_keyword_empty');
		$count = $result[0];
		$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts WHERE pid IN (" . $result[1] . ")  ORDER BY postdate DESC ");
	} else {
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE $sql");
		$count = $rt['sum'];
		$page < 1 && $page = 1;
		$limit = S::sqlLimit(($page - 1) * $nums, $nums);
		$sql .= ' ORDER BY postdate DESC ';
		$sql .= $direct ? " LIMIT $nums" : $limit;
		$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts $forceIndex WHERE $sql");
	}
	$pages = pagerforjs($count, $page, ceil($count / $nums), "onclick=\"manageclass.superdel(this,'superdel_rpl','')\"");
	$delid = $postdb = array();
	while ($post = $db->fetch_array($query)) {
		if ($direct) {
			$delid[$post['pid']] = $post['fid'] . '_' . $post['tid'];
			continue;
		} 
		$post['forumname'] = $forum[$post['fid']]['name'];
		$post['postdate'] = get_date($post['postdate']);
		$post['content'] = substrs($post['content'], 30);
		$postdb[] = $post;
	}
	$userip = str_replace('%', '*', $userip);
	include PrintEot('thread');
	exit();

} elseif ($action == 'view') {
	
	S::gp(array(
		'tid',
		'pid'
	));
	
	$pw_posts = GetPtable('N', $tid);
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . 'AND pid<' . S::sqlEscape($pid));
	$page = ceil(($rt['sum'] + 1.5) / $db_readperpage);
	
	ObHeader("read.php?tid=$tid&page=$page&ds=1#$pid");
}
//delete pcid
function _delPcTopic($pcdb) {
	global $db;
	foreach ($pcdb as $key => $value) {
		$pcids = S::sqlImplode($value);
		$key = $key > 20 ? $key - 20 : 0;
		$pcvaluetable = GetPcatetable($key);
		$db->update("DELETE FROM $pcvaluetable WHERE tid IN($pcids)");
	}
}


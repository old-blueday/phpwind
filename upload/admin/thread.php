<?php
!function_exists('adminmsg') && exit('Forbidden');

require_once (R_P . 'require/forum.php');

require_once (R_P . 'require/updateforum.php');
$basename = "$admin_file?adminjob=thread";

if ($admin_gid == 5) {
	list($allowfid, $forumcache) = GetAllowForum($admin_name);
	$sql = $allowfid ? "fid IN($allowfid)" : '0';
} else {
	include (D_P . 'data/bbscache/forumcache.php');
	list($hidefid, $hideforum) = GetHiddenForum();
	if ($admin_gid == 3) {
		$forumcache .= $hideforum;
		$sql = '1';
	} else {
		$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
	}
}

if (empty($action)) {
	InitGP(array(
		'ttable'
	));
	if ($ttable == 'auto') {
		$rt = $db->get_one("SELECT MAX(tid) AS mtid FROM pw_threads");
		$pw_tmsgs = GetTtable($rt['mtid']);
	} else {
		$pw_tmsgs = $ttable > 0 ? 'pw_tmsgs' . $ttable : 'pw_tmsgs';
	}
	InitGP(array(
		'fid',
		'ifkeep',
		'ttable',
		'pstarttime',
		'pendtime',
		'lstarttime',
		'lendtime',
		'author',
		'keyword',
		'userip',
		'lines',
		'searchDisplay'
	));
	InitGP(array(
		'tstart',
		'tend',
		'hits',
		'replies',
		'tcounts',
		'counts',
		'page',
		'sphinx',
		'sphinxRange'
	), 'GP', 2);
	
	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
	$pStartString = $pstarttime && is_numeric($pstarttime) ? get_date($pstarttime, 'Y-m-d') : $pstarttime;
	$pEndString = $pendtime && is_numeric($pendtime) ? get_date($pendtime, 'Y-m-d') : $pendtime;
	$lStartString = $lstarttime && is_numeric($lstarttime) ? get_date($lstarttime, 'Y-m-d') : $lstarttime;
	$lEndString = $lendtime && is_numeric($lendtime) ? get_date($lendtime, 'Y-m-d') : $lendtime;
	$tstart = $tstart ? $tstart : '';
	$tend = $tend ? $tend : '';
	$replies = $replies ? $replies : '';
	$hits = $hits ? $hits : '';
	$tcounts = $tcounts ? $tcounts : '';
	$counts = $counts ? $counts : '';
	$threadMsgChecked = $sphinxRange == 2 ? 'checked' : '';
	$threadChecked = !$threadChecked ? 'checked' : '';
	$sphinxChecked = $sphinx ? 'checked' : '';
	$fid = $fid ? $fid : '-1';
	if (!is_numeric($lines)) $lines = $db_perpage;
	null === $searchDisplay && $searchDisplay = 'none';
	
	$threadTableList = array();
	if ($db_tlist) {
		foreach ($db_tlist as $key => $val) {
			$name = !empty($val['2']) ? $val['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs' . $key);
			$threadTableList[$key] = $name;
		}
	}
	$threadTableSelections = $threadTableList ? formSelect('ttable', $ttable, $threadTableList, 'class="select_wa"') : '';
	
	if ($fid == '-1' && !$pstarttime && !$pendtime && !$tcounts && !$counts && !$lstarttime && !$lendtime && !$hits && !$replies && !$author && !$keyword && !$userip && !$tstart && !$tend) {
		$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
	} else {
		
		$pstarttime = $pstarttime && !is_numeric($pstarttime) ? PwStrtoTime($pstarttime) : $pstarttime;
		$pendtime = $pendtime && !is_numeric($pendtime) ? PwStrtoTime($pendtime) : $pendtime;
		$lstarttime = $lstarttime && !is_numeric($lstarttime) ? PwStrtoTime($lstarttime) : $lstarttime;
		$lendtime = $lendtime && !is_numeric($lendtime) ? PwStrtoTime($lendtime) : $lendtime;
		
		if (is_numeric($fid) && $fid > 0) {
			$sql .= " AND t.fid=" . pwEscape($fid);
		}
		if ($ifkeep) {
			$sql .= " AND t.topped=0 AND t.digest=0";
		}
		if ($pstarttime) {
			$sql .= " AND t.postdate>" . pwEscape($pstarttime);
		}
		if ($pendtime) {
			$sql .= " AND t.postdate<" . pwEscape($pendtime);
		}
		if ($lstarttime) {
			$sql .= " AND t.lastpost>" . pwEscape($lstarttime);
		}
		if ($lendtime) {
			$sql .= " AND t.lastpost<" . pwEscape($lendtime);
		}
		if ($tstart) {
			$sql .= " AND t.tid>" . pwEscape($tstart);
		}
		if ($tend) {
			$sql .= " AND t.tid<" . pwEscape($tend);
		}
		$hits && $sql .= " AND t.hits<" . pwEscape($hits);
		$replies && $sql .= " AND t.replies<" . pwEscape($replies);
		if ($tcounts) {
			$sql .= " AND char_length(tm.content)>" . pwEscape($tcounts);
		} elseif ($counts) {
			$sql .= " AND char_length(tm.content)<" . pwEscape($counts);
		}
		if ($author) {
			$authorarray = explode(",", $author);
			foreach ($authorarray as $value) {
				$value = str_replace('*', '%', $value);
				$authorwhere .= " OR username LIKE " . pwEscape($value, false);
			}
			$authorwhere = substr_replace($authorwhere, "", 0, 3);
			$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
			while ($rt = $db->fetch_array($query)) {
				$authorids[] = $rt['uid'];
			}
			if ($authorids) {
				$sql .= " AND t.authorid IN(" . pwImplode($authorids) . ")";
			} else {
				adminmsg('author_nofind');
			}
		}
		if ($keyword) {
			$keyword = trim($keyword);
			$keywordarray = explode(",", $keyword);
			foreach ($keywordarray as $value) {
				$value = str_replace('*', '%', $value);
				$keywhere .= 'OR';
				$keywhere .= " tm.content LIKE " . pwEscape("%$value%") . "OR t.subject LIKE " . pwEscape("%$value%");
			}
			$keywhere = substr_replace($keywhere, "", 0, 3);
			$sql .= " AND ($keywhere) ";
		}
		if ($userip) {
			$userip = str_replace('*', '%', $userip);
			$sql .= " AND (tm.userip LIKE " . pwEscape($userip) . ') ';
			$ip_add = ',tm.userip';
		}
		$sql .= " AND tm.tid!=''";
		
		if ($sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword, '*') === false) {
			$keyword = trim($keyword);
			$forumIds = ( $fid > 0 ) ? array($fid) : array();
			$sphinxServer = L::loadclass('searcher','search');
			$result = $sphinxServer->manageThreads($keyword,$sphinxRange,$authorarray,$pstarttime,$pendtime,$forumIds,$page,$lines);
			if ($result === false) {
				adminmsg('search_keyword_empty');
			}
			$count = $result[0];
			$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE tm.tid in (" . $result[1] . ") ORDER BY tid DESC");
		} else {
			$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql LIMIT 1");
			$count = $rs['count'];
			$start = ($page - 1) * $lines;
			$limit = pwLimit($start, $lines);
			$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql ORDER BY tid DESC $limit");
		}
		$page < 1 && $page = 1;
		$numofpage = ceil($count / $lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		//$pages = numofpage($count, $page, $numofpage, "$basename&fid=$fid&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$hits&replies=$replies&author=" . rawurlencode($author) . "&keyword=" . rawurlencode($keyword) . "&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&sphinx=$sphinx&sphinxRange=$sphinxRange&searchDisplay=$searchDisplay&");
		$pages = pagerforjs($count, $page, $numofpage, "onclick=\"manageclass.superdel(this,'superdel_tpc','')\"");
		$delid = $topicdb = array();
		include (D_P . 'data/bbscache/forum_cache.php');
		while ($topic = $db->fetch_array($query)) {
			if ($_POST['direct']) {
				$delid[$topic['tid']] = $topic['fid'];
			} else {
				$topic['forumname'] = $forum[$topic['fid']]['name'];
				$topic['postdate'] = get_date($topic['postdate']);
				$topic['lastpost'] = get_date($topic['lastpost']);
				$topicdb[] = $topic;
			}
		}
		//if (!$_POST['direct']) {
		//	include PrintEot('thread');
		//	exit();
		//}
		
		$userip = str_replace('%', '*', $userip);
	}
	
	include PrintEot('thread');
	exit();
} elseif ($action == 'deltpc') {
	
	InitGP(array(
		'fid',
		'ifkeep',
		'pstarttime',
		'pendtime',
		'lstarttime',
		'lendtime',
		'author',
		'keyword',
		'userip',
		'lines',
		'step',
		'direct',
		'fid',
		'ttable'
	));
	InitGP(array(
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

		!$direct && InitGP(array('delid'), 'P');
		!$delid && adminmsg('operate_error');
		$delids = array();
		foreach ($delid as $key => $value) {
			is_numeric($key) && $delids[] = $key;
		}
		$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
		$delarticle->delTopicByTids($delids, false, $delcredit);

		adminmsg('operate_success', "$basename&fid=$fid&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$hits&replies=$replies&author=" . rawurlencode($author) . "&keyword=" . rawurlencode($keyword) . "&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&page=$page");
	
	}
	
} elseif ($action == 'delrpl') {
	
	InitGP(array(
		'ptable'
	));
	is_numeric($ptable) && $dbptable = $ptable;
	$pw_posts = GetPtable($dbptable);
	
	InitGP(array(
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
	InitGP(array(
		'pstart',
		'pend',
		'page',
		'sphinx',
		'sphinx_range',
		'delcredit'
	), 'GP', 2);
	
	if ($step == 2 || $direct) {

		//if (!$direct) {
			InitGP(array('delid'), 'P');
		//}
		!$delid && adminmsg('operate_error');
				
		$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
		$delarticle->delReplyByPids($delid, false, $delcredit);

		P_unlink(D_P . 'data/bbscache/c_cache.php');
		adminmsg('operate_success', "$basename&action=replylist&fid=$fid&tid=$tid&pstart=$pstart&pend=$pend&author=" . rawurlencode($author) . "&keyword=" . rawurlencode($keyword) . "&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&page=$page");
	}
	
} elseif ('replylist' == $action) {
	InitGP(array(
		'ptable'
	));
	(!isset($ptable)) && $ptable = $db_ptable;
	//is_numeric($ptable) && $dbptable = $ptable;
	$pw_posts = GetPtable($ptable);
	
	InitGP(array(
		'fid',
		'tid',
		'author',
		'keyword',
		'tcounts',
		'counts',
		'userip',
		'nums',
		'searchDisplay'
	));
	InitGP(array(
		'pstart',
		'pend',
		'page',
		'sphinx',
		'sphinx_range'
	), 'GP', 2);

	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
	$pstart = $pstart ? $pstart : '';
	$pend = $pend ? $pend : '';
	$tcounts = $tcounts ? $tcounts : '';
	$counts = $counts ? $counts : '';
	$fid = $fid ? $fid : '-1';
	$sphinxChecked = $sphinx ? 'checked' : '';
	$nums = is_numeric($nums) ? $nums : $db_perpage;
	null === $searchDisplay && $searchDisplay = 'none';
	
	$postTableList = array();
	if ($db_plist) {
		foreach ($db_plist as $key => $val) {
			$name = $val ? $val : ($key != 0 ? getLangInfo('other', 'posttable') . $key : getLangInfo('other', 'posttable'));
			$postTableList[$key] = $name;
		}
	}
	$postTableSelections = $postTableList ? formSelect('ptable', $ptable, $postTableList, 'class="select_wa"') : '';

	if (!$counts && !$tcounts && $fid == '-1' && !$keyword && !$tid && !$author && !$userip && !$pstart && !$pend) {
		$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
	} else {
		if (is_numeric($fid) && $fid > 0) {
			$sql .= " AND fid=" . pwEscape($fid);
		}
		if ($tid) {
			$tids = array();
			$tid_array = explode(",", $tid);
			foreach ($tid_array as $value) {
				if (is_numeric($value)) {
					$tids[] = $value;
				}
			}
			$tids && $sql .= " AND tid IN(" . pwImplode($tids) . ")";
		}
		if ($pstart) {
			$sql .= " AND pid>" . pwEscape($pstart);
		}
		if ($pend) {
			$sql .= " AND pid<" . pwEscape($pend);
		}
		$forceIndex = '';
		if ($author) {
			$authorarray = explode(",", $author);
			foreach ($authorarray as $value) {
				$value = addslashes(str_replace('*', '%', $value));
				$authorwhere .= " OR username LIKE " . pwEscape($value);
			}
			$authorwhere = substr_replace($authorwhere, "", 0, 3);
			$authorids = array();
			$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
			while ($rt = $db->fetch_array($query)) {
				$authorids[] = $rt['uid'];
			}
			if ($authorids) {
				$sql .= " AND authorid IN(" . pwImplode($authorids) . ")";
				$forceIndex = " FORCE INDEX(postdate) ";
			} else {
				adminmsg('author_nofind');
			}
		}
		if ($keyword) {
			$keyword = trim($keyword);
			$keywordarray = explode(",", $keyword);
			foreach ($keywordarray as $value) {
				$value = str_replace('*', '%', $value);
				$keywhere .= " OR content LIKE " . pwEscape("%$value%");
			}
			$keywhere = substr_replace($keywhere, "", 0, 3);
			$sql .= " AND ($keywhere) ";
		}
		if ($userip) {
			$userip = str_replace('*', '%', $userip);
			$sql .= " AND (userip LIKE " . pwEscape($userip) . ")";
		}
		
		if ($tcounts) {
			$sql .= " AND char_length(content)>" . pwEscape($tcounts);
		} elseif ($counts) {
			$sql .= " AND char_length(content)<" . pwEscape($counts);
		}
		if ($sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword, '*') === false) {
			$forumIds = ($fid > 0) ? array($fid) : array();
			$sphinxServer = L::loadclass('searcher','search');
			$result = $sphinxServer->manageThreads($keyword,3,$authorarray,$pstart,$pend,$forumIds,$page,$nums);
			if ($result === false) {
				adminmsg('search_keyword_empty');
			}
			$count = $result[0];
			$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts WHERE pid in (" . $result[1] . ")  ORDER BY postdate DESC ");
		} else {
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE $sql");
			$count = $rt['sum'];
			$page < 1 && $page = 1;
			$limit = pwLimit(($page - 1) * $nums, $nums);
			$sql .= ' ORDER BY postdate DESC ';
			$sql .= $_POST['direct'] ? " LIMIT $nums" : $limit;
			$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts $forceIndex WHERE $sql");
		}
		//$pages = numofpage($count, $page, ceil($count / $nums), "$basename&action=replylist&fid=$fid&tid=$tid&pstart=$pstart&pend=$pend&author=" . rawurlencode($author) . "&keyword=" . rawurlencode($keyword) . "&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&sphinx=$sphinx&searchDisplay=$searchDisplay&");
		$pages = pagerforjs($count, $page, ceil($count / $nums), "onclick=\"manageclass.superdel(this,'superdel_rpl','')\"");
		$delid = $postdb = array();
		while ($post = $db->fetch_array($query)) {
			if ($_POST['direct']) {
				$delid[$post['pid']] = $post['fid'] . '_' . $post['tid'];
			} else {
				//$post['delid'] = $post['fid'] . '_' . $post['tid'];
				$post['forumname'] = $forum[$post['fid']]['name'];
				$post['postdate'] = get_date($post['postdate']);
				$post['content'] = substrs($post['content'], 30);
				$postdb[] = $post;
			}
		}
		//if (!$_POST['direct']) {
		//	include PrintEot('thread');
		//	exit();
		//}
		
		$userip = str_replace('%', '*', $userip);
	}
	include PrintEot('thread');
	exit();

} elseif ($action == 'view') {
	
	InitGP(array(
		'tid',
		'pid'
	));
	
	$pw_posts = GetPtable('N', $tid);
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE tid=" . pwEscape($tid) . 'AND pid<' . pwEscape($pid));
	$page = ceil(($rt['sum'] + 1.5) / $db_readperpage);
	
	ObHeader("read.php?tid=$tid&page=$page#$pid");
}
//delete pcid
function _delPcTopic($pcdb) {
	global $db;
	foreach ($pcdb as $key => $value) {
		$pcids = pwImplode($value);
		$key = $key > 20 ? $key - 20 : 0;
		$pcvaluetable = GetPcatetable($key);
		$db->update("DELETE FROM $pcvaluetable WHERE tid IN($pcids)");
	}
}


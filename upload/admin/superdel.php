<?php
!function_exists('adminmsg') && exit('Forbidden');

require_once(R_P.'require/forum.php');

if ($admintype == 'article') {

	require_once(R_P.'require/updateforum.php');
	$basename = "$admin_file?adminjob=superdel&admintype=article";

	if ($admin_gid == 5) {
		list($allowfid,$forumcache) = GetAllowForum($admin_name);
		$sql = $allowfid ? "fid IN($allowfid)" : '0';
	} else {
		//* include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
		pwCache::getData(D_P.'data/bbscache/forumcache.php');
		list($hidefid,$hideforum) = GetHiddenForum();
		if ($admin_gid == 3) {
			$forumcache .= $hideforum;
			$sql = '1';
		} else {
			$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
		}
	}

	if (empty($action)) {
		$p_table = $t_table = '';
		if ($db_plist && count($db_plist)>1) {
			foreach ($db_plist as $key => $val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$p_table .= "<option value=\"$key\">".$name."</option>";
			}
			$p_table = str_replace("<option value=\"$db_ptable\">","<option value=\"$db_ptable\" selected>",$p_table);
		}
		if ($db_tlist) {
			$tlistdb = $db_tlist;
			foreach ($tlistdb as $key=>$val) {
				$name = !empty($val['2']) ? $val['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs'.$key);
				$t_table .= "<option value=\"$key\">$name</option>";
			}
		}
		include PrintEot('superdel');exit;

	} elseif ($action == 'deltpc') {

		S::gp(array('ttable'));
		if ($ttable == 'auto') {
			$rt = $db->get_one("SELECT MAX(tid) AS mtid FROM pw_threads");
			$pw_tmsgs = GetTtable($rt['mtid']);
		} else {
			$pw_tmsgs = $ttable>0 ? 'pw_tmsgs'.intval($ttable) : 'pw_tmsgs';
		}
		S::gp(array('fid','ifkeep','pstarttime','pendtime','lstarttime','lendtime','author','authorid','keyword','userip','lines','direct'));
		S::gp(array('tstart','tend','hits','replies','tcounts','counts','page','sphinx','sphinxRange'),'GP',2);
		if (empty($_POST['step'])) {
			if($page=="0"){
			$_POST['pstarttime'] && $pstarttime = PwStrtoTime($pstarttime);
			$_POST['pendtime']&&($pendtime)   &&  $pendtime   = PwStrtoTime($pendtime);
			$_POST['lstarttime'] && $lstarttime = PwStrtoTime($lstarttime);
			$_POST['lendtime']   && $lendtime   = PwStrtoTime($lendtime);
			}
			if ($fid=='-1' && !$pstarttime && !$pendtime && !$tcounts && !$counts && !$lstarttime && !$lendtime && !$hits && !$replies && !$author && !$authorid && !$keyword && !$userip && !$tstart && !$tend) {
				adminmsg('noenough_condition');
			}
			if (is_numeric($fid) && $fid > 0) {
				$sql .= " AND t.fid=".S::sqlEscape($fid);
			}
			if ($ifkeep) {
				$sql.=" AND t.topped=0 AND t.digest=0";
			}
			if ($pstarttime) {
				$sql.=" AND t.postdate>".S::sqlEscape($pstarttime);
			}
			if ($pendtime) {
				$sql.=" AND t.postdate<".S::sqlEscape($pendtime);
			}
			if ($lstarttime) {
				$sql.=" AND t.lastpost>".S::sqlEscape($lstarttime);
			}
			if ($lendtime) {
				$sql.=" AND t.lastpost<".S::sqlEscape($lendtime);
			}
			if ($tstart) {
				$sql.=" AND t.tid>".S::sqlEscape($tstart);
			}
			if ($tend) {
				$sql.=" AND t.tid<".S::sqlEscape($tend);
			}
			$hits    && $sql.=" AND t.hits<".S::sqlEscape($hits);
			$replies && $sql.=" AND t.replies<".S::sqlEscape($replies);
			if ($tcounts) {
				$sql.=" AND char_length(tm.content)>".S::sqlEscape($tcounts);
			} elseif ($counts) {
				$sql.=" AND char_length(tm.content)<".S::sqlEscape($counts);
			}
			if ($author || $authorid) {
				$authorarray = explode(",",($authorid) ? $authorid : $author);
				foreach ($authorarray as $value) {
					if($authorarray=$authorid){
						$value = intval($value);
						$authorwhere .= " OR uid LIKE ".S::sqlEscape($value,false);
					}else{
						$value = str_replace('*','%',$value);
						$authorwhere .= " OR username LIKE ".S::sqlEscape($value,false);					
					}
				}
				$authorwhere = substr_replace($authorwhere,"",0,3);
				$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt = $db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
				if ($authorids) {
					$sql .= " AND t.authorid IN(".S::sqlImplode($authorids).")";
				} else {
					adminmsg('author_nofind');
				}
			}
			if ($keyword) {
				$keyword = trim($keyword);
				$keywordarray = explode(",",$keyword);
				foreach ($keywordarray as $value) {
					$value = str_replace('*','%',$value);
					$keywhere .= 'OR';
					$keywhere .= " tm.content LIKE ".S::sqlEscape("%$value%")."OR t.subject LIKE ".S::sqlEscape("%$value%");
				}
				$keywhere = substr_replace($keywhere,"",0,3);
				$sql .= " AND ($keywhere) ";
			}
			if ($userip) {
				$userip	 = str_replace('*','%',$userip);
				$sql	.= " AND (tm.userip LIKE ".S::sqlEscape($userip).') ';
				$ip_add  = ',tm.userip';
			}
			$sql .= " AND tm.tid!=''";

			if (!is_numeric($lines))$lines=100;
			if( $sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword,'*') === false ){
				$keyword = trim($keyword);
				$forumIds = ( $fid > 0 ) ? array($fid) : array();
				$sphinxServer = L::loadclass('searcher','search');
				$result = $sphinxServer->manageThreads($keyword,$sphinxRange,$authorarray,$pstarttime,$pendtime,$forumIds,$page,$lines);
				if($result === false){
					adminmsg('search_keyword_empty');
				}
				$count = $result[0];
				$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE tm.tid in (".$result[1].") ORDER BY tid DESC");
			}else{
				$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql LIMIT 1");
				$count = $rs['count'];
				$start = ($page-1)*$lines;
				$limit = S::sqlLimit($start,$lines);
				$query = $db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE $sql ORDER BY tid DESC $limit");
			}
			$page < 1 && $page = 1;
			$numofpage = ceil($count/$lines);
			if ($numofpage && $page > $numofpage) {
				$page = $numofpage;
			}
			//$pages=numofpage_t($count,$page,$numofpage,"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$fid&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$hits&replies=$replies&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&sphinx=$sphinx&sphinxRange=$sphinxRange&");
			$pages = pagerforjs($count, $page, $numofpage, "onclick=\"manageclass.superdel(this,'del_tpc_form','')\"");
			$delid = $topicdb = array();
			//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
			pwCache::getData(D_P.'data/bbscache/forum_cache.php');
			while ($topic = $db->fetch_array($query)) {
				if ($_POST['direct']) {
					$delid[$topic['tid']] = $topic['fid'];
				} else {
					$topic['forumname'] = $forum[$topic['fid']]['name'];
					$topic['postdate'] = get_date($topic['postdate']);
					$topic['lastpost'] = get_date($topic['lastpost']);
					$topicdb[]=$topic;
				}
			}
			if (!$_POST['direct']) {
				include PrintEot('superdel');exit;
			}
		}
		if ($_POST['step'] == 2 || $_POST['direct']) {
			!$direct && S::gp(array('delid'), 'P');
			!$delid && adminmsg('operate_error');
			
			$delids = $delaids = $specialdb = array();
			$fidarray = array();
			foreach ($delid as $key => $value) {
				is_numeric($key) && $delids[] = $key;
			}
			
			$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
			$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($delids));
			
			//$delarticle->delTopic($readdb, $db_recycle); /*需要使用回收站功能？*/
			$delarticle->delTopic($readdb, false);
								
			adminmsg('operate_success',"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$_POST[fid]&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$_POST[hits]&replies=$_POST[replies]&author=".rawurlencode($author)."&authorid=$authorid&keyword=".rawurlencode($keyword)."&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&page=$page");

		}
	} elseif ($action == 'delrpl') {

		S::gp(array('ptable'));
		is_numeric($ptable) && $dbptable = $ptable;
		$pw_posts = GetPtable($dbptable);

		S::gp(array('fid','tid','author','keyword','tcounts','counts','userip','nums'));
		S::gp(array('pstart','pend','page','sphinx','sphinx_range'),'GP',2);

		if (empty($_POST['step'])) {
			if (!$counts && !$tcounts && $fid=='-1' && !$keyword && !$tid && !$author && !$userip && !$pstart && !$pend) {
				adminmsg('noenough_condition');
			}
			if (is_numeric($fid) && $fid > 0) {
				$sql .= " AND fid=".S::sqlEscape($fid);
			}
			if ($tid) {
				$tids = array();
				$tid_array = explode(",",$tid);
				foreach ($tid_array as $value) {
					if (is_numeric($value)) {
						$tids[] = $value;
					}
				}
				$tids && $sql.=" AND tid IN(".S::sqlImplode($tids).")";
			}
			if ($pstart) {
				$sql.=" AND pid>".S::sqlEscape($pstart);
			}
			if ($pend) {
				$sql.=" AND pid<".S::sqlEscape($pend);
			}
			$forceIndex = '';
			if ($author) {
				$authorarray=explode(",",$author);
				foreach ($authorarray as $value) {
					$value=addslashes(str_replace('*','%',$value));
					$authorwhere.=" OR username LIKE ".S::sqlEscape($value);
				}
				$authorwhere=substr_replace($authorwhere,"",0,3);
				$authorids = array();
				$query=$db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt=$db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
				if ($authorids) {
					$sql .= " AND authorid IN(".S::sqlImplode($authorids).")";
					$forceIndex = " FORCE INDEX(".getForceIndex('idx_postdate').") ";
				} else {
					adminmsg('author_nofind');
				}
			}
			if ($keyword) {
				$keyword=trim($keyword);
				$keywordarray=explode(",",$keyword);
				foreach ($keywordarray as $value) {
					$value=str_replace('*','%',$value);
					$keywhere.=" OR content LIKE ".S::sqlEscape("%$value%");
				}
				$keywhere=substr_replace($keywhere,"",0,3);
				$sql.=" AND ($keywhere) ";
			}
			if ($userip) {
				$userip=str_replace('*','%',$userip);
				$sql.=" AND (userip LIKE ".S::sqlEscape($userip).")";
			}

			if ($tcounts) {
				$sql.=" AND char_length(content)>".S::sqlEscape($tcounts);
			} elseif ($counts) {
				$sql.=" AND char_length(content)<".S::sqlEscape($counts);
			}
			$nums = is_numeric($nums) ? $nums : 20;
			if( $sphinx && $keyword && $db_sphinx['isopen'] == 1 && strpos($keyword,'*') === false ){
				$forumIds = ($fid > 0) ? array($fid) : array();
				$sphinxServer = L::loadclass('searcher','search');
				$result = $sphinxServer->manageThreads($keyword,3,$authorarray,$pstart,$pend,$forumIds,$page,$nums);
				if($result === false){
					adminmsg('search_keyword_empty');
				}
				$count = $result[0];
				$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts WHERE pid in (".$result[1].")  ORDER BY postdate DESC ");
			}else{
				$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE $sql");
				$count = $rt['sum'];
				$page < 1 && $page = 1;
				$limit = S::sqlLimit(($page-1)*$nums,$nums);
				$sql  .= ' ORDER BY postdate DESC ';
				$sql  .= $_POST['direct'] ? " LIMIT $nums" : $limit;
				$query = $db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts $forceIndex WHERE $sql");
			}
			//$pages = numofpage_t($count,$page,ceil($count/$nums),"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$fid&tid=$tid&pstart=$pstart&pend=$pend&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&sphinx=$sphinx&");
			$pages = pagerforjs($count, $page, ceil($count/$nums), "onclick=\"manageclass.superdel(this,'del_rpl_form','')\"");
			$delid = $postdb = array();
			while ($post = $db->fetch_array($query)) {
				if ($_POST['direct']) {
					$delid[$post['pid']] = $post['fid'].'_'.$post['tid'];
				} else {
					$post['delid']	   = $post['fid'].'_'.$post['tid'];
					$post['forumname'] = $forum[$post['fid']]['name'];
					$post['postdate']  = get_date($post['postdate']);
					$post['content']   = substrs($post['content'],30);
					$postdb[] = $post;
				}
			}
			if (!$_POST['direct']) {
				include PrintEot('superdel');exit;
			}
		}
		if ($_POST['step'] == 2 || $_POST['direct']) {
			if (!$_POST['direct']) {
				S::gp(array('delid'),'P');
			}
			!$delid && adminmsg('operate_error');
			$delids = $delaids = $dtids = array();
			$fidarray = $tidarray = $delnum = array();
			foreach ($delid as $key=>$value) {
				//$key: pid
				//$value: fid_tid
				list($dfid, $dtid) = explode('_', $value);			
				is_numeric($key) && $dpids[$dtid][] = $key;
				$dtids[$dtid] = $dfid;
			}
						
			$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
			foreach($dpids as $dtid => $pids){
				$pw_tmsgs = GetTtable($dtid);
				$dfid = $dtids[$dtid];
				$threaddb = $db->get_one("SELECT t.tid,t.fid,t.author,t.authorid,t.postdate,t.subject,t.topped,t.anonymous,t.ifshield,t.ptable,t.ifcheck,tm.aid FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$dtid'");
				if(!$threaddb){
					continue;
				}
				$pw_posts = GetPtable($threaddb['ptable']);
				$pids  = S::sqlImplode($pids);
				$query = $db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,ifcheck FROM $pw_posts WHERE tid='$dtid' AND fid='$dfid' AND pid IN($pids)");
				$replydb = array();
				while ($result = $db->fetch_array($query)) {		
					!$result['subject'] && $result['subject'] = substrs($rt['content'],35);
					$result['postdate'] = get_date($result['postdate']);
					$result['ptable'] = $threaddb['ptable'];
					
					$replydb[] = $result;
				}
				/*删除回复*/
				$delarticle->delReply($replydb, false);
				
				/*删除静态*/
				$htmurl = $db_htmdir . '/' . $dfid . '/' . get_date('ym', $threaddb['postdate']) . '/' . $dtid . '.html';
				if (file_exists(R_P . $htmurl)) {
					P_unlink(R_P . $htmurl);
				}
			}

			//* P_unlink(D_P.'data/bbscache/c_cache.php');
			pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
			adminmsg('operate_success',"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$_POST[fid]&tid=$_POST[tid]&pstart=$pstart&pend=$pend&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&page=$page");
		}
	} elseif ($action == 'view') {

		S::gp(array('tid','pid'));

		$pw_posts = GetPtable('N',$tid);
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE tid=".S::sqlEscape($tid).'AND pid<'.S::sqlEscape($pid));
		$page = ceil(($rt['sum']+1.5)/$db_readperpage);

		ObHeader("read.php?tid=$tid&page=$page#$pid");
	}

} elseif ($admintype == 'message') {

	$basename = "$admin_file?adminjob=superdel&admintype=message";
	$messageServer = L::loadClass('message', 'message');

	if (empty($action)) {

		include PrintEot('superdel');exit;

	} elseif ($action == 'del') {
		S::gp(array('stime','etime','fromuser','keyword','lines','direct','page'));
		if(!empty($fromuser)){
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userdb = $userService->getByUserName($fromuser);
			empty($userdb) && adminmsg('输入的用户不存在');
		}
		($lines) && $lines = intval($lines);
		$page = (intval($page)) ? intval($page) : 1;
		$stime && !is_numeric($stime) && $stime =PwStrtoTime($stime);
		$etime && !is_numeric($etime) && $etime = PwStrtoTime($etime);
		$url = $basename."&action=del&stime=".$stime."&etime=".$etime."&fromuser=$fromuser&lines=$lines&keyword=".rawurlencode($keyword)."&";
		if (empty($_POST['step'])) {
			!$lines && $lines = $db_perpage;
			($etime < $stime) && adminmsg('开始时间需要小于结束时间');
			list($searchCount,$searchList) = $messageServer->manageMessage($keyword,$stime,$etime,$fromuser,$direct,$page,$lines);
			$totalPages = ceil($searchCount/$lines);
			$page = ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
			$pages = numofpage($searchCount, $page, $totalPages, "$url");
			if($direct){
				adminmsg('operate_success');
			}else{
				include PrintEot('superdel');exit;
			}
		}
		if ($_POST['step'] == 2) {
			S::gp(array('delid'),'P');
			empty($delid) && adminmsg("请选择要删除的消息");
			$messageServer->manageMessageWithMessageIds($delid);
			adminmsg('operate_success',"$url");
		}
	} elseif ($action == 'msglog') {
		S::gp(array('smstype','keepunread','direct','page'));
		$page = (intval($page)) ? intval($page) : 1;
		$url = $basename."&action=msglog&smstype=$smstype&keepunread=$keepunread&";
		if (empty($_POST['step'])) {
			$direct = 1;
			empty($smstype) && adminmsg('类型不能为空');
			list($searchCount,$searchList) = $messageServer->manageMessageWithCategory($smstype,$keepunread,$direct,$page,$db_perpage);
			$pages = numofpage($searchCount, $page, ceil($searchCount / $db_perpage), "$url");
			if($direct){
				adminmsg('operate_success');
			}else{
				include PrintEot('superdel');exit;
			}
		}elseif($_POST['step'] == 2){
			S::gp(array('delid'),'P');
			empty($delid) && adminmsg("请选择要删除的消息");
			$messageServer->manageMessageWithMessageIds($delid);
			adminmsg('operate_success',"$url");
		}
	}
}


function _delModelTopic($modeldb){
	global $db;
	foreach ($modeldb as $key => $value) {
		$modelids = S::sqlImplode($value);
		$pw_topicvalue = GetTopcitable($key);
		$db->update("DELETE FROM $pw_topicvalue WHERE tid IN($modelids)");
	}
}

function _delPcTopic($pcdb){
	global $db;
	foreach ($pcdb as $key => $value) {
		$pcids =  S::sqlImplode($value);
		$key = $key > 20 ? $key - 20 : 0;
		$key = (int)$key;
		$pcvaluetable = GetPcatetable($key);
		$db->update("DELETE FROM $pcvaluetable WHERE tid IN($pcids)");
	}
}

/**
 * 将活动帖子的数据删除
 * @param array $activityDb 帖子数据，形入array(tid, tid)
 */
function _delActivityTopic ($activityDb) {
	global $db;
	$defaultValueTableName = getActivityValueTableNameByActmid();
	$newActivityDb = array();
	$query = $db->query("SELECT actmid,tid FROM $defaultValueTableName WHERE tid IN(".S::sqlImplode($activityDb).")");
	while ($rt = $db->fetch_array($query)) {
		$newActivityDb[$rt['actmid']][] = $rt['tid'];
	}
	
	/*帖子被删除费用日志更新*/
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();

	/*帖子被删除费用日志更新*/
	foreach ($newActivityDb as $key => $value) {
		$tids = S::sqlImplode($value);
		$userDefinedValueTableName = getActivityValueTableNameByActmid($key, 1, 1);
		$db->update("DELETE FROM $defaultValueTableName WHERE tid IN($tids)");
		$db->update("DELETE FROM $userDefinedValueTableName WHERE tid IN($tids)");
		$db->update("DELETE FROM pw_activitymembers WHERE tid IN($tids)");
		/*帖子被删除费用日志更新*/
		$postActForBbs->UpdatePayLog($value,0,4);
		/*帖子被删除费用日志更新*/
		/*帖子被删除发送站内信*/
		$postActForBbs->activityDelSendmsg($value);
		/*帖子被删除发送站内信*/
	}
}
?>
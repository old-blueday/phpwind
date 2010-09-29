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
		include(D_P.'data/bbscache/forumcache.php');
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

		InitGP(array('ttable'));
		if ($ttable == 'auto') {
			$rt = $db->get_one("SELECT MAX(tid) AS mtid FROM pw_threads");
			$pw_tmsgs = GetTtable($rt['mtid']);
		} else {
			$pw_tmsgs = $ttable>0 ? 'pw_tmsgs'.$ttable : 'pw_tmsgs';
		}
		InitGP(array('fid','ifkeep','pstarttime','pendtime','lstarttime','lendtime','author','keyword','userip','lines','direct'));
		InitGP(array('tstart','tend','hits','replies','tcounts','counts','page','sphinx','sphinxRange'),'GP',2);
		if (empty($_POST['step'])) {
			if($page=="0"){
			$_POST['pstarttime'] && $pstarttime = PwStrtoTime($pstarttime);
			$_POST['pendtime']&&($pendtime)   &&  $pendtime   = PwStrtoTime($pendtime);
			$_POST['lstarttime'] && $lstarttime = PwStrtoTime($lstarttime);
			$_POST['lendtime']   && $lendtime   = PwStrtoTime($lendtime);
			}
			if ($fid=='-1' && !$pstarttime && !$pendtime && !$tcounts && !$counts && !$lstarttime && !$lendtime && !$hits && !$replies && !$author && !$keyword && !$userip && !$tstart && !$tend) {
				adminmsg('noenough_condition');
			}
			if (is_numeric($fid) && $fid > 0) {
				$sql .= " AND t.fid=".pwEscape($fid);
			}
			if ($ifkeep) {
				$sql.=" AND t.topped=0 AND t.digest=0";
			}
			if ($pstarttime) {
				$sql.=" AND t.postdate>".pwEscape($pstarttime);
			}
			if ($pendtime) {
				$sql.=" AND t.postdate<".pwEscape($pendtime);
			}
			if ($lstarttime) {
				$sql.=" AND t.lastpost>".pwEscape($lstarttime);
			}
			if ($lendtime) {
				$sql.=" AND t.lastpost<".pwEscape($lendtime);
			}
			if ($tstart) {
				$sql.=" AND t.tid>".pwEscape($tstart);
			}
			if ($tend) {
				$sql.=" AND t.tid<".pwEscape($tend);
			}
			$hits    && $sql.=" AND t.hits<".pwEscape($hits);
			$replies && $sql.=" AND t.replies<".pwEscape($replies);
			if ($tcounts) {
				$sql.=" AND char_length(tm.content)>".pwEscape($tcounts);
			} elseif ($counts) {
				$sql.=" AND char_length(tm.content)<".pwEscape($counts);
			}
			if ($author) {
				$authorarray = explode(",",$author);
				foreach ($authorarray as $value) {
					$value = str_replace('*','%',$value);
					$authorwhere .= " OR username LIKE ".pwEscape($value,false);
				}
				$authorwhere = substr_replace($authorwhere,"",0,3);
				$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt = $db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
				if ($authorids) {
					$sql .= " AND t.authorid IN(".pwImplode($authorids).")";
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
					$keywhere .= " tm.content LIKE ".pwEscape("%$value%")."OR t.subject LIKE ".pwEscape("%$value%");
				}
				$keywhere = substr_replace($keywhere,"",0,3);
				$sql .= " AND ($keywhere) ";
			}
			if ($userip) {
				$userip	 = str_replace('*','%',$userip);
				$sql	.= " AND (tm.userip LIKE ".pwEscape($userip).') ';
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
				$limit = pwLimit($start,$lines);
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
			include(D_P.'data/bbscache/forum_cache.php');
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
			!$direct && InitGP(array('delid'), 'P');
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
								
			adminmsg('operate_success',"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$_POST[fid]&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$_POST[hits]&replies=$_POST[replies]&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&lines=$lines&ttable=$ttable&tcounts=$tcounts&counts=$counts&page=$page");

		}
	} elseif ($action == 'delrpl') {

		InitGP(array('ptable'));
		is_numeric($ptable) && $dbptable = $ptable;
		$pw_posts = GetPtable($dbptable);

		InitGP(array('fid','tid','author','keyword','tcounts','counts','userip','nums'));
		InitGP(array('pstart','pend','page','sphinx','sphinx_range'),'GP',2);

		if (empty($_POST['step'])) {
			if (!$counts && !$tcounts && $fid=='-1' && !$keyword && !$tid && !$author && !$userip && !$pstart && !$pend) {
				adminmsg('noenough_condition');
			}
			if (is_numeric($fid) && $fid > 0) {
				$sql .= " AND fid=".pwEscape($fid);
			}
			if ($tid) {
				$tids = array();
				$tid_array = explode(",",$tid);
				foreach ($tid_array as $value) {
					if (is_numeric($value)) {
						$tids[] = $value;
					}
				}
				$tids && $sql.=" AND tid IN(".pwImplode($tids).")";
			}
			if ($pstart) {
				$sql.=" AND pid>".pwEscape($pstart);
			}
			if ($pend) {
				$sql.=" AND pid<".pwEscape($pend);
			}
			$forceIndex = '';
			if ($author) {
				$authorarray=explode(",",$author);
				foreach ($authorarray as $value) {
					$value=addslashes(str_replace('*','%',$value));
					$authorwhere.=" OR username LIKE ".pwEscape($value);
				}
				$authorwhere=substr_replace($authorwhere,"",0,3);
				$authorids = array();
				$query=$db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while ($rt=$db->fetch_array($query)) {
					$authorids[] = $rt['uid'];
				}
				if ($authorids) {
					$sql .= " AND authorid IN(".pwImplode($authorids).")";
					$forceIndex = " FORCE INDEX(postdate) ";
				} else {
					adminmsg('author_nofind');
				}
			}
			if ($keyword) {
				$keyword=trim($keyword);
				$keywordarray=explode(",",$keyword);
				foreach ($keywordarray as $value) {
					$value=str_replace('*','%',$value);
					$keywhere.=" OR content LIKE ".pwEscape("%$value%");
				}
				$keywhere=substr_replace($keywhere,"",0,3);
				$sql.=" AND ($keywhere) ";
			}
			if ($userip) {
				$userip=str_replace('*','%',$userip);
				$sql.=" AND (userip LIKE ".pwEscape($userip).")";
			}

			if ($tcounts) {
				$sql.=" AND char_length(content)>".pwEscape($tcounts);
			} elseif ($counts) {
				$sql.=" AND char_length(content)<".pwEscape($counts);
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
				$limit = pwLimit(($page-1)*$nums,$nums);
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
				InitGP(array('delid'),'P');
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
				$pids  = pwImplode($pids);
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

			P_unlink(D_P.'data/bbscache/c_cache.php');
			adminmsg('operate_success',"$admin_file?adminjob=superdel&admintype=article&action=$action&fid=$_POST[fid]&tid=$_POST[tid]&pstart=$pstart&pend=$pend&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&page=$page");
		}
	} elseif ($action == 'view') {

		InitGP(array('tid','pid'));

		$pw_posts = GetPtable('N',$tid);
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE tid=".pwEscape($tid).'AND pid<'.pwEscape($pid));
		$page = ceil(($rt['sum']+1.5)/$db_readperpage);

		ObHeader("read.php?tid=$tid&page=$page#$pid");
	}
} elseif ($admintype == 'delmember') {

	$basename="$admin_file?adminjob=superdel&admintype=delmember";
	if (empty($action)) {

		$groupselect = "<option value='-1'>" . getLangInfo('all','reg_member') . "</option>";
		$query = $db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
		while ($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=$group[gid]>$group[grouptitle]</option>";
		}
		include PrintEot('superdel');exit;

	} elseif ($action == 'del') {

		InitGP(array('groupid','schname','schemail','postnum','onlinetime','userip','regdate','schlastvisit','orderway','asc','lines','item'));
		InitGP(array('page'),'GP',2);
		if (empty($_POST['step'])) {

			if (!$schname && !$schemail && !$groupid && $regdate=='all' && $schlastvisit='all') {
				adminmsg('noenough_condition');
			}
			is_array($item) && $item = array_sum($item);
			for($i=0; $i<4; $i++){
				${'check_'.$i} = ($item & pow(2,$i)) ? 'checked' : '';
			}
			if ($groupid != '-1') {
				if ($groupid == '3' && !If_manager) {
					adminmsg('manager_right');
				} elseif (($groupid == '4' || $groupid == '5') && $admin_gid != 3) {
					adminmsg('admin_right');
				}
				$sql = "m.groupid=".pwEscape($groupid);
			} else {
				$sql = "m.groupid='-1'";
			}
			if ($schname != '') {
				$schname = str_replace('*','%',$schname);
				$sql .= " AND (m.username LIKE ".pwEscape($schname).")";
			}
			if ($schemail != '') {
				$schemail = str_replace('*','%',$schemail);
				$sql .= " AND (m.email LIKE ".pwEscape($schemail).")";
			}
			if ($postnum) {
				$sql .= " AND md.postnum<".pwEscape($postnum);
			}
			if ($onlinetime) {
				$sql .= " AND md.onlinetime<".pwEscape($onlinetime);
			}
			if ($userip) {
				$userip = str_replace('*','%',$userip);
				$sql .= " AND (md.onlineip LIKE ".pwEscape("$userip%").")";
			}
			if ($regdate != 'all') {
				$schtime = $timestamp-$regdate;
				$sql .= " AND m.regdate<".pwEscape($schtime);
			}
			if ($schlastvisit != 'all') {
				$schtime = $timestamp - $schlastvisit;
				$sql .= " AND md.thisvisit<".pwEscape($schtime);
			}
			$order = '';
			if ($orderway) {
				switch ($orderway) {
					//case 'username' : $orderway = 'm.username'; break;//用户名
					case 'regdate' : $orderway = 'm.regdate'; break;//注册时间
					//case 'lastpost' : $orderway = 'md.lastpost'; break;//最后发表
					case 'lastvisit' : $orderway = 'md.lastvisit'; break;//最后登录
					case 'postnum' : $orderway = 'md.postnum'; break;// 发帖
					default: $orderway = $orderway; break;
				}
				$order = "ORDER BY " .$orderway;
				$asc=='DESC' && $order .= " $asc";
			}
			
			$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql");
			$count = $rs['count'];
			if (!is_numeric($lines)) $lines = 100;
			$page < 1 && $page = 1;
			$numofpage = ceil($count/$lines);
			if ($numofpage && $page > $numofpage) {
				$page = $numofpage;
			}
			//$pages = numofpage($count,$page,$numofpage, "$admin_file?adminjob=superdel&admintype=delmember&action=$action&groupid=$groupid&schname=" . rawurlencode($schname)."&schemail=$schemail&postnum=$postnum&onlinetime=$onlinetime&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&asc=$asc&lines=$lines&");
			$pages = pagerforjs($count,$page,$numofpage, "onclick=\"manageclass.superdel(this,'superdel_member','')\"");
			$start = ($page-1)*$lines;
			$limit = "LIMIT $start,$lines";
			$delid = $schdb = array();
			$query = $db->query("SELECT m.uid,m.username,m.email,m.groupid,m.regdate,md.lastvisit,md.postnum,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql $order $limit");
			while ($sch = $db->fetch_array($query)) {
				if ($_POST['direct']) {
					$delid[] = $sch['uid'];
				} else {
					strpos($sch['onlineip'],'|') && $sch['onlineip'] = substr($sch['onlineip'], 0, strpos($sch['onlineip'],'|'));
					if ($sch['groupid'] == '-1') {
						$sch['group'] = getLangInfo('all','reg_member');
					} else {
						$sch['group'] = $ltitle[$sch['groupid']];
					}
					$sch['regdate']   = get_date($sch['regdate']);
					$sch['lastvisit'] = get_date($sch['lastvisit']);
					$schdb[] = $sch;
				}
			}
			if (!$_POST['direct']) {
				include PrintEot('superdel');exit;
			}
		}
		if ($_POST['step'] == 2 || $_POST['direct']) {
			@set_time_limit(300);
			InitGP(array('item'),'P');
			$item = array_sum($item);
			!$item && adminmsg('operate_error');
			if (!$_POST['direct']) {
				InitGP(array('delid'),'P');
			}
			!$delid && adminmsg('operate_error');
			$userIds = $delid;
			$delids = pwImplode($delid);

			if ($item & 1) {
				$ucuser = L::loadClass('Ucuser', 'user'); /* @var $ucuser PW_Ucuser */
				$ucuser->delete($delid);
				
				#TO DO群组
				foreach($userIds as $userId){
					$colonyid =  $db->get_value("SELECT colonyid FROM pw_cmembers WHERE uid=".pwEscape($userId));
					$colonyMembers =  $db->get_value("SELECT count(id) FROM pw_cmembers WHERE colonyid =".pwEscape($colonyid));
					$db->update("UPDATE pw_colonys SET members=".pwEscape($colonyMembers-1)." WHERE id= ".pwEscape($colonyid));
					$db->update("DELETE FROM pw_cmembers WHERE uid=".pwEscape($userId));
				}
				//朋友
				$friendService = L::loadClass('friend', 'friend'); /* @var $friendService PW_Friend */
				$friendService->delFriendByFriendids($userIds);
				
				//关注
				$db->update("DELETE FROM pw_attention WHERE friendid IN (".pwImplode($userIds).")");
			}
			if ($item & 2) {
				$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
				$delarticle->delTopicByUids($delid);
			}
			if ($item & 4) {
				$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
				$delarticle->delReplyByUids($delid);
			}
			if ($item & 8) {
				$messageServer = L::loadClass('message', 'message'); /* @var $messageServer PW_Message */
				foreach($userIds as $userId){
					$messageServer->clearMessages($userId,array('groupsms','sms','notice','request','history'));
				}
			}
			if ($item & 16) {
				require_once(R_P. 'require/app_core.php');
				$photoService = L::loadClass('photo', 'colony'); /* @var $photoService PW_Photo */
				$photoService->delAlbumByUids($userIds);
			}
			if ($item & 32) {
				$diaryService = L::loadClass('diary', 'diary'); /* @var $diaryService PW_Diary */
				$diaryService->delByUids($userIds);
			}
			
			if ($item & 64) {
				$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
				$weiboService->deleteWeibosByUids($userIds);
				
				$db->update("DELETE FROM pw_cwritedata WHERE uid IN (".pwImplode($userIds).")");
			}
			if ($item & 128) {
				$db->update("DELETE FROM pw_comment WHERE uid IN (".pwImplode($userIds).")");
				
				$db->update("DELETE FROM pw_weibo_comment WHERE uid IN (".pwImplode($userIds).")");
				$db->update("UPDATE pw_weibo_content SET replies=0 WHERE uid IN(".pwImplode($userIds).")");
			}
			if ($item & 256) {
				//评分
				$userService = L::loadClass('userservice', 'user'); /* @var $userService PW_Userservice */
				$userNames = $userService->getUserNamesByUserIds($userIds);
				if ($userNames) {
					$db->update("DELETE FROM pw_pinglog WHERE pinger IN (".pwImplode($userNames).")");
				}

				//收藏
				$collectionService = L::loadClass('collection', 'collection'); /* @var $collectionService PW_Collection */
				$collectionService->deleteByUids($userIds);
				
				//朋友
				$friendService = L::loadClass('friend', 'friend'); /* @var $friendService PW_Friend */
				$friendService->delFriendByUids($userIds);
				
				//关注
				$db->update("DELETE FROM pw_attention WHERE uid IN (".pwImplode($userIds).")");
			}

			$userCache = L::loadClass('userCache', 'user');
			$userCache->delete($userIds);
			adminmsg('operate_success', "$admin_file?adminjob=superdel&admintype=delmember&action=$action&groupid=$groupid&schname=" . rawurlencode($schname)."&schemail=$schemail&postnum=$postnum&onlinetime=$onlinetime&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&asc=$asc&lines=$lines&page=$page");
		}
	}
} elseif ($admintype == 'message') {

	$basename = "$admin_file?adminjob=superdel&admintype=message";
	$messageServer = L::loadClass('message', 'message');

	if (empty($action)) {

		include PrintEot('superdel');exit;

	} elseif ($action == 'del') {
		InitGP(array('stime','etime','fromuser','keyword','lines','direct','page'));
		if(!empty($fromuser)){
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userdb = $userService->getByUserName($fromuser);
			empty($userdb) && adminmsg('输入的用户不存在');
		}
		($lines) && $lines = intval($lines);
		$page = (intval($page)) ? intval($page) : 1;
		$url = $basename."&action=del&stime=$stime&etime=$etime&fromuser=$fromuser&lines=$lines&keyword=".rawurlencode($keyword)."&";
		if (empty($_POST['step'])) {
			!$lines && $lines = $db_perpage;
			$stime = empty($stime) ? '' : PwStrtoTime($stime);
			$etime = empty($etime) ? '' : PwStrtoTime($etime);
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
			InitGP(array('delid'),'P');
			empty($delid) && adminmsg("请选择要删除的消息");
			$messageServer->manageMessageWithMessageIds($delid);
			adminmsg('operate_success',"$url");
		}
	} elseif ($action == 'msglog') {
		InitGP(array('smstype','keepunread','direct','page'));
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
			InitGP(array('delid'),'P');
			empty($delid) && adminmsg("请选择要删除的消息");
			$messageServer->manageMessageWithMessageIds($delid);
			adminmsg('operate_success',"$url");
		}
	}
}


function _delModelTopic($modeldb){
	global $db;
	foreach ($modeldb as $key => $value) {
		$modelids = pwImplode($value);
		$pw_topicvalue = GetTopcitable($key);
		$db->update("DELETE FROM $pw_topicvalue WHERE tid IN($modelids)");
	}
}

function _delPcTopic($pcdb){
	global $db;
	foreach ($pcdb as $key => $value) {
		$pcids =  pwImplode($value);
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
	$query = $db->query("SELECT actmid,tid FROM $defaultValueTableName WHERE tid IN(".pwImplode($activityDb).")");
	while ($rt = $db->fetch_array($query)) {
		$newActivityDb[$rt['actmid']][] = $rt['tid'];
	}
	
	/*帖子被删除费用日志更新*/
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();

	/*帖子被删除费用日志更新*/
	foreach ($newActivityDb as $key => $value) {
		$tids = pwImplode($value);
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
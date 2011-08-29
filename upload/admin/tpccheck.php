<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=tpccheck";
include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
include_once(R_P.'require/forum.php');

if ($admin_gid == 5) {
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = $allowfid ? "fid IN($allowfid)" : '0';
} else {
	include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if ($admin_gid == 3) {
		$forumcache .= $hideforum;
		$sql = '1';
	} else {
		$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
	}
}
$action = S::getGP('action');


if (!$action) {
	if (!$_POST['step']) {

		S::gp(array('fid','username','uid','page'));
		if (is_numeric($fid)) {
			$sql .= " AND fid=" . S::sqlEscape($fid);
		} elseif ($sql == '1') {
			$fids = array();
			foreach ($forum as $key => $value) {
				$fids[] = $key;
			}
			$fids && $sql .= " AND fid IN(" . S::sqlImplode($fids) . ")";
		}
		$sql .= " AND ifcheck='0'";
		if ($username) {
			$sql .= " AND author like " . S::sqlEscape("%$username%");
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userdb = $userService->getByUserName($username);
			$uid=$userdb['uid'];
		}
	//	is_numeric($uid) && $sql .= "AND authorid=" . S::sqlEscape($uid);

		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);

		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_threads WHERE $sql");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&fid=$fid&uid=$uid&");

		/**Begin modify by liaohu 2010-06-21*/
		$checkdb = array();

		$query = $db->query("SELECT tid,fid,subject,author,authorid,postdate FROM pw_threads WHERE $sql ORDER BY postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['content_table'] = GetTtable($rt['tid']);

			if ($rt['subject']) {
				$rt['subject'] = substrs($rt['subject'],35);
			} else {
				$rt['subject'] = substrs($rt['content'],35);
			}

			$rt['name']     = $forum[$rt['fid']]['name'];
			$rt['postdate'] = get_date($rt['postdate']);

			$checkdb[] = $rt;
		}

		foreach($checkdb as $key=>$v){
			$query = $db->query("SELECT content FROM ".$v['content_table']." where tid = ".$v['tid']);
			$rt = $db->fetch_array($query);
			$checkdb[$key]['content'] = $rt['content'];
		}
		/**End modify by liaohu 2010-06-21*/
		$forumcache = preg_replace("/\<option value=\"$fid\"\>(.+?)\<\/option\>(\\r?\\n)/is","<option value=\"".$fid."\" selected>\\1</option>\\2",$forumcache);
		include PrintEot('tpccheck');exit;

	} elseif ($_POST['step'] == 2) {

	    /**Begin modify by liaohu 2010-06-21*/
		S::gp(array('pass','dels','here','selid'),'P');

		if(0 == count($pass) && 0 == count($dels) && 0 == count($here)){
			adminmsg("operate_error");
		}

		$pass = array_values($pass);

		if (is_array($pass)) {
			$fids  = $cydb = $arrtmsgs = array();
			$query = $db->query("SELECT tid,fid,tpcstatus FROM pw_threads WHERE $sql AND tid IN(".S::sqlImplode($pass).")");
			while ($rt = $db->fetch_array($query)) {
				$tablename = GetTtable($rt['tid']);
				$arrtmsgs[$tablename][] = $rt['tid'];
				$fids[$rt['fid']] ++;
				if ($rt['tpcstatus'] && getstatus($rt['tpcstatus'], 1)) {
					$cydb[] = $rt['tid'];
				}
			}
			foreach ($fids as $key => $value) {
				$rt = $db->get_one("SELECT tid,author,postdate,subject FROM pw_threads WHERE fid=" . S::sqlEscape($key) . " ORDER BY lastpost DESC LIMIT 1");
				$lastpost = $rt['subject']."\t".$rt['author']."\t".$rt['postdate']."\t"."read.php?tid=$rt[tid]&page=e#a";
				$db->update("UPDATE pw_forumdata"
					. " SET topic=topic+" . S::sqlEscape($value)
						. ',article=article+' . S::sqlEscape($value)
						. ',tpost=tpost+' . S::sqlEscape($value)
						. ',lastpost=' . S::sqlEscape($lastpost)
					. ' WHERE fid=' . S::sqlEscape($key));
			}
			if ($pass) {
				//$db->update("UPDATE pw_threads SET ifcheck='1' WHERE $sql AND tid IN(".S::sqlImplode($pass).")");
				pwQuery::update('pw_threads', "$sql AND tid IN (:tid)", array($pass), array('ifcheck'=>1));
				foreach ($arrtmsgs as $tmsgs => $tids) {
					if ($tids) {
						//* $db->update("UPDATE $tmsgs SET ifwordsfb='$db_wordsfb' WHERE tid IN(".S::sqlImplode($tids).")");
						pwQuery::update($tmsgs, 'tid IN (:tid)', array($tids), array('ifwordsfb'=>$db_wordsfb));
					}
				}
				/**
				$threadIds = explode("','",trim($pass,"'"));
				if($threadIds){
					$threads = L::loadClass('Threads', 'forum');
					$threads->delThreads($threadIds);
				}**/
			}
			if ($cydb) {
				$query = $db->query("SELECT COUNT(*) AS tnum,cyid FROM pw_argument WHERE tid IN(" . S::sqlImplode($cydb) . ") GROUP BY cyid");
				while ($rt = $db->fetch_array($query)) {
					$db->update("UPDATE pw_colonys SET tnum=tnum+" . S::sqlEscape($rt['tnum']) . ' WHERE id=' . S::sqlEscape($rt['cyid']));
				}
			}
			/*
			foreach($fids as $fid){
				$threadList = L::loadClass("threadlist", 'forum');
				$threadList->refreshThreadIdsByForumId($fid);
			} */
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>array_keys($fids)));		
			
		} else if(is_array($dels)){

			$delarticle = L::loadClass('DelArticle', 'forum');
			if (!$sqlby = $delarticle->sqlFormatByIds($dels)) {
				$basename = "javascript:history.go(-1);";
				adminmsg('operate_error');
			}
			$readdb = $delarticle->getTopicDb("$sql AND tid $sqlby");

			$delarticle->delTopic($readdb);
		}
		adminmsg('operate_success');
		/**End modify by liaohu 2010-06-21*/
	}
} else {
	$basename="$admin_file?adminjob=tpccheck&action=postcheck";
	if (!$_POST['step']) {
		S::gp(array('fid','username','uid','page','ptable'));
		/**Begin modify by liaohu 2010-06-21*/
		if ($username) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userdb = $userService->getByUserName($username);
			$uid=$userdb['uid'];
		}
		//is_numeric($uid) && $sql .= " AND authorid=" . S::sqlEscape($uid);
		$sql = str_replace('fid', 't.fid', $sql);
		$sql .= " ORDER BY postdate DESC";
		if ($db_plist && count($db_plist)>1) {
			!isset($ptable) && $ptable = $db_ptable;
			foreach ($db_plist as $key => $val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$p_table .= "<option value=\"$key\">".$name."</option>";
			}	
			$p_table  = str_replace("<option value=\"$ptable\">","<option value=\"$ptable\" selected=\"selected\">",$p_table);
			$pw_posts = GetPtable($ptable);
		} else {
			$pw_posts = 'pw_posts';
		}
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE ifcheck='0' ".(is_numeric($fid) ? " AND fid=".S::sqlEscape($fid) : " ") . ($username  ?  " AND author like " . S::sqlEscape("%$username%") : " " ) . " ORDER BY postdate DESC");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&fid=$fid&uid=$uid&");
		//(is_numeric($uid)  ? " AND t.authorid=" . S::sqlEscape($uid) : " ") .
		$postdb=array();
		$query = $db->query("SELECT p.pid,p.tid,p.fid,p.subject,p.author,p.authorid,p.ifcheck,p.postdate,p.content,t.subject as tsubject FROM $pw_posts AS p LEFT JOIN pw_threads AS t ON p.tid = t.tid WHERE p.ifcheck='0' ".(is_numeric($fid) ? " AND t.fid=".S::sqlEscape($fid) : " ") .($username  ? " AND p.author like " . S::sqlEscape("%$username%") : " ") . " AND $sql $limit");
		/**Begin modify by liaohu 2010-06-21*/
		while ($rt = $db->fetch_array($query)) {
			if ($rt['subject']) {
				$rt['subject'] = substrs($rt['subject'],35);
			} else {
				$rt['subject'] = substrs($rt['content'],35);
			}
			$rt['name']     = $forum[$rt['fid']]['name'];
			$rt['postdate'] = get_date($rt['postdate']);
			$postdb[]       = $rt;
		}

		$forumcache = preg_replace("/\<option value=\"$fid\"\>(.+?)\<\/option\>(\\r?\\n)/is","<option value=\"".$fid."\" selected>\\1</option>\\2",$forumcache);
		include PrintEot('postcheck');exit;

	} elseif ($_POST['step'] == 2) {
		/**Begin modify by liaohu 2010-06-21*/
		S::gp(array('pass','dels','here','ptable'),'P');

		if(0 == count($pass) && 0 == count($dels) && 0 == count($here)){
			adminmsg("operate_error");
		}

		$pass = array_values($pass);
		$pw_posts = GetPtable($ptable);
		//if ($type == 'pass') {
		if(is_array($pass)){
			$fids  = $tids = array();
			$query = $db->query("SELECT fid,tid FROM $pw_posts WHERE $sql AND pid IN(".S::sqlImplode($pass).")");
			while ($rt = $db->fetch_array($query)) {
				$tids[$rt['tid']] ++;
				$fids[$rt['fid']] ++;
			}
			foreach ($tids as $key => $value) {
				$rt = $db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid=" . S::sqlEscape($key) . " ORDER BY postdate DESC LIMIT 1");

				//$db->update("UPDATE pw_threads SET replies=replies+".S::sqlEscape($value) . ",lastpost=" . S::sqlEscape($rt['postdate'],false) . ",lastposter =" . S::sqlEscape($rt['author'],false) . "WHERE tid=" . S::sqlEscape($key));
				$db->update(pwQuery::buildClause('UPDATE :pw_table SET replies = replies + :replies, lastpost = :lastpost, lastposter = :lastposter WHERE tid = :tid', array('pw_threads', $value, $rt['postdate'], $rt['author'], $key)));
				# memcache refresh
				M::sendNotice(
					array($rt['author']),
					array(
						'title' => getLangInfo('writemsg','post_pass_title'),
						'content' => getLangInfo('writemsg','post_pass_content',array(
							'tid' => $key
						)),
					)
				);
				/*
				$threadList = L::loadClass("threadlist", 'forum');
				$threadList->updateThreadIdsByForumId($fid,$key);

				$thread = L::loadClass("Threads", 'forum');
				$thread->clearThreadByThreadId($key); */
				
				Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));				
			}
			foreach ($fids as $key => $value) {
				$db->update("UPDATE pw_forumdata SET article=article+".S::sqlEscape($value).",tpost=tpost+".S::sqlEscape($value,false)."WHERE fid=".S::sqlEscape($key));
			}
			//$db->update("UPDATE $pw_posts SET ifcheck='1',ifwordsfb='$db_wordsfb' WHERE $sql AND pid IN(".S::sqlImplode($pass).")");
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET ifcheck='1',ifwordsfb=:ifwordsfb WHERE $sql AND pid IN(:pid)", array($pw_posts, $db_wordsfb, $pass)));
			/**
			$threadIds = explode(",",$pass);
			if($threadIds){
				$threads = L::loadClass('Threads', 'forum');
				$threads->delThreads($threadIds);
			}**/
		} else if(is_array($dels)){
			require_once(R_P.'require/credit.php');
			$creditOpKey = "Deleterp";
			$forumInfos = array();
			$_tids = $_pids = $deluids = array();
			$query = $db->query("SELECT fid,tid,pid,aid,author,authorid FROM $pw_posts WHERE $sql AND pid IN(".S::sqlImplode($dels).")");
			while ($rt = $db->fetch_array($query)) {
				//积分操作
				if (!isset($forumInfos[$rt['fid']])) $forumInfos[$rt['fid']] = L::forum($rt['fid']);
				$foruminfo = $forumInfos[$rt['fid']];
				$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);
				$credit->addLog("topic_$creditOpKey", $creditset[$creditOpKey], array(
					'uid' => $rt['authorid'],
					'username' => $rt['author'],
					'ip' => $onlineip,
					'fname' => strip_tags($foruminfo['name']),
					'operator' => $windid,
				));
				$credit->sets($rt['authorid'],$creditset[$creditOpKey],false);

				$deluids[$rt['authorid']] = isset($deluids[$rt['authorid']]) ? $deluids[$rt['authorid']] + 1 : 1;
				if ($rt['aid']) {
					$_tids[$rt['tid']] = $rt['tid'];
					$_tids[$rt['pid']] = $rt['pid'];
				}
			}
			$credit->runsql();

			if ($_tids && $_pids) {
				$pw_attachs = L::loadDB('attachs', 'forum');
				$attachdb = $pw_attachs->getByTid($_tids,$_pids);
				require_once(R_P.'require/updateforum.php');
				delete_att($attachdb);
				pwFtpClose($ftp);
			}
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($deluids as $uid => $value) {
				$userService->updateByIncrement($uid, array(), array('postnum'=>-$value));
			}
			//$db->update("DELETE FROM $pw_posts WHERE $sql AND pid IN(".S::sqlImplode($dels).")");
			$db->update(pwQuery::buildClause("DELETE FROM :pw_table WHERE $sql AND pid IN(:pid)", array($pw_posts, $dels)));
		}
		adminmsg('operate_success');
		/**End modify by liaohu 2010-06-21*/
	}
}
?>
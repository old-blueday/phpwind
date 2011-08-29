<?php
!function_exists('adminmsg') && exit('Forbidden');
empty($adminitem) && $adminitem = 'updatecache';
$jobUrl = "$admin_file?adminjob=aboutcache";
$basename="$admin_file?adminjob=aboutcache&adminitem=$adminitem";

if ($adminitem == 'updatecache') {
	
	if(empty($action)){
		$memcache = new ClearMemcache();
		$isMemcachOpen = $memcache->_isMemecacheOpen();
		if($isMemcachOpen){
			$forumSelect = getForumSelectHtml();
		}
		$isUniqueStrategyOpen = $isMemcachOpen;
		include PrintEot('updatecache');exit;
	} elseif($action=='cache'){
		updatecache();
		adminmsg('operate_success');
	} elseif($_POST['action']=='topped'){
		require_once(R_P.'require/updateforum.php');
		updatetop();
		adminmsg('operate_success');
	} elseif($_POST['action']=='bbsinfo'){
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$count = $userService->count();
		$lastestUser = $userService->getLatestNewUser();
		//* $db->update("UPDATE pw_bbsinfo SET newmember=".S::sqlEscape($lastestUser['username']).", totalmember=".S::sqlEscape($count)."WHERE id='1'");
		pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('newmember'=>$lastestUser['username'], 'totalmember'=>$count));
		adminmsg('operate_success');
	} elseif($_POST['action']=='online'){
		$writeinto=str_pad("<?php die;?>",96)."\n";
		pwCache::writeover(D_P.'data/bbscache/online.php',$writeinto);
		pwCache::writeover(D_P.'data/bbscache/guest.php',$writeinto);
		pwCache::writeover(D_P.'data/bbscache/olcache.php',"<?php\n\$userinbbs=0;\n\$guestinbbs=0;\n?>");
		adminmsg('operate_success');
	} elseif($action=='member'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		!$percount && $percount=300;
		!$step && $step = 1;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$maxUid = $db->get_value("SELECT MAX(uid) FROM pw_members");
		//* $_cache = getDatastore();
		$ptable_a=array('pw_posts');
		if ($db_plist && count($db_plist)>1) {
			foreach ($db_plist as $key => $value) {
				if($key == 0) continue;
				$ptable_a[] = 'pw_posts'.$key;
			}
		}
		$sqlWhere = 'WHERE authorid>' . S::sqlEscape($start) . ' AND authorid<=' . S::sqlEscape($next);
		$uids  = array();
		$query = $db->query("SELECT authorid,COUNT(*) as count FROM pw_threads $sqlWhere GROUP BY authorid");
		while ($rt = $db->fetch_array($query)) {
			$uids[$rt['authorid']] = $rt['count'];
		}
		foreach ($ptable_a as $val) {
			$query = $db->query("SELECT authorid,COUNT(*) as count FROM $val $sqlWhere GROUP BY authorid");
			while ($rt = $db->fetch_array($query)) {
				$uids[$rt['authorid']] += $rt['count'];
			}
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($uids as $key => $value) {
			$userService->update($key, null, array('postnum' => $value));
			//* $_cache->delete('UID_'.$key);
		}
	
		if ($next <= $maxUid) {
			adminmsg('updatecache_step',EncodeUrl("$basename&action=$action&step=$step&percount=$percount"));
		} else{
			adminmsg('operate_success',"$basename");
		}
	} elseif($action=='digest'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		!$step && $step=1;
		!$percount && $percount=300;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$j_url="$basename&action=$action&step=$step&percount=$percount";
		$goon=0;
		//* $_cache = getDatastore();
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$query=$db->query("SELECT authorid,COUNT(*) as count FROM pw_threads WHERE digest>0 AND ifcheck='1' GROUP BY authorid LIMIT $start,$percount");
		while($rt=$db->fetch_array($query)){
			$goon=1;
			$userService->update($rt['authorid'], null, array('digests'=>$rt['count']));
			//* $_cache->delete('UID_'.$rt['authorid']);
		}
		if($goon){
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else{
			adminmsg('operate_success');
		}
	} elseif($action=='forum'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
		pwCache::getData(D_P.'data/bbscache/forum_cache.php');
		if(!$step){
			//* $db->update("UPDATE pw_forumdata SET topic=0,article=0,subtopic=0");
			pwQuery::update('pw_forumdata', null, null, array('topic'=>0,'article'=>0, 'subtopic'=>0));
		    $step=1;
		}
		!$percount && $percount=30;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$j_url="$basename&action=$action&step=$step&percount=$percount";
		$goon=0;
		$query=$db->query("SELECT fid,fup,type,allowhtm,cms FROM pw_forums LIMIT $start,$percount");
		while(@extract($db->fetch_array($query))){
			$goon=1;
			@extract($db->get_one("SELECT COUNT(*) AS topic,SUM( replies ) AS replies FROM pw_threads WHERE fid=".S::sqlEscape($fid)."AND ifcheck='1' AND topped<=3"));
			$article=$topic+$replies;
			if($type=='sub2' || $type=='sub'){
				//* $db->update("UPDATE pw_forumdata SET article=article+".S::sqlEscape($article).",subtopic=subtopic+".S::sqlEscape($topic)."WHERE fid=".S::sqlEscape($fup));
				$db->update(pwQuery::buildClause("UPDATE :pw_table SET article=article+:article,subtopic=subtopic+:subtopic WHERE fid=:fid", array('pw_forumdata', $article, $topic, $fup)));
				if($type == 'sub2'){
					$fup=$forum[$fup]['fup'];
					//* $db->update("UPDATE pw_forumdata SET article=article+".S::sqlEscape($article).',subtopic=subtopic+'.S::sqlEscape($topic).'WHERE fid='.S::sqlEscape($fup));
					$db->update(pwQuery::buildClause("UPDATE :pw_table SET article=article+:article,subtopic=subtopic+:subtopic WHERE fid=:fid", array('pw_forumdata', $article, $topic, $fup)));
				}
			} elseif($type=='category'){
				$topic=$article=0;
			}
			$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid)."AND topped=0 AND ifcheck=1 AND lastpost>0 ORDER BY lastpost DESC LIMIT 0,1");
			if($lt['tid']){
				$lt['subject'] = substrs($lt['subject'],21);
				if($lt['postdate']!=$lt['lastpost']){
					$lt['subject']='Re:'.$lt['subject'];
					$add='&page=e#a';
				}
				$toread=$cms ? '&toread=1' : '';
				$htmurl=$db_readdir.'/'.$fid.'/'.date('ym',$lt['postdate']).'/'.$lt['tid'].'.html';
				$new_url=file_exists(R_P.$htmurl) && $allowhtm==1 && !$cms ? "$R_url/$htmurl" : "read.php?tid=$lt[tid]$toread$add";
				$lastinfo=addslashes(S::escapeChar($lt['subject'])."\t".$lt['lastposter']."\t".$lt['lastpost']."\t".$new_url);
			} else{
				$lastinfo='';
			}
			//* $db->update("UPDATE pw_forumdata SET topic=".S::sqlEscape($topic).',article=article+'.S::sqlEscape($article).',lastpost='.S::sqlEscape($lastinfo).' WHERE fid='.S::sqlEscape($fid));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET topic=:topic, article=article+:article,lastpost=:lastpost WHERE fid=:fid", array('pw_forumdata', $topic, $article, $lastinfo, $fid)));
		}
		if($goon){
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else{
			adminmsg('operate_success');
		}
	} elseif($action=='thread'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		!$step && $step=1;
		!$percount &&$percount=300;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$j_url="$basename&action=$action&step=$step&percount=$percount";
		$goon=0;
		$query=$db->query("SELECT tid,replies,ifcheck,ptable FROM pw_threads LIMIT $start,$percount");
		while($rt=$db->fetch_array($query)){
			$goon=1;
			if($rt['ifcheck']){
				$pw_posts = GetPtable($rt['ptable']);
				@extract($db->get_one("SELECT COUNT(*) AS replies FROM $pw_posts WHERE tid=".S::sqlEscape($rt['tid'])."AND ifcheck='1'"));
				if($rt['replies']!=$replies){
					//$db->update("UPDATE pw_threads SET replies=".S::sqlEscape($replies)."WHERE tid=".S::sqlEscape($rt['tid']));
					pwQuery::update('pw_threads', 'tid=:tid', array($rt['tid']), array('replies'=>$replies));
				}
			}
		}
		if($goon){
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else{
			adminmsg('operate_success');
		}
	} elseif ($action == 'updateMemberFriends'){
		//修复用户的好友数
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'),'GP');
		!$step && $step=1;
		!$percount && $percount=1000;
		$start = ($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		//* $_cache = getDatastore();
		$j_url = $basename.'&action='.$action.'&step='.$step.'&percount='.$percount;
		$sql = "SELECT f.uid AS id, COUNT(*) AS value FROM pw_friends f GROUP BY uid LIMIT $start,$percount";
		$query = $db->query($sql);
		$_loop = 0;
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		while ($rt = $db->fetch_array($query)) {
			$_loop = 1;
			$userService->update($rt['id'], null, array('f_num'=>$rt['value']));
			//* $_cache->delete('UID_'.$rt['id']);
		}
		if ($_loop) {
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else {
			adminmsg('operate_success');
		}	
	}  elseif ($action == 'group') {
	
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		!$step && $step=1;
		!$percount && $percount=300;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$j_url="$basename&action=$action&step=$step&percount=$percount";
		//* $_cache = getDatastore();
		$goon=0;
		$query = $db->query("SELECT uid,postnum,digests,rvrc,money,credit as credits,currency,onlinetime FROM pw_memberdata LIMIT $start,$percount");
		while (@extract($db->fetch_array($query))) {
			$goon = 1;
			$usercredit = array(
				'postnum'	=> $postnum,
				'digests'	=> $digests,
				'rvrc'		=> $rvrc,
				'money'		=> $money,
				'credit'	=> $credits,
				'currency'	=> $currency,
				'onlinetime'=> $onlinetime,
			);
			$upgradeset = unserialize($db_upgrade);
			foreach ($upgradeset as $key => $val) {
				if (is_numeric($key)) {
					require_once(R_P.'require/credit.php');
					foreach ($credit->get($uid,'CUSTOM') as $key => $value) {
						$usercredit[$key] = $value;
					}
					break;
				}
			}
			require_once(R_P.'require/functions.php');
			$memberid = getmemberid(CalculateCredit($usercredit,$upgradeset));
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->update($uid, array('memberid'=>$memberid));
			//* $_cache->delete('UID_'.$uid);
		}
		if ($goon) {
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else{
			adminmsg('operate_success');
		}
	} elseif($_POST['action'] == 'usergroup') {
		/*
		* 更新系统组成员头衔
		*/
		//* $_cache = getDatastore();
		$forumadmin = array();
		$query = $db->query("SELECT forumadmin FROM pw_forums WHERE forumadmin!=''");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['forumadmin']) {
				$forumadmin += explode(",",$rt['forumadmin']);
			}
		}
		if ($forumadmin) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$forumadmin = array_unique($forumadmin);
			$query = $db->query("SELECT uid,groupid FROM pw_members WHERE username IN (".S::sqlImplode($forumadmin,false).")");
			while ($rt = $db->fetch_array($query)) {
				if ($rt['groupid']=='-1') {
					$userService->update($rt['uid'], array('groupid'=>5));
					//* $_cache->delete('UID_'.$rt['uid']);
				}
			}
		}
	
		$glist = array('-99');
		$gids  = array();
		$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype IN('default','system','special') AND gid>2");
		while (@extract($db->fetch_array($query))) {
			$gids[] = $gid;
			$glist[] = $gid;
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$query = $db->query("SELECT uid,username,groupid,groups FROM pw_members WHERE groupid<>'-1'");
		while (@extract($db->fetch_array($query))) {
			$username = addslashes($username);
			if (!in_array($groupid,$gids)) {
				$userService->update($uid, array('groupid'=>-1));
				//* $_cache->delete('UID_'.$uid);
				if ($groups == '') {
					admincheck($uid,$username,$groupid,$groups,'delete');
				}
			} else {
				admincheck($uid,$username,$groupid,$groups,'update');
			}
		}
		$db->update("DELETE FROM pw_administrators WHERE groupid NOT IN(".S::sqlImplode($glist,false).") AND groups=''");
		adminmsg('operate_success');
	} elseif ($action == 'appcount') {
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','percount'));
		!$step && $step=1;
		!$percount && $percount=300;
		$start=($step-1)*$percount;
		$next=$start+$percount;
		$step++;
		$j_url="$basename&action=$action&step=$step&percount=$percount";
		$goon=0;
		$query = $db->query("SELECT uid,username FROM pw_members WHERE uid>".S::sqlEscape($start)." AND uid <= ".S::sqlEscape($next));
		while ($rt = $db->fetch_array($query)) {
			$goon = 1;
			$diarynum = $photonum = $owritenum = $groupnum = $sharenum = 0;
			$uid = (int)$rt['uid'];
			$username = $rt['username'];
			$diarynum = $db->get_value("SELECT COUNT(*) FROM pw_diary WHERE uid=".S::sqlEscape($uid));
			//此处连表为用到索引
			$photonum = $db->get_value("SELECT COUNT(*) FROM pw_cnphoto cn LEFT JOIN pw_cnalbum ca ON cn.aid=ca.aid WHERE ca.atype='0' AND ca.ownerid=".S::sqlEscape($uid));
			$owritenum = $db->get_value("SELECT COUNT(*) FROM pw_owritedata WHERE uid=".S::sqlEscape($uid));
			$groupnum = $db->get_value("SELECT COUNT(*) FROM pw_colonys WHERE admin=".S::sqlEscape($username));
			$sharenum = $db->get_value("SELECT COUNT(*) FROM pw_collection WHERE uid=".S::sqlEscape($uid));
			$allnum = $diarynum + $photonum + $owritenum + $groupnum + $sharenum;
			if ($allnum > 0) {
				$db->pw_update(
					"SELECT * FROM pw_ouserdata WHERE uid=".S::sqlEscape($uid),
					"UPDATE pw_ouserdata SET ".S::sqlSingle(array('diarynum' => $diarynum,'photonum' => $photonum,'owritenum' => $owritenum,'groupnum' => $groupnum,'sharenum' => $sharenum))." WHERE uid=".S::sqlEscape($uid),
					"INSERT INTO pw_ouserdata SET ".S::sqlSingle(array('uid' => $uid,'diarynum' => $diarynum,'photonum' => $photonum,'owritenum' => $owritenum,'groupnum' => $groupnum,'sharenum' => $sharenum))
				);
			}
		}
		if ($goon) {
			adminmsg('updatecache_step',EncodeUrl($j_url));
		} else{
			adminmsg('operate_success');
		}
	} elseif ($action == 'refreshMemcache'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('fid'));
		//* $memcache = new ClearMemcache();
		//* $memcache->refresh(array($fid));
		$_cacheService = Perf::gatherCache('pw_threads');
		$_cacheService->clearCacheForThreadListByForumIds($fid);
		adminmsg('operate_success');
	} elseif ($action == 'clearClassCompressFile'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		// 清除类库压缩文件
		$_packService = pwPack::getPackService ();
		$_packService->flushClassFiles();
		adminmsg('operate_success');
	} elseif ($action == 'clearCacheCompressFile'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		// 清除data目录下的缓存文件的压缩包
		$_packService = pwPack::getPackService ();
		$_packService->flushCacheFiles();		
		adminmsg('operate_success');		
	} elseif ($action == 'clearUniqueCache'){
		// 清除唯一主键的缓存
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		$uniqueService = L::loadClass ('unique', 'utility');
		$uniqueService->clear($GLOBALS['db_unique_strategy']);	
		adminmsg('operate_success');
	} elseif ($action == 'clearMemcache'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('fid'));
		//* $memcache = new ClearMemcache();
		//* $memcache->clear(array($fid));
		$_cacheService = Perf::gatherCache('pw_threads');
		$_cacheService->clearCacheForThreadListByForumIds($fid);	
		adminmsg('operate_success');
	}else if ($action == 'clearForumMemcache'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		if (Perf::checkMemcache()){
			// 清除index页面的版块缓存
			$_cacheService = Perf::getCacheService();
			$_cacheService->delete('all_forums_info');
			// 清除thread页面版块缓存
			$query = $db->query('SELECT fid FROM pw_forumdata');
			while($rt = $db->fetch_array($query)){
				$_cacheService->delete('forumdata_announce_' . $rt['fid']);
			}
		}
		adminmsg('operate_success');		
	} elseif ($action == 'flushMemcache'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		//* $memcache = new ClearMemcache();
		//* $memcache->flush();
		$_cacheService = L::loadClass('cacheservice', 'utility');
		$_cacheService->flush(PW_CACHE_MEMCACHE);
		adminmsg('operate_success');
	} elseif ($action == 'flushDatastore') {
		//* $db->update("UPDATE pw_datastore SET expire=expire-3600 WHERE skey LIKE ('UID_%')");
		$_cacheService = perf::gatherCache('pw_membersdbcache');
		$_cacheService->flush();
		if (Perf::checkMemcache()){
			$_cacheService = L::loadClass('cacheservice', 'utility');
			$_cacheService->flush(PW_CACHE_MEMCACHE);			
		}		
		adminmsg('operate_success');
	}
		
} elseif ($adminitem == 'pwcache' || $adminitem == 'blacklist'){
	if (empty($action)) {
		if ($_POST['step']) {
			S::gp(array('config'),'P');
			$ifpwcache = 0;
			foreach ($config['ifpwcache'] as $val) {
				$ifpwcache ^= (int)$val;
			}
			setConfig('db_ifpwcache', $ifpwcache);
		
			$cachenum = $config['cachenum'] ? (int)$config['cachenum'] : 20;
			setConfig('db_cachenum', $cachenum);
			updatecache_c();
			adminmsg('operate_success',"$basename");
		} else {
			$ifpwcache_1 = $db_ifpwcache&1 ? 'checked' : '';
			$ifpwcache_2 = $db_ifpwcache&2 ? 'checked' : '';
			$ifpwcache_4 = $db_ifpwcache&4 ? 'checked' : '';
			$ifpwcache_8 = $db_ifpwcache&8 ? 'checked' : '';
			$ifpwcache_16 = $db_ifpwcache&16 ? 'checked' : '';
			$ifpwcache_32 = $db_ifpwcache&32 ? 'checked' : '';
			$ifpwcache_64 = $db_ifpwcache&64 ? 'checked' : '';
			$ifpwcache_128 = $db_ifpwcache&128 ? 'checked' : '';
			$ifpwcache_256 = $db_ifpwcache&256 ? 'checked' : '';
			$ifpwcache_512 = $db_ifpwcache&512 ? 'checked' : '';
			$ifpwcache_1024 = $db_ifpwcache&1024 ? 'checked' : '';
			!$db_cachenum && $db_cachenum = 20;
			include PrintEot('pwcache');exit;
		}
	} elseif ($action == 'blacklist') {
		
		if ($_POST['step']) {
			InitGP(array('tidblacklist', 'uidblacklist'), 'P');
			$tidblacklist = checkNumStrList($tidblacklist);
			$uidblacklist = checkNumStrList($uidblacklist);
			setConfig('db_tidblacklist', $tidblacklist);
			setConfig('db_uidblacklist', $uidblacklist);
			
			if ($tidblacklist) {
				$db->update("DELETE FROM pw_elements WHERE type IN('hitsort','hitsortday','hitsortweek','replysort','replysortday','replysortweek', 'newsubject','newreply') AND id IN(" . S::sqlImplode(explode(',', $tidblacklist)) . ')');
			}
			if ($uidblacklist) {
				$db->update("DELETE FROM pw_elements WHERE type='usersort' AND id IN(" . S::sqlImplode(explode(',', $uidblacklist)) . ')');
			}
			updatecache_c();
			adminmsg('operate_success', $basename . '&action=blacklist');
	
		} else {
			
			include PrintEot('pwcache');exit;
		}
	
	} elseif ($action == 'update') {
		$type = S::getGP('type','G');
		if (!$type) {
			include PrintEot('pwcache');exit;
		} else {
			!$db_sortnum && $db_sortnum = 20;
			L::loadClass('getinfo', '', false);
			$getinfo =& GetInfo::getInstance();
			if (in_array($type,array('replysort','replysortday','replysortweek'))){
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
	
				$fourmlimit = array();
				for ($j=0;$j<5;$j++) {
					$replysort_judge = '';
					//* @include_once pwCache::getPath(S::escapePath(D_P.'data/bbscache/replysort_judge_'.$j.'.php'));
					pwCache::getData(S::escapePath(D_P.'data/bbscache/replysort_judge_'.$j.'.php'));
					$replysort_judge && $fourmlimit[$j] = $replysort_judge;
				}
				if (!$step) {
					$step = 0;
					for ($j=0;$j<5;$j++) {
						$fourmlimit[$j][$type] = array();
					}
					$db->query("DELETE FROM pw_elements WHERE type=".S::sqlEscape($type));
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$foruminfo = $db->get_one("SELECT allowtype FROM pw_forums WHERE fid=".S::sqlEscape($fourmid));
							$allowtype = $foruminfo['allowtype'];
							$arr_posts = array();
							if ($type == 'replysortday') {
								$hour = 24;
							} elseif ($type == 'replysortweek') {
								$hour = 7 * 24;
							} else {
								$hour = 0;
							}
							for ($j=0;$j<5;$j++) {
								if ($allowtype & pow(2,$j)) {
									$arr_posts[$j] = $getinfo->getPostList('replysort',$fourmid,$db_sortnum,$hour,$j);
									$arr_posts[$j] = arr_unique($arr_posts[$j]);
								}
							}
							foreach ($arr_posts as $key => $value) {
								foreach ($value as $k => $v) {
									$arr_posts[$key][$k]['type'] = $type;
									$arr_posts[$key][$k]['mark'] = $fourmid;
									$type == 'replysort' && $arr_posts[$key][$k]['addition'] = '';
								}
							}
	
							$updatesql = array();
							foreach ($arr_posts as $key => $value) {
								if (count($value) == $db_sortnum) {
									$tmpdate = end($value);
									$fourmlimit[$key][$type][$fourmid] = $tmpdate['value'];
								} else {
									$fourmlimit[$key][$type][$fourmid] = 0;
								}
								$updatesql = array_merge($updatesql,$value);
							}
							if ($updatesql) {
								$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".S::sqlMulti($updatesql,false);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				foreach ($fourmlimit as $key => $value) {
					if ($value) {
						pwCache::setData(D_P.'data/bbscache/'.S::escapePath('replysort_judge_'.$key).'.php',"<?php\r\n\$replysort_judge=".pw_var_export($value).";\r\n?>");
					}
				}
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=$type&step=$step");
				}
			} elseif (in_array($type,array('hitsort','hitsortday','hitsortweek'))) {
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
	
				$fourmlimit = array();
				//* @include_once pwCache::getPath(D_P.'data/bbscache/hitsort_judge.php');
				pwCache::getData(D_P.'data/bbscache/hitsort_judge.php');
				$hitsort_judge && $fourmlimit = $hitsort_judge;
				if (!$step) {
					$step = 0;
					$db->query("DELETE FROM pw_elements WHERE type=".S::sqlEscape($type));
					$fourmlimit[$type] = array();
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$arr_posts = array();
							if ($type == 'hitsortday') {
								$hour = 24;
							} elseif ($type == 'hitsortweek') {
								$hour = 7 * 24;
							} else {
								$hour = 0;
							}
	
							$arr_posts = $getinfo->getPostList('hitsort',$fourmid,$db_sortnum,$hour);
							$arr_posts = arr_unique($arr_posts);
							foreach ($arr_posts as $key => $value) {
								$arr_posts[$key]['type'] = $type;
								$arr_posts[$key]['mark'] = $fourmid;
								$type == 'hitsort' && $arr_posts[$key]['addition'] = '';
							}
	
	
							if (count($arr_posts) == $db_sortnum) {
								$tmpdate = end($arr_posts);
								$fourmlimit[$type][$fourmid] = $tmpdate['value'];
							} else {
								$fourmlimit[$type][$fourmid] = 0;
							}
	
							if ($arr_posts) {
								$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".S::sqlMulti($arr_posts,false);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				pwCache::setData(D_P.'data/bbscache/hitsort_judge.php',"<?php\r\n\$hitsort_judge=".pw_var_export($fourmlimit).";\r\n?>");
				touch(D_P.'data/bbscache/hitsort_judge.php');
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=$type&step=$step");
				}
			} elseif ($type=='usersort') {
				//* include_once pwCache::getPath(D_P.'data/bbscache/usersort_judge.php');
				pwCache::getData(D_P.'data/bbscache/usersort_judge.php');
				$step = intval(S::getGP('step'));
				$sorttype = array('money','rvrc','credit','currency','todaypost','monthpost','postnum','monoltime','onlinetime','digests','f_num','postMostUser');
				foreach ($_CREDITDB as $key => $val) {
					is_numeric($key) &&	$sorttype[] = $key;
				}
				$arr_sortkeys = array_keys($sorttype);
				if (!$step) {
					$step = 0;
					$_usersort = array();
					$usersort_judge = array();
				}
				$total = count($sorttype);
				if ($step < $total) {
					$mark = $sorttype[$step];
					$db->update("DELETE FROM pw_elements WHERE type='usersort' AND mark=".S::sqlEscape($mark));
					$step++;
					$_usersort = $getinfo->userSort($mark,$db_sortnum,false);
					$_usersort = arr_unique($_usersort);
					if (is_array($_usersort) && count($_usersort)==$db_sortnum) {
						$tmpdate = end($_usersort);
						$usersort_judge[$mark] = $tmpdate['value'];
					} else {
						$usersort_judge[$mark] = 0;
					}
					if ($_usersort) {
						$sql = "REPLACE INTO pw_elements(id,value,addition,type,mark) VALUES".S::sqlMulti($_usersort,false);
						$db->update($sql);
					}
	
					pwCache::setData(D_P.'data/bbscache/usersort_judge.php',"<?php\r\n\$usersort_judge=".pw_var_export($usersort_judge).";\r\n?>");
	
					adminmsg('updatecache_total_step',"$basename&action=update&type=usersort&step=$step");
				}
			} elseif ($type=='newsubject') {
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
				if (!$step) {
					$step = 0;
					$db->query("DELETE FROM pw_elements WHERE type='newsubject'");
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$arr_posts = array();
							$arr_posts = $getinfo->getPostList('newsubject',$fourmid,$db_sortnum);
							$arr_posts = arr_unique($arr_posts);
							foreach ($arr_posts as $key => $value) {
								$arr_posts[$key]['type'] = 'newsubject';
								$arr_posts[$key]['mark'] = $fourmid;
							}
							if ($arr_posts) {
								$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES".S::sqlMulti($arr_posts,false);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=newsubject&step=$step");
				}
			} elseif ($type == 'newreply') {
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
				if (!$step) {
					$step = 0;
					$db->query("DELETE FROM pw_elements WHERE type='newreply'");
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$arr_posts = array();
							$arr_posts = $getinfo->getPostList('newreply',$fourmid,$db_sortnum);
							$arr_posts = arr_unique($arr_posts);
							foreach ($arr_posts as $key => $value) {
								$arr_posts[$key]['type'] = 'newreply';
								$arr_posts[$key]['mark'] = $fourmid;
							}
							if ($arr_posts) {
								$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES".S::sqlMulti($arr_posts,false);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=newreply&step=$step");
				}
			} elseif ($type == 'newpic') {
				//adminmsg('newpic_not_needupdate');
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
				if (!$step) {
					$step = 0;
					$db->query("DELETE FROM pw_elements WHERE type='newpic'");
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$arr_posts = array();
							$arr_posts = $getinfo->newAttach('img',$fourmid,$db_sortnum);
							$arr_posts = arr_unique($arr_posts);
							foreach ($arr_posts as $key => $value) {
								$arr_posts[$key]['type'] = 'newpic';
								$arr_posts[$key]['mark'] = $fourmid;
							}
							if ($arr_posts) {
								$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".S::sqlMulti($arr_posts,true);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=newpic&step=$step");
				}
			} elseif ($type=='hotfavor') {
				$step = intval(S::getGP('step'));
				//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
				pwCache::getData(D_P.'data/bbscache/forum_cache.php');
				$arr_forumkeys = array_keys($forum);
				if (!$step) {
					$step = 0;
					$db->query("DELETE FROM pw_elements WHERE type='hotfavor'");
				}
				$total = count($arr_forumkeys);
				for ($i=0;$i<5;$i++) {
					if ($step < $total) {
						$fourmid = $arr_forumkeys[$step];
						!$forum[$fourmid] && adminmsg('undefined_action');
						$step++;
						if ($forum[$fourmid]['type']=='category') {
							continue;
						} else {
							$arr_posts = array();
							$arr_posts = $getinfo->hotfavor($fourmid,$db_sortnum);
							$arr_posts = arr_unique($arr_posts);
	
							foreach ($arr_posts as $key => $value) {
								$arr_posts[$key]['type'] = 'hotfavor';
							}
	
							if ($arr_posts) {
								$sql = "REPLACE INTO pw_elements(id,mark,value,type) VALUES".S::sqlMulti($arr_posts,false);
								$db->update($sql);
							}
						}
					} else {
						break;
					}
				}
				if ($step < $total) {
					adminmsg('updatecache_total_step',"$basename&action=update&type=hotfavor&step=$step");
				}
			}
			adminmsg('operate_success');
		}
	} else {
		adminmsg('undefined_action');
	}
	include PrintEot('pwcache');exit;
} elseif ($adminitem == 'threadcache' || $adminitem == 'guestcache'){
	S::GP(array('step','config'));
	if ($step != 2){
		//thread
		$db_fcachenum = (int) $db_fcachenum;
		$db_fcachetime = (int) $db_fcachetime;
		//guest
		$db_fguestnum = (int) $db_fguestnum;
		$db_tguestnum = (int) $db_tguestnum;
		$db_guestread = (int) $db_guestread;
		$db_guestindex = (int) $db_guestindex;
		$db_guestthread = (int) $db_guestthread;
		include PrintEot('pcache');exit;
	} else {
		//guest
		substr($config['guestdir'], -1) == '/' && $config['guestdir'] = substr($config['guestdir'], 0, -1);
		saveConfig();
		adminmsg('operate_success');
	}
} elseif ($adminitem == 'guestdir'){
	//缓存文件清理
	if (!is_dir(D_P.$db_guestdir)) {
		adminmsg('gusetdir_not_exists');
	}
	if (empty($action)) {
	
		$f_num	= $f_size = $g_size = $g_num = 0;
		PwDir(D_P.$db_guestdir);
		$g_size = round($g_size/1048576,2);
		if ($g_num > 1000) {
			$g_num	= '>'.$g_num;
			$g_size = '>'.$g_size;
		}
		$fp = opendir(D_P.'data/bbscache');
		while ($file = readdir($fp)) {
			if ($file!='' && !in_array($file,array('.','..')) && preg_match('/^fcache\_\d+\_\d+\.php$/i',$file)) {
				++$f_num;
				$f_size += filesize(D_P.'data/bbscache/'.$file);
			}
			if ($f_num > 1000) break;
		}
		closedir($fp);
	
		$f_size = round($f_size/1048576,2);
		if ($f_num > 1000) {
			$f_num	= '>'.$f_num;
			$f_size = '>'.$f_size;
		}
	
		include PrintEot('guestdir');exit;
	
	} elseif ($action == 'delete') {
	
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('num','selid'));
		S::gp(array('step'),'GP',2);
	
		if (empty($selid)) {
			adminmsg('operate_error');
		}
		!is_numeric($num) && $num = 1000;
		$isnum	= 1;
		$path	= D_P.$db_guestdir;
		++$step;
	
		$fp = opendir($path);
	
		while ($file = readdir($fp)) {
			if ($file!='' && !in_array($file,array('.','..'))) {
				if (is_dir("$path/$file")) {
					if ($file[0]=='T' && $selid[2] || $file[0]=='R' && $selid[3]) {
						$fp1 = opendir("$path/$file");
						while ($file1 = readdir($fp1)) {
							if ($file1!='' && !in_array($file1,array('.','..'))) {
								++$isnum;
								P_unlink("$path/$file/$file1");
								if ($isnum > $num) break;
							}
						}
						closedir($fp1);
						rmdir("$path/$file");
					}
				} elseif ($selid[1]) {
					++$isnum;
					P_unlink("$path/$file");
				}
			}
			if ($isnum > $num) break;
		}
		closedir($fp);
	
		if ($isnum > $num) {
			$url = "$basename&action=delete&num=$num&step=$step";
			foreach ($selid as $key=>$value) {
				$url .= "&selid[$key]=$value";
			}
			$delnum = $num*$step;
			adminmsg('guestdir_delete',EncodeUrl($url),2);
		}
	
		adminmsg('operate_success');
	
	} elseif ($action == 'delf') {
	
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('num'));
		S::gp(array('step'),'GP',2);
	
		!is_numeric($num) && $num = 1000;
		$step	= (int)$step;
		$isnum	= 1;
		$path	= D_P.'data/bbscache';
		++$step;
	
		$fp = opendir($path);
	
		while ($file = readdir($fp)) {
			if ($file!='' && !in_array($file,array('.','..')) && preg_match('/^fcache\_\d+\_\d+\.php$/i',$file)) {
				++$isnum;
				//* P_unlink("$path/$file");
				pwCache::deleteData("$path/$file");
			}
			if ($isnum > $num) break;
		}
		closedir($fp);
	
		if ($isnum > $num) {
			$url = "$basename&action=delf&num=$num&step=$step";
			$delnum = $num*$step;
			adminmsg('fcache_delete',EncodeUrl($url),2);
		}
		adminmsg('operate_success');
	}
}


//class & functions for updatecache
class ClearMemcache {
	function _isMemecacheOpen(){
		return class_exists("Memcache") && strtolower($GLOBALS['db_datastore']) == 'memcache';
	}
}

function getForumSelectHtml(){
    global $db;
   	$query	= $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid ORDER BY f.vieworder,f.fid");
	$fkeys = array('fid','fup','ifsub','childid','type','name','style','f_type','cms','ifhide');
	$catedb = $forumdb = $subdb1 = $subdb2 = $forum_cache = $fname= array();
	while ($forums = $db->fetch_array($query)) {
		$fname[$forums['fid']] = str_replace(array("\\","'",'<','>'),array("\\\\","\'",'&lt;','&gt;'), strip_tags($forums['name']));
		$forum = array();
		foreach ($fkeys as $k) {
			$forum[$k] = $forums[$k];
		}
		if ($forum['type'] == 'category') {
			$catedb[] = $forum;
		} elseif ($forum['type'] == 'forum') {
			$forumdb[$forum['fup']] || $forumdb[$forum['fup']] = array();
			$forumdb[$forum['fup']][] = $forum;
		} elseif ($forum['type'] == 'sub') {
			$subdb1[$forum['fup']] || $subdb1[$forum['fup']] = array();
			$subdb1[$forum['fup']][] = $forum;
		} else {
			$subdb2[$forum['fup']] || $subdb2[$forum['fup']] = array();
			$subdb2[$forum['fup']][] = $forum;
		}
	}
	$forumcache = '';
	foreach ($catedb as $cate) {
		if (!$cate) continue;
		$forum_cache[$cate['fid']] = $cate;
		$forumlist_cache[$cate['fid']]['name'] = strip_tags($cate['name']);
                $forumcache .= "<option value=\"$cate[fid]\">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		if (!$forumdb[$cate['fid']]) continue;

		foreach ($forumdb[$cate['fid']] as $forum) {
			$forum_cache[$forum['fid']] = $forum;
                        $forumlist_cache[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
                        $forumcache .= "<option value=\"$forum[fid]\"> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			if (!$subdb1[$forum['fid']]) continue;
			foreach ($subdb1[$forum['fid']] as $sub1) {
				$forum_cache[$sub1['fid']] = $sub1;
				$forumcache .= "<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				if (!$subdb2[$sub1['fid']]) continue;

				foreach ($subdb2[$sub1['fid']] as $sub2) {
					$forum_cache[$sub2['fid']] = $sub2;
					$forumcache .= "<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
				}
			}
		}
	}
     return $forumcache;
}

//end class & functions for updatecache

//class & functions for pwcache & blacklist
function arr_unique($array){
	if (is_array($array)) {
		$temp_array = array();
		foreach ($array as $key => $value) {
			$var_md5 = md5(is_array($value) ? serialize($value) : $value);
			if (in_array($var_md5,$temp_array)) {
				unset($array[$key]);
			} else {
				$temp_array[] = $var_md5;
			}
		}
	}
	return $array;
}

function checkNumStrList($str) {
	$arr = explode(',', $str);
	$ret = array();
	foreach ($arr as $key => $val) {
		if ($val > 0) {
			$ret[] = intval($val);
		}
	}
	return $ret ? implode(',', $ret) : '';
}
//end class & functions for pwcache & blacklist

//class & functions for guestdir
function PwDir($path) {
	global $g_num,$g_size;
	$fp = opendir($path);

	while ($file = readdir($fp)) {
		if ($file!='' && !in_array($file,array('.','..'))) {
			if (is_dir("$path/$file")) {
				PwDir("$path/$file");
			} else {
				++$g_num;
				$g_size += filesize("$path/$file");
			}
		}
		if ($g_num > 1000) break;
	}
	closedir($fp);
}
//end class & functions for guestdir
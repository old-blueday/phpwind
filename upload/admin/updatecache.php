<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=updatecache";

if(empty($action)){
	$memcache = new ClearMemcache();
	$isMemcachOpen = $memcache->_isMemecacheOpen();
	if($isMemcachOpen){
		$forumSelect = getForumSelectHtml();
	}
	include PrintEot('update');exit;
} elseif($_POST['action']=='cache'){
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
	$db->update("UPDATE pw_bbsinfo SET newmember=".pwEscape($lastestUser['username']).", totalmember=".pwEscape($count)."WHERE id='1'");
	adminmsg('operate_success');
} elseif($_POST['action']=='online'){
	$writeinto=str_pad("<?php die;?>",96)."\n";
	writeover(D_P.'data/bbscache/online.php',$writeinto);
	writeover(D_P.'data/bbscache/guest.php',$writeinto);
	writeover(D_P.'data/bbscache/olcache.php',"<?php\n\$userinbbs=0;\n\$guestinbbs=0;\n?>");
	adminmsg('operate_success');
} elseif($action=='member'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
	!$percount && $percount=300;
	!$step && $step = 1;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$maxUid = $db->get_value("SELECT MAX(uid) FROM pw_members");
	$_cache = getDatastore();
	$ptable_a=array('pw_posts');
	if ($db_plist && count($db_plist)>1) {
		foreach ($db_plist as $key => $value) {
			if($key == 0) continue;
			$ptable_a[] = 'pw_posts'.$key;
		}
	}
	$sqlWhere = 'WHERE authorid>' . pwEscape($start) . ' AND authorid<=' . pwEscape($next);
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
		$_cache->delete('UID_'.$key);
	}

	if ($next <= $maxUid) {
		adminmsg('updatecache_step',EncodeUrl("$basename&action=$action&step=$step&percount=$percount"));
	} else{
		adminmsg('operate_success',"$admin_file?adminjob=setuser");
	}
} elseif($action=='digest'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
	!$step && $step=1;
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$j_url="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$_cache = getDatastore();
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$query=$db->query("SELECT authorid,COUNT(*) as count FROM pw_threads WHERE digest>0 AND ifcheck='1' GROUP BY authorid LIMIT $start,$percount");
	while($rt=$db->fetch_array($query)){
		$goon=1;
		$userService->update($rt['authorid'], null, array('digests'=>$rt['count']));
		$_cache->delete('UID_'.$rt['authorid']);
	}
	if($goon){
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='forum'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
	include_once(D_P.'data/bbscache/forum_cache.php');
	if(!$step){
		$db->update("UPDATE pw_forumdata SET topic=0,article=0,subtopic=0");
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
		@extract($db->get_one("SELECT COUNT(*) AS topic,SUM( replies ) AS replies FROM pw_threads WHERE fid=".pwEscape($fid)."AND ifcheck='1' AND topped<=3"));
		$article=$topic+$replies;
		if($type=='sub2' || $type=='sub'){
			$db->update("UPDATE pw_forumdata SET article=article+".pwEscape($article).",subtopic=subtopic+".pwEscape($topic)."WHERE fid=".pwEscape($fup));
			if($type == 'sub2'){
				$fup=$forum[$fup]['fup'];
				$db->update("UPDATE pw_forumdata SET article=article+".pwEscape($article).',subtopic=subtopic+'.pwEscape($topic).'WHERE fid='.pwEscape($fup));
			}
		} elseif($type=='category'){
			$topic=$article=0;
		}
		$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid=".pwEscape($fid)."AND topped=0 AND ifcheck=1 AND lastpost>0 ORDER BY lastpost DESC LIMIT 0,1");
		if($lt['tid']){
			$lt['subject'] = substrs($lt['subject'],21);
			if($lt['postdate']!=$lt['lastpost']){
				$lt['subject']='Re:'.$lt['subject'];
				$add='&page=e#a';
			}
			$toread=$cms ? '&toread=1' : '';
			$htmurl=$db_readdir.'/'.$fid.'/'.date('ym',$lt['postdate']).'/'.$lt['tid'].'.html';
			$new_url=file_exists(R_P.$htmurl) && $allowhtm==1 && !$cms ? "$R_url/$htmurl" : "read.php?tid=$lt[tid]$toread$add";
			$lastinfo=addslashes(Char_cv($lt['subject'])."\t".$lt['lastposter']."\t".$lt['lastpost']."\t".$new_url);
		} else{
			$lastinfo='';
		}
		$db->update("UPDATE pw_forumdata SET topic=".pwEscape($topic).',article=article+'.pwEscape($article).',lastpost='.pwEscape($lastinfo).' WHERE fid='.pwEscape($fid));
	}
	if($goon){
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='thread'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
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
			@extract($db->get_one("SELECT COUNT(*) AS replies FROM $pw_posts WHERE tid=".pwEscape($rt['tid'])."AND ifcheck='1'"));
			if($rt['replies']!=$replies){
				$db->update("UPDATE pw_threads SET replies=".pwEscape($replies)."WHERE tid=".pwEscape($rt['tid']));
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
	InitGP(array('step','percount'),'GP');
	!$step && $step=1;
	!$percount && $percount=1000;
	$start = ($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$_cache = getDatastore();
	$j_url = $basename.'&action='.$action.'&step='.$step.'&percount='.$percount;
	$sql = "SELECT f.uid AS id, COUNT(*) AS value FROM pw_friends f GROUP BY uid LIMIT $start,$percount";
	$query = $db->query($sql);
	$_loop = 0;
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	while ($rt = $db->fetch_array($query)) {
		$_loop = 1;
		$userService->update($rt['id'], null, array('f_num'=>$rt['value']));
		$_cache->delete('UID_'.$rt['id']);
	}
	if ($_loop) {
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else {
		adminmsg('operate_success');
	}	
}  elseif ($action == 'group') {

	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
	!$step && $step=1;
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$j_url="$basename&action=$action&step=$step&percount=$percount";
	$_cache = getDatastore();
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
		$_cache->delete('UID_'.$uid);
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
	$_cache = getDatastore();
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
		$query = $db->query("SELECT uid,groupid FROM pw_members WHERE username IN (".pwImplode($forumadmin,false).")");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['groupid']=='-1') {
				$userService->update($rt['uid'], array('groupid'=>5));
				$_cache->delete('UID_'.$rt['uid']);
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
			$_cache->delete('UID_'.$uid);
			if ($groups == '') {
				admincheck($uid,$username,$groupid,$groups,'delete');
			}
		} else {
			admincheck($uid,$username,$groupid,$groups,'update');
		}
	}
	$db->update("DELETE FROM pw_administrators WHERE groupid NOT IN(".pwImplode($glist,false).") AND groups=''");
	adminmsg('operate_success');
} elseif ($action == 'appcount') {
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('step','percount'));
	!$step && $step=1;
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$j_url="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query = $db->query("SELECT uid,username FROM pw_members WHERE uid>".pwEscape($start)." AND uid <= ".pwEscape($next));
	while ($rt = $db->fetch_array($query)) {
		$goon = 1;
		$diarynum = $photonum = $owritenum = $groupnum = $sharenum = 0;
		$uid = (int)$rt['uid'];
		$username = $rt['username'];
		$diarynum = $db->get_value("SELECT COUNT(*) FROM pw_diary WHERE uid=".pwEscape($uid));
		//此处连表为用到索引
		$photonum = $db->get_value("SELECT COUNT(*) FROM pw_cnphoto cn LEFT JOIN pw_cnalbum ca ON cn.aid=ca.aid WHERE ca.atype='0' AND ca.ownerid=".pwEscape($uid));
		$owritenum = $db->get_value("SELECT COUNT(*) FROM pw_owritedata WHERE uid=".pwEscape($uid));
		$groupnum = $db->get_value("SELECT COUNT(*) FROM pw_colonys WHERE admin=".pwEscape($username));
		$sharenum = $db->get_value("SELECT COUNT(*) FROM pw_collection WHERE uid=".pwEscape($uid));
		$allnum = $diarynum + $photonum + $owritenum + $groupnum + $sharenum;
		if ($allnum > 0) {
			$db->pw_update(
				"SELECT * FROM pw_ouserdata WHERE uid=".pwEscape($uid),
				"UPDATE pw_ouserdata SET ".pwSqlSingle(array('diarynum' => $diarynum,'photonum' => $photonum,'owritenum' => $owritenum,'groupnum' => $groupnum,'sharenum' => $sharenum))." WHERE uid=".pwEscape($uid),
				"INSERT INTO pw_ouserdata SET ".pwSqlSingle(array('uid' => $uid,'diarynum' => $diarynum,'photonum' => $photonum,'owritenum' => $owritenum,'groupnum' => $groupnum,'sharenum' => $sharenum))
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
	InitGP(array('fid'));
	$memcache = new ClearMemcache();
	$memcache->refresh(array($fid));
	adminmsg('operate_success');
} elseif ($action == 'clearMemcache'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('fid'));
	$memcache = new ClearMemcache();
	$memcache->clear(array($fid));
	adminmsg('operate_success');
	
} elseif ($action == 'flushMemcache'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	$memcache = new ClearMemcache();
	$memcache->flush();
	adminmsg('operate_success');
} elseif ($action == 'flushDatastore') {
	$db->update("UPDATE pw_datastore SET expire=expire-3600 WHERE skey LIKE ('UID_%')");
	adminmsg('operate_success');
}

class ClearMemcache {
	
	/*
	 * 刷新缓存
	 */
	function refresh($forumIds){
		if(!is_array($forumIds)){
			return false;
		}
		$threadList = $this->_getThreadList();
		foreach($forumIds as $forumId){
			$threadList->refreshThreadIdsByForumId($forumId);
		}
		return true;
	}
	
	/*
	 * 清除缓存
	 */
	function clear($forumIds){
		if(!is_array($forumIds)){
			return false;
		}
		$threadList = $this->_getThreadList();
		foreach($forumIds as $forumId){
			$threadList->clearThreadIdsByForumId($forumId);
		}
	}
	
	/*
	 * 清除全部缓存
	 */
	function flush(){
		$memcache = L::loadClass("memcache", 'utility');
		$memcache->flush();
	}
	
	function _getThreadList(){
		$threadlist =  L::loadClass("threadlist", 'forum');
		return $threadlist;
	}
	
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

?>
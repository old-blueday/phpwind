<?php
if (isset($_GET['ajax'])) {
	define('AJAX','1');
}
require_once('global.php');
$groupid == 'guest' && Showmsg('not_login');
S::gp(array('action'),'GP',0);
S::gp(array('atc_content'),'P');

if (!in_array($action, array('banuser', 'banfree', 'delatc', 'topped', 'shield', 'remind', 'commend', 'check', 'inspect', 'pingcp', 'split', 'banuserip', 'viewip', 'bansignature'))) {
	Showmsg('undefined_action');
}

require_once(R_P.'require/forum.php');
require_once(R_P.'require/writelog.php');
require_once(R_P.'require/updateforum.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');
/****/
if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}
(!$foruminfo || $foruminfo['type'] == 'category') && Showmsg('data_error');
wind_forumcheck($foruminfo);

$isGM = S::inArray($windid,$manager);
$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
if (!$isGM) {
	switch ($action) {
		case 'delatc' :
			$admincheck = pwRights($isBM,'modother');
			break;
		case 'check' :
			$admincheck = pwRights($isBM,'tpccheck');
			break;
		case 'commend' :
			$admincheck = $isBM;
			break;
		case 'banuser' :
		case 'banfree' :
			//$admincheck = pwRights($isBM,'banuser');
			$admincheck = $SYSTEM['banuser']>0?true:false;
			break;
		case 'shield' :
			$admincheck = pwRights($isBM,'shield');
			break;
		case 'remind' :
			$admincheck = pwRights($isBM,'remind');
			break;
		case 'split' :
			$admincheck = pwRights($isBM,'split'); //拆分
			break;
		case 'inspect' :
			$admincheck = pwRights($isBM,'inspect');
			break;
		case 'banuserip' :
		case 'viewip':
			$admincheck = pwRights($isBM,'banuserip');
			break;
		case 'bansignature' :
			$admincheck = pwRights($isBM,'bansignature');
			break;
		case 'pingcp' :
			$admincheck = pwRights($isBM,'pingcp');
			break;
		case 'topped' :
			$admincheck = pwRights($isBM,'replaytopped');
			break;
		default :
			$admincheck = false;
	}
	!$admincheck && Showmsg('mawhole_right');
}
$secondurl = "thread.php?fid=$fid";
$template = 'ajax_masingle';

if (empty($_POST['step']) && !defined('AJAX')) {
	require_once(R_P.'require/header.php');
	$template = 'masingle';
} elseif ($_POST['step'] == '3') {
	if ($SYSTEM['enterreason'] && !$atc_content) {
		Showmsg('enterreason');
	}
}

if ($action == "topped") {
	S::gp(array('step'),'GP');
	if (empty($step)) {
		//增加置顶
		S::gp(array('tid','pid','lou','page'),'GP', 2);
		if (empty($pid) || empty($tid) || empty($lou)) {
			Showmsg('undefined_action');
		}
		$remindinfo = getLangInfo('bbscode','read_topped')."\t".addslashes($windid)."\t".$timestamp;
		$pw_posts = GetPtable('N',$tid);
		$pw_poststop = array('fid'=>'0','tid'=>$tid,'pid'=>$pid,'floor'=>$lou,'uptime'=>$timestamp,'overtime'=>'');
		$db->update("REPLACE INTO pw_poststopped SET " . S::sqlSingle($pw_poststop));
		//$db->update("UPDATE $pw_posts SET remindinfo = ".S::sqlEscape($remindinfo)." WHERE tid=$tid AND pid=$pid");
		pwQuery::update($pw_posts, 'tid=:tid AND pid=:pid', array($tid, $pid), array('remindinfo' => $remindinfo));
	}elseif($step == '2'){
		//删除置顶
		S::gp(array('tid','pid','page'),'GP', 2);
		if (empty($pid) || empty($tid)) {
			Showmsg('undefined_action');
		}
		$remindinfo = getLangInfo('bbscode','read_deltopped')."\t".addslashes($windid)."\t".$timestamp;
		$pw_posts = GetPtable('N',$tid);
		//$db->update("UPDATE $pw_posts SET remindinfo = '' WHERE tid= ".S::sqlEscape($tid)." AND pid= " . S::sqlEscape($pid));
		pwQuery::update($pw_posts, 'tid=:tid AND pid=:pid', array($tid, $pid), array('remindinfo' => ''));
		$db->update("DELETE FROM pw_poststopped WHERE tid = ".S::sqlEscape($tid)." AND pid = " . S::sqlEscape($pid));
	}
	$t_count = $db->get_value("SELECT COUNT(*) AS count FROM pw_poststopped WHERE tid = " . S::sqlEscape($tid) . " AND fid = '0' ");
	$t_count = intval($t_count);
	//$db->update("UPDATE pw_threads SET topreplays=".S::sqlEscape($t_count,false)." WHERE tid=" . S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('topreplays'=>$t_count));
	//* $threads = L::loadClass('Threads', 'forum');
	//* $threads->delThreads($tid);
	if (defined('AJAX')) {
		Showmsg('ajaxma_success');
	} else {
		refreshto("read.php?tid={$tid}&displayMode=1&page=$page",'operate_success');
	}

} elseif ($action == "banuser") {

	S::gp(array('uid','tid','page'),'GP',2);
	(!$tid || !$uid) && Showmsg('masingle_data_error');

	if (empty($_POST['step'])) {

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userinfo = $userService->get($uid);
		if(!in_array($userinfo['groupid'],array(-1,6)) || $userinfo['groups']){
			//* include_once pwCache::getPath(D_P.'data/bbscache/cache_read.php');
			pwCache::getData(D_P.'data/bbscache/cache_read.php');
			$showTips = true;
			$userLevel = $ltitle[$userinfo['groupid']];
			empty($userLevel) && $userLevel = $ltitle[$userinfo['memberid']];
			if($userinfo['groups']){
				$otherGroups = array();
				foreach(explode(',',$userinfo['groups']) as $v){
					$v = intval(trim($v));
					$v > 0 && isset($ltitle[$v]) && $otherGroups[] = $ltitle[$v];
				}
				$otherGroups = implode(' , ',$otherGroups);
			}
		}
		$reason_sel = '';
		$reason_a	= explode("\n",$db_adminreason);
		foreach ($reason_a as $k=>$v) {
			if ($v = trim($v)) {
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else {
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		$pwBanMax = $SYSTEM['banmax'];
		require_once PrintEot($template);footer();

	} else {

		S::gp(array('limit','type','range','ifmsg','banip'),'P',2);
		$banuserService = L::loadClass('BanUser', 'user'); /* @var $banuserService PW_BanUser */
		$params = array(
			'tid' => $tid,
			'limit' => $limit,
			'type' => $type,
			'banip' => $banip,
			'range' => $range,
			'ifmsg' => $ifmsg,
			'reason' => $atc_content
		);
		$return = $banuserService->ban($uid,$params);
		$return !== true && Showmsg($return);
		if (defined('AJAX')) {
			Showmsg('ajax_banuser_success');
		} else {
			refreshto("read.php?tid={$tid}&displayMode=1&page=$page",'operate_success');
		}
	}
} elseif ($action == "banfree") {

	S::gp(array('pid','uid','fid','tid','page'),'GP',2);
	$SYSTEM['banuser'] or Showmsg('banuser_no_banright');
	(!$tid || !$fid) && Showmsg('masingle_data_error');
	if (empty($_POST['step'])) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userinfo = $userService->get($uid);
		require_once PrintEot($template);footer();
	} else {
		S::gp(array('ifmsg'),'P');
		$banuserService = L::loadClass('BanUser', 'user'); /* @var $banuserService PW_BanUser */
		$params = array(
			'fid' => intval($fid),
			'tid' => intval($tid),
			'ifmsg' => $ifmsg == 1?$ifmsg:0,
			'userip' => $userip,
			'reason' => $atc_content,
		);
		$banuserService->banfree($uid,$params);
		if (defined('AJAX')) {
			Showmsg('ajax_banfree_success');
		} else {
			refreshto("read.php?tid={$tid}&displayMode=1&page=$page",'operate_success');
		}
	}
} elseif ($action == "shield") {

	S::gp(array('pid','page'),'GP',2);
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	$sqlsel = $sqltab = $sqladd = '';
	if (is_numeric($pid)) {
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.fid,t.subject,t.content,t.postdate,t.ifshield,t.anonymous,t.authorid,";
		$sqltab = "$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd = " AND t.pid='$pid'";
	} else {
		$sqlsel = "t.fid,t.subject,t.postdate,t.ifshield,t.anonymous,";
		$sqltab = "pw_threads t LEFT JOIN pw_members m ON t.authorid=m.uid";
	}

	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid=".S::sqlEscape($tid).$sqladd);
	if (!$readdb || $readdb['fid'] <> $fid) {
		Showmsg('illegal_tid');
	}
	$readdb['ifshield'] == '2' && Showmsg('illegal_data');

	if ($winduid != $readdb['authorid']) {
		/**Begin modify by liaohu*/
		$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
		if (($readdb['groupid'] == 3 || $readdb['groupid'] == 4 || $readdb['groupid'] == 5) && !in_array($readdb['groupid'],$pce_arr)) {
			Showmsg('modify_admin');
		}
		/**End modify by liaohu*/
	}
	$readdb['subject'] = substrs($readdb['subject'],35);
	!$readb['subject'] && is_numeric($pid) && $readdb['subject'] = substrs($readdb['content'],35);

	if (empty($_POST['step'])) {

		$readdb['ifshield'] ? $check_N = 'checked' : $check_Y = 'checked';
		$readdb['postdate'] = get_date($readdb['postdate']);
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot($template);footer();

	} else {

		PostCheck();
		if ($_POST['step'] == 3) {
			$readdb['ifshield'] == 1 && Showmsg('read_shield');
			$ifshield = 1;
		} else {
			$readdb['ifshield'] == 0 && Showmsg('read_unshield');
			$ifshield = 0;
		}
		if (is_numeric($pid)) {
			//$db->update("UPDATE $pw_posts SET ifshield=".S::sqlEscape($ifshield).' WHERE pid='.S::sqlEscape($pid).' AND tid='.S::sqlEscape($tid));
			pwQuery::update($pw_posts, 'pid=:pid AND tid=:tid', array($pid, $tid), array('ifshield' => $ifshield));
		} else {
			//$db->update('UPDATE pw_threads SET ifshield='.S::sqlEscape($ifshield).' WHERE tid='.S::sqlEscape($tid));
			pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('ifshield'=>$ifshield));
			if ($db_ifpwcache ^ 1) {
				$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id=".S::sqlEscape($tid));
			}
			//* $threads = L::loadClass('Threads', 'forum');
			//* $threads->delThreads($tid);
		}
		if ($_POST['ifmsg']) {
			M::sendNotice(
				array($readdb['username']),
				array(
					'title' => getLangInfo('writemsg','shield_title_'.$ifshield),
					'content' => getLangInfo('writemsg','shield_content_'.$ifshield,array(
						'manager'	=> $windid,
						'fid'		=> $fid,
						'tid'		=> $tid,
						'subject'	=> $readdb['subject'],
						'postdate'	=> get_date($readdb['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					)),
				)
			);
		}
		if ($_POST['step'] == 3) {
			$log = array(
				'type'      => 'shield',
				'username1' => $readdb['username'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'shield_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'forum'		=> $forum[$fid]['name'],
				'subject'	=> substrs($readdb['subject'],28),
				'reason'	=> stripslashes($atc_content)
			);
			writelog($log);
		}
		if ($foruminfo['allowhtm'] && $page==1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($tid);
		}
		refreshto("read.php?tid=$tid&displayMode=1&page=$page",'operate_success');
	}
} elseif ($action == 'remind') {

	S::gp(array('pid','page'),'GP',2);
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	$sqlsel = $sqltab = $sqladd = '';
	if (is_numeric($pid)) {
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.fid,t.subject,t.content,t.postdate,t.remindinfo,t.anonymous,t.authorid,";
		$sqltab = "$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd = " AND t.pid='$pid'";
	} else {
		$pw_tmsgs = GetTtable($tid);
		$sqlsel = "t.fid,t.subject,t.postdate,t.anonymous,tm.remindinfo,";
		$sqltab = "pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON t.authorid=m.uid";
	}
	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid=".S::sqlEscape($tid).' AND t.fid='.S::sqlEscape($fid).$sqladd);
	if (!$readdb || $readdb['fid'] <> $fid) {
		Showmsg('illegal_tid');
	}

	if ($winduid != $readdb['authorid']) {
		/**Begin modify by liaohu*/
		$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
		if (($readdb['groupid'] == 3 || $readdb['groupid'] == 4 || $readdb['groupid'] == 5) && !in_array($readdb['groupid'],$pce_arr)) {
			Showmsg('modify_admin');
		}
		/**End modify by liaohu*/
	}
	$readdb['subject'] = substrs($readdb['subject'],35);
	!$readb['subject'] && is_numeric($pid) && $readdb['subject']=substrs($readdb['content'],35);

	if (empty($_POST['step'])) {

		$readdb['remindinfo'] ? $check_N = 'checked' : $check_Y = 'checked';
		$readdb['postdate'] = get_date($readdb['postdate']);
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		list($remindinfo)=explode("\t",$readdb['remindinfo']);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		$remindinfo = str_replace('&nbsp;',' ',$remindinfo);
		require_once PrintEot($template);footer();

	} elseif ($_POST['step'] == 3) {

		PostCheck();
		!$atc_content && Showmsg('remind_data_empty');

		$remindinfo = $atc_content."\t".addslashes($windid)."\t".$timestamp;
		if (strlen($remindinfo)>150) Showmsg('remind_length');
		if (is_numeric($pid)) {
			//$db->update("UPDATE $pw_posts SET remindinfo=".S::sqlEscape($remindinfo).' WHERE pid='.S::sqlEscape($pid).' AND tid='.S::sqlEscape($tid));
			pwQuery::update($pw_posts, 'pid=:pid AND tid=:tid', array($pid, $tid), array('remindinfo' => $remindinfo));
		} else {
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo=".S::sqlEscape($remindinfo).' WHERE tid='.S::sqlEscape($tid));
			pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('remindinfo'=>$remindinfo));
			//* $threads = L::loadClass('Threads', 'forum');
			//* $threads->delThreads($tid);
		}
		if ($_POST['ifmsg']) {
			M::sendNotice(
				array($readdb['username']),
				array(
					'title' => getLangInfo('writemsg','remind_title'),
					'content' => getLangInfo('writemsg','remind_content',array(
						'manager'	=> $windid,
						'fid'		=> $fid,
						'tid'		=> $tid,
						'subject'	=> $readdb['subject'],
						'postdate'	=> get_date($readdb['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					)),
				)
			);
		}
		$log = array(
			'type'      => 'remind',
			'username1' => $readdb['username'],
			'username2' => $windid,
			'field1'    => $fid,
			'field2'    => '',
			'field3'    => '',
			'descrip'   => 'remind_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'tid'		=> $tid,
			'forum'		=> $forum[$fid]['name'],
			'subject'	=> substrs($readdb['subject'],28),
			'reason'	=> stripslashes($atc_content)
		);
		writelog($log);
		if (defined('AJAX')) {
			echo "success\t".str_replace(array("\n","\t"),array('<br />',''),$atc_content)."\t$windid";ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$page",'operate_success');
		}
	} else {

		PostCheck();
		if (is_numeric($pid)) {
			//$db->update("UPDATE $pw_posts SET remindinfo='' WHERE pid=".S::sqlEscape($pid).' AND tid='.S::sqlEscape($tid));
			pwQuery::update($pw_posts, 'pid=:pid AND tid=:tid', array($pid, $tid), array('remindinfo' => ''));
		} else {
			//* $db->update("UPDATE $pw_tmsgs SET remindinfo='' WHERE tid=".S::sqlEscape($tid));
			pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('remindinfo'=>''));
		}
		if (defined('AJAX')) {
			echo "cancle\t";ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$page",'operate_success');
		}
	}
} elseif ($action == 'split') { //拆分帖子
	S::gp(array('fid','tid','page','selid','splittype','splitid','splittitle','ifmsg','atc_content'));
	$tid    = (int)$tid;
	$page 	= (int)$page;
	$fid 	= (int)$fid;
	empty($selid) && Showmsg('split_no_thread');

	if (!$_POST['step']) {
		$splitNum = count($selid); //选中数量
		$reason_sel = '';
		$reason_a	= explode("\n",$db_adminreason);
		foreach ($reason_a as $k => $v) {
			if ($v = trim($v)) {
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else {
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot($template);
		footer();

	} else {
		PostCheck();
		$splittype = (int)$splittype; //拆分类型
		$pids = array(); //拆分的id
		$lastPid = NULL; //拆分中最后一个帖子ID
		$firstPid = NULL; //拆分的第一个帖子ID
		foreach ($selid as $k => $v) {
			if(is_numeric($v)) {
				$pids[] = $v;
				if($v > $lastPid) $lastPid = $v;
				if($v < $firstPid) $firstPid = $v;
			}
		}
		$pidsNum = count($pids); //回复数
		$pingService = L::loadClass("ping", 'forum');
		//tucool
		$foruminfo = L::forum($fid);
		$istucool = $foruminfo['forumset']['iftucool'] && $foruminfo['forumset']['tucoolpic'];
		$istucool && $tucoolService = L::loadClass('Tucool','forum');
		if ($selid[0] == 'tpc') { //需要拆分主题帖子
			if ($splittype == 0) { //新帖操作

				//判断和获取数据
				$pw_tmsgs_tid = GetTtable($tid); //获取分表表名
				$pw_posts_tid = GetPtable('N',$tid); //post表
				if(strlen($splittitle) == 0) Showmsg('split_title_not_null');
				if(strlen($splittitle) > 100) Showmsg('split_title_not_big');
				$splitTopic = $db->get_one("SELECT * FROM pw_threads WHERE tid = ".S::sqlEscape($tid)); //pw_thread
				if(!$splitTopic) Showmsg("split_no_thread");
				$splitTopicMsg = $db->get_one("SELECT * FROM $pw_tmsgs_tid WHERE tid = ".S::sqlEscape($tid)); //pw_tmsg
				$splitNum = $splitTopic['replies'] + 1;
				if($pidsNum == $splitTopic['replies'])  Showmsg("split_not_all"); //不允许全部帖子拆分

				//拆到新帖子操作
				$lastNewThreadPost = $db->get_one("SELECT * FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($lastPid)); //新帖回复
				if($lastNewThreadPost !== false){
					$splitTopic['lastpost'] = $lastNewThreadPost['postdate'];
					$splitTopic['lastposter'] = $lastNewThreadPost['author'];
				}else{
					$splitTopic['lastpost'] = $splitTopic['postdate'];
					$splitTopic['lastposter'] = $splitTopic['author'];
				}
				$splitTopic['ptable']  = $db_ptable; //分表
				$splitTopic['subject'] = $splittitle;
				unset($splitTopic['tid']);
				$splitTopic['replies'] = $pidsNum; //回复数
				$db->update("INSERT INTO pw_threads SET " . S::sqlSingle($splitTopic));
				$newId = $db->insert_id();

				$pw_tmsgs = GetTtable($newId); //获取分表表名
				$pw_posts = GetPtable('N',$newId); //获取分表表名
				$splitTopicMsg['tid'] = $newId;
				if($splitTopicMsg) $db->update("INSERT INTO $pw_tmsgs SET " . S::sqlSingle($splitTopicMsg));
				$splitid = $newId;
				//如果有置顶的情况
				$forumdata = $db->get_one("SELECT * FROM pw_forumdata  WHERE fid = ".S::sqlEscape($splitTopic['fid'])); //获取板块信息
				if($forumdata['topthreads'] == ""){
					$forumdata['topthreads'] = $newId;
				}else{
					$forumdata['topthreads'] = $forumdata['topthreads'].','.$newId;
				}
				if($splitTopic['topped'] > 0){
					$postTopArr = array();
					$postTopArr['fid'] = $splitTopic['fid'];
					$postTopArr['tid'] = $newId;
					$postTopArr['uptime'] = 2;
					$postTopArr['floor'] = $splitTopic['topped'];
					$db->update("INSERT INTO pw_poststopped  SET " . S::sqlSingle($postTopArr));
					updatetop();
					//require_once(R_P.'admin/cache.php');
					//updatecache_forums($postTopArr['fid']);
					//$db->update("UPDATE pw_forumdata   SET  top1 = top1 + 1, topic  = topic + 1, topthreads = " . S::sqlEscape($forumdata['topthreads'])." WHERE fid  = " . S::sqlEscape($splitTopic['fid']));
				}
				
				//* $db->update("UPDATE pw_forumdata   SET  topic  = topic + 1 WHERE fid  = " . S::sqlEscape($splitTopic['fid']));
				$db->update(pwQuery::buildClause("UPDATE :pw_table SET topic=topic+1 WHERE fid=:fid", array('pw_forumdata', $splitTopic['fid'])));

				//回复处理
				$pidsStr  = S::sqlImplode($pids);
				if($pw_posts == $pw_posts_tid){ //如果回复数在同一post表
					if($pidsStr) $db->query("UPDATE $pw_posts_tid SET tid = ".S::sqlEscape($splitid)." WHERE  pid in ($pidsStr)");	//合并操作
				}else{ //临界情况 回复分布在不同的post表中
					if($pidsStr){
						$db->query("INSERT INTO $pw_posts (tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo) (SELECT tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo FROM $pw_posts_tid as a WHERE a.pid in ($pidsStr))");
						$db->query("UPDATE $pw_posts SET tid = ".S::sqlEscape($splitid)." WHERE  tid =  ".S::sqlEscape($tid));
					}
				}

				//被拆帖子操作
				$newTopic = $db->get_one("SELECT * FROM $pw_posts_tid WHERE tid = ".S::sqlEscape($tid)." ORDER BY pid ASC LIMIT 1"); //主题帖
				$lastPost = $db->get_one("SELECT * FROM $pw_posts_tid WHERE tid = ".S::sqlEscape($tid)." ORDER BY pid DESC LIMIT 1");
				$newTopicInfo = array(
					'fid'       => $fid,
					'author'    => $newTopic['author'],
					'authorid'  => $newTopic['authorid'],
					'postdate'  => $newTopic['postdate'],
					'lastpost'  => $lastPost['postdate'],
					'lastposter'=> $lastPost['author']
				);
				$pidsNum = $pidsNum + 1; //一个回复变主题
				$db->update("UPDATE pw_threads SET " . S::sqlSingle($newTopicInfo) . " , replies = replies - $pidsNum  WHERE tid = ".S::sqlEscape($tid));
				Perf::gatherInfo('changeThreads', array('tid'=>$tid));
				$pwtmsgInfoMsg = array(
					'userip'   =>  $newTopic['userip'],
					'content'	 =>$newTopic['content'],
					'ipfrom'   =>  $newTopic['ipfrom'],
					'ifsign'   =>  1,
					'ifconvert'=>  2,
					'ifwordsfb'=>  1,
					'tid'      =>  $tid
				);
				//* $db->update("UPDATE $pw_tmsgs_tid SET " . S::sqlSingle($pwtmsgInfoMsg). " WHERE tid = ".S::sqlEscape($tid));
				pwQuery::update($pw_tmsgs_tid, 'tid=:tid', array($tid), $pwtmsgInfoMsg);
				$db->query("DELETE  FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($newTopic['pid']));  //删除成为主题的回复

				//评分操作
				$db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid = 0");
				if($pidsStr) $db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				$db->update("UPDATE pw_pinglog SET  pid = 0 WHERE tid = ".S::sqlEscape($tid)." AND  pid =  ".S::sqlEscape($newTopic['pid'])." ");
				$pingService->update_markinfo($fid, $tid, 0);
				$pingService->update_markinfo($fid, $splitid, 0);

				//附件操作
				$db->query("UPDATE  pw_attachs SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid = 0");
				if($pidsStr) $db->update("UPDATE pw_attachs SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				$db->update("UPDATE pw_attachs SET  pid = 0 WHERE tid = ".S::sqlEscape($tid)." AND  pid =  ".S::sqlEscape($newTopic['pid'])." ");
			} else { //合并帖子操作
				//判断和获取数据
				(!$splitid) &&  Showmsg('split_no_splitid');
				$splitid = (int)$splitid;
				if($tid == $splitid)  Showmsg('split_no_common_thread');
				$result = $db->get_one("SELECT tid,fid,postdate,author FROM pw_threads WHERE tid = ".S::sqlEscape($splitid));
				if(!$result) Showmsg('split_is_no_splitthread');
				$splitTopic = $db->get_one("SELECT * FROM pw_threads WHERE tid = ".S::sqlEscape($tid)); //pw_thread
				$splitNum = $splitTopic['replies'] + 1;
				if($pidsNum == $splitTopic['replies'])  Showmsg("split_not_all"); //不允许全部帖子拆分

				//拆分
				$pw_tmsgs = GetTtable($tid);
				$pw_posts_tid = GetPtable('N',$tid); //post表
				$pw_posts = GetPtable('N',$splitid); //post表 拆分到的表
				$splitTopicInfo = $db->get_one("SELECT * FROM pw_threads WHERE tid = ".S::sqlEscape($tid));
				$splitTopicMsg = $db->get_one("SELECT * FROM $pw_tmsgs WHERE tid = ".S::sqlEscape($tid));
				$postInfo = array(
					'tid' => $splitid,
					'fid' => $fid,
					'author' =>$splitTopicInfo['author'],
					'authorid' =>$splitTopicInfo['authorid'],
					'postdate' =>$splitTopicInfo['postdate'],
					'userip' =>$splitTopicMsg['userip'],
					'ipfrom'=>$splitTopicMsg['ipfrom'],
					'content'=>$splitTopicMsg['content'],
					'ifmark'=>$splitTopicMsg['ifmark'],
					'ifconvert'=>$splitTopicMsg['ifconvert'],
					'ifwordsfb'=>$splitTopicMsg['ifwordsfb'],
					'ifsign'=>$splitTopicMsg['ifsign'],
					'ifcheck'=>$splitTopicInfo['ifcheck'],
					'remindinfo'=>$splitTopicMsg['remindinfo']
				);
				$db->update("INSERT INTO $pw_posts SET " . S::sqlSingle($postInfo));
				$postNewId = $db->insert_id();

				//回复
				$pidsStr  = S::sqlImplode($pids);
				if($pw_posts == $pw_posts_tid){ //如果回复数在同一post表
					if($pidsStr) $db->query("UPDATE $pw_posts_tid SET tid = ".S::sqlEscape($splitid)." WHERE  pid in ($pidsStr)");	//合并操作
				}else{ //临界情况 回复分布在不同的post表中
					if($pidStr){
						$db->query("INSERT INTO $pw_posts (tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo) (SELECT tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo FROM $pw_posts_tid as a WHERE a.pid in ($pidsStr))");
						$db->query("UPDATE $pw_posts SET tid = ".S::sqlEscape($splitid)." WHERE  tid =  ".S::sqlEscape($tid));
					}
				}

				//拆分新帖数据更新操作
				$lastPost = $db->get_one("SELECT * FROM $pw_posts WHERE tid = ".S::sqlEscape($splitid)." ORDER BY pid DESC LIMIT 1"); //最后回复帖
				$pidsNumSplit = $pidsNum + 1;
				$db->query("UPDATE pw_threads SET replies = replies + $pidsNumSplit , lastpost = ".S::sqlEscape($lastPost['postdate'])." , lastposter = ".S::sqlEscape($lastPost['author'])." WHERE  tid = ".S::sqlEscape($splitid));	//回复
				Perf::gatherInfo('changeThreads', array('tid'=>$splitid));

				//被拆分的数据更新
				$postInfo   = $db->get_one("SELECT * FROM $pw_posts_tid WHERE tid = ".S::sqlEscape($tid)." ORDER BY pid ASC LIMIT 1");
				if($postInfo) {
					$threadInfo = array(
						'fid'       => $fid,
						'author'    => $postInfo['author'],
						'authorid'  => $postInfo['authorid'],
						'ifcheck'   => $postInfo['ifcheck'],
						'postdate'  => $postInfo['postdate'],
						'lastpost'  => $postInfo['postdate'],
						'lastposter'=> $postInfo['author']
					);
					$db->update("UPDATE pw_threads SET " . S::sqlSingle($threadInfo)." WHERE tid = ".S::sqlEscape($tid));
					Perf::gatherInfo('changeThreads', array('tid'=>$tid));
					$pw_tmsgs = GetTtable($tid); //获取分表表名
					$pwtmsgInfo = array(
				  		'userip'   =>  $postInfo['userip'],
						'content'  =>  $postInfo['content'],
				 		'ipfrom'   =>  $postInfo['ipfrom'],
				  		'ifsign'   =>  $postInfo['ifsign'],
				  		'ifconvert'=>  $postInfo['ifconvert'],
						'aid'      =>  $postInfo['aid'],
				  		'ifwordsfb'=>  $postInfo['ifwordsfb']
					);
					//* $db->update("UPDATE $pw_tmsgs SET " . S::sqlSingle($pwtmsgInfo). " WHERE tid = ".S::sqlEscape($tid));
					pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), $pwtmsgInfo);
				}
				$lastPostT = $db->get_one("SELECT * FROM $pw_posts_tid WHERE tid = ".S::sqlEscape($tid)." ORDER BY pid DESC LIMIT 1"); //最后回复帖
				$pidsNumT = $pidsNum + 1;
				if($lastPostT !== false){
					$db->query("UPDATE pw_threads SET replies = replies - $pidsNumT , lastpost = ".S::sqlEscape($lastPostT['postdate'])." , lastposter = ".S::sqlEscape($lastPostT['author'])." WHERE  tid = ".S::sqlEscape($tid));	//回复
				} else {
					$db->query("UPDATE pw_threads SET replies = replies - $pidsNumT , lastpost = ".S::sqlEscape($postInfo['postdate'])." , lastposter = ".S::sqlEscape($postInfo['author'])." WHERE  tid = ".S::sqlEscape($tid));	//回复
				}
				Perf::gatherInfo('changeThreads', array('tid'=>$tid));
				$db->query("DELETE FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($postInfo['pid']));

				//评分操作
				$db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." , pid = ".S::sqlEscape($postNewId)." WHERE tid = ".S::sqlEscape($tid)." AND  pid = 0");
				if($pidsStr) $db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				$db->update("UPDATE pw_pinglog SET  pid = 0 WHERE tid = ".S::sqlEscape($tid)." AND  pid =  ".S::sqlEscape($postInfo['pid'])." ");
				$pingService->update_markinfo($fid, $tid, 0);
				$pingService->update_markinfo($fid, $splitid, 0);

				//附件操作
				$db->query("UPDATE  pw_attachs SET tid = ".S::sqlEscape($splitid)."  , pid = ".S::sqlEscape($postNewId)." WHERE tid = ".S::sqlEscape($tid)." AND  pid = 0");
				if($pidsStr) $db->update("UPDATE pw_attachs SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				$db->update("UPDATE pw_attachs SET  pid = 0 WHERE tid = ".S::sqlEscape($tid)." AND  pid =  ".S::sqlEscape($postInfo['pid'])." ");
				$fjInfo = $db->get_one("SELECT COUNT(*) as a  FROM pw_attachs WHERE tid = ".S::sqlEscape($splitid)." AND pid = ".S::sqlEscape($postNewId)." limit 1");
				if($fjInfo) $db->update("UPDATE $pw_posts SET  aid = ".S::sqlEscape($fjInfo['a'])." WHERE  pid =  ".S::sqlEscape($postNewId)." ");
			}

			//如果分表 删除原表中的回复
			if($pw_posts !== $pw_posts_tid){
				if($pidsStr){
					$db->query("DELETE  FROM $pw_posts_tid WHERE pid  in ($pidsStr)");
				}
			}

		} else { //不需要拆分主题帖子

			$pw_posts_tid = GetPtable('N',$tid); //post表

			if ($splittype == 0) { //新帖操作

				if(strlen($splittitle) == 0) Showmsg('split_title_not_null');
				if(strlen($splittitle) > 100) Showmsg('split_title_not_big');

				//拆分
				$threadId   = $pids[0];
				$postInfo   = $db->get_one("SELECT * FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($threadId));
				$threadInfo = array(
					'fid'       => $fid,
					'author'    => $postInfo['author'],
					'authorid'  => $postInfo['authorid'],
					'subject'   => $splittitle,
					'ifcheck'   => $postInfo['ifcheck'],
					'postdate'  => $postInfo['postdate'],
					'lastpost'  => $postInfo['postdate'],
					'ptable'    => $db_ptable,//分表
					'lastposter'=> $postInfo['author']
				);
				$db->update("INSERT INTO pw_threads SET " . S::sqlSingle($threadInfo));
				$newId = $db->insert_id();
				$pw_tmsgs = GetTtable($newId); //获取分表表名
				$pwtmsgInfo = array(
				  	'userip'   =>  $postInfo['userip'],
					'content'  =>  $postInfo['content'],
				 	'ipfrom'   =>  $postInfo['ipfrom'],
			  		'ifsign'   =>  $postInfo['ifsign'],
				  	'ifconvert'=>  $postInfo['ifconvert'],
				  	'ifwordsfb'=>  $postInfo['ifwordsfb'],
				  	'tid'      =>  $newId
				);
				$db->update("INSERT INTO $pw_tmsgs SET " . S::sqlSingle($pwtmsgInfo));
				$db->query("DELETE  FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($threadId));
				$splitid = $newId;
				//更新板块数据表
				//* $db->update("UPDATE pw_forumdata   SET  topic  = topic + 1  WHERE fid  = " . S::sqlEscape($fid));
				$db->update(pwQuery::buildClause("UPDATE :pw_table SET topic=topic+1 WHERE fid=:fid", array('pw_forumdata', $fid)));

			} else { //合并帖子操作
				(!$splitid) &&  Showmsg('split_no_splitid');
				$splitid = (int)$splitid;
				if($tid == $splitid)  Showmsg('split_no_common_thread');
				$result = $db->get_one("SELECT tid,fid,postdate,author FROM pw_threads WHERE tid = ".S::sqlEscape($splitid));
				if(!$result) Showmsg('split_is_no_splitthread');
			}

			$splitThread = $db->get_one("SELECT tid,fid,postdate,author,subject  FROM pw_threads WHERE tid = ".S::sqlEscape($tid));
			$splitTopic = $splitThread;
			$pw_posts = GetPtable('N',$splitid);

			if ($splitid) {

				//最后发帖和时间
				$lastPostInfo = $db->get_one("SELECT * FROM $pw_posts_tid WHERE pid = ".S::sqlEscape($lastPid));
				$oldLastPostInfo = $db->get_one("SELECT * FROM $pw_posts_tid WHERE tid = ".S::sqlEscape($tid)." ORDER BY pid DESC limit 1");

				//回复操作
				$pidsStr  = S::sqlImplode($pids);
				if($pw_posts == $pw_posts_tid){ //如果回复数在同一post表
					if($pidsStr) $db->query("UPDATE $pw_posts_tid SET tid = ".S::sqlEscape($splitid)." WHERE  pid in ($pidsStr)");	//合并操作
				}else{ //临界情况 回复分布在不同的post表中
					if($pidsStr){
						$db->query("INSERT INTO $pw_posts (tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo) (SELECT tid,author,authorid,postdate,userip,ipfrom,content,ifmark,ifconvert,ifwordsfb,ifsign,ifcheck,remindinfo FROM $pw_posts_tid as a WHERE a.pid in ($pidsStr))");
						$db->query("UPDATE $pw_posts SET tid = ".S::sqlEscape($splitid)." WHERE  tid =  ".S::sqlEscape($tid));
						$db->query("DELETE  FROM $pw_posts_tid WHERE pid  in ($pidsStr)");
					}
				}

				//被拆帖子
				$pidNum = count($pids); //回复数
				if ($oldLastPostInfo) {
					$db->query("UPDATE pw_threads SET replies = replies - $pidNum,lastpost = ".S::sqlEscape($oldLastPostInfo['postdate']).",lastposter = ".S::sqlEscape($oldLastPostInfo['author'])." WHERE  tid = ".S::sqlEscape($tid));
				} else {
					$db->query("UPDATE pw_threads SET replies = replies - $pidNum,lastpost = ".S::sqlEscape($splitThread['postdate']).",lastposter = ".S::sqlEscape($splitThread['author'])." WHERE  tid = ".S::sqlEscape($tid));
				}
				Perf::gatherInfo('changeThreads', array('tid'=>$tid));

				//拆到的帖子
				if($splittype == 0 && $pidNum > 1) $pidNum = $pidNum - 1;
				if($lastPostInfo){
					$db->query("UPDATE pw_threads SET replies = replies + $pidNum,lastpost = ".S::sqlEscape($lastPostInfo['postdate']).",lastposter = ".S::sqlEscape($lastPostInfo['author'])." WHERE  tid = ".S::sqlEscape($splitid));	//拆到 回复数 最后发帖人
				}
				Perf::gatherInfo('changeThreads', array('tid'=>$splitid));

				//评分操作
				if($pidsStr) $db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				if ($splittype == 0) $db->update("UPDATE pw_pinglog SET tid = ".S::sqlEscape($splitid)." , pid = 0 WHERE tid = ".S::sqlEscape($splitid)." AND  pid = ".S::sqlEscape($postInfo['pid']));
				$pingService->update_markinfo($fid, $tid, 0);
				$pingService->update_markinfo($fid, $splitid, 0);

				//附件操作
				if($pidsStr) $db->update("UPDATE pw_attachs SET tid = ".S::sqlEscape($splitid)." WHERE tid = ".S::sqlEscape($tid)." AND  pid in ($pidsStr)");
				if ($splittype == 0) {
					$db->update("UPDATE pw_attachs SET  pid = 0 , tid = ".S::sqlEscape($splitid)."  WHERE tid = ".S::sqlEscape($splitid)." AND  pid =  ".S::sqlEscape($postInfo['pid'])." ");
					$fjInfo = $db->get_one("SELECT COUNT(*) as a FROM pw_attachs WHERE tid = ".S::sqlEscape($splitid)." AND pid = 0 ");
					//* $fjInfo && $db->update("UPDATE $pw_tmsgs SET  aid = ".S::sqlEscape($fjInfo['a'])." WHERE  tid =  ".S::sqlEscape($splitid)." ");
					$fjInfo && pwQuery::update($pw_tmsgs, 'tid=:tid', array($splitid), array('aid'=>$fjInfo['a']));
				}

		    }
	     }
		//tucool
		if ($istucool) {
			$tucoolService->updateTucoolImageNum($tid);
			$tucoolService->updateTucoolImageNum($splitid);
		}

		//通知
		if ($ifmsg) {
				M::sendNotice(
					array($splitThread['author']),
					array(
						'title' => getLangInfo('writemsg','split_title'),
						'content' => getLangInfo('writemsg','split_content',array(
							'msg'	=> $atc_content,
							'spiltInfo' => "<a href=\"read.php?tid=".$tid."\">".$splitTopic['subject']."</a>",
						)),
					)
				);
		}

		$refreshto = "read.php?tid=$tid&displayMode=1&page=$pahe";
		if (defined('AJAX')) {
			Showmsg("ajaxma_success");
		} else {
			refreshto($refreshto,'operate_success');
		}

	}
} elseif ($action == 'delatc') {

	S::gp(array('selid'));
	empty($selid) && Showmsg('mawhole_nodata');
	$pw_tmsgs = GetTtable($tid);
	$tpcdb = $db->get_one("SELECT t.tid,t.fid,t.author,t.authorid,t.postdate,t.subject,t.topped,t.anonymous,t.ifshield,t.ptable,t.ifcheck,tm.aid FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid'");
	if (!$tpcdb || $tpcdb['fid'] != $fid) {
		Showmsg('undefined_action');
	}
	$pw_posts = GetPtable($tpcdb['ptable']);
	$deltpc = '';
	$pids = array();
	foreach ($selid as $k => $v) {
		if ($v == 'tpc') {
			$deltpc = '1';
		} elseif (is_numeric($v)) {
			$pids[] = $v;
		}
	}
	$threaddb = array();
	if ($deltpc) {
		$tpcdb['ifshield'] == '2' && Showmsg('illegal_data');
		if ($winduid != $tpcdb['authorid']) {
			$authordb = $db->get_one('SELECT groupid FROM pw_members WHERE uid='.S::sqlEscape($tpcdb['authorid']));
			/**Begin modify by liaohu*/
			$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
			if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
				Showmsg('modify_admin');
			}
			/**End modify by liaohu*/
		}
		$tpcdb['pid'] = 'tpc';
		$tpcdb['postdate'] = get_date($tpcdb['postdate']);
		$threaddb[] = $tpcdb;
	}
	if ($pids) {
		$pids  = S::sqlImplode($pids);
		$query = $db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,ifcheck FROM $pw_posts WHERE tid='$tid' AND fid='$fid' AND pid IN($pids)");
		while ($rt = $db->fetch_array($query)) {
			if ($winduid != $rt['authorid']) {
				$authordb = $db->get_one('SELECT groupid FROM pw_members WHERE uid='.S::sqlEscape($rt['authorid']));
				/**Begin modify by liaohu*/
				$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
				if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
					Showmsg('modify_admin');
				}
				/**End modify by liaohu*/
			}
			if (!$rt['subject']) {
				$rt['subject'] = substrs($rt['content'],35);
			}
			$rt['postdate'] = get_date($rt['postdate']);
			$rt['ptable'] = $tpcdb['ptable'];
			$threaddb[] = $rt;
		}
	}
	if (empty($_POST['step'])) {

		$reason_sel = '';
		$reason_a	= explode("\n",$db_adminreason);
		foreach ($reason_a as $k => $v) {
			if ($v = trim($v)) {
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else {
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		$count = count($threaddb);
		require_once PrintEot($template);footer();

	} else {

		PostCheck();
		S::gp(array('ifdel','ifmsg'),'P');
		require_once(R_P.'require/credit.php');
		$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);

		foreach ($threaddb as $key => $val) {
			if ($val['pid'] != 'tpc' && !$val['subject']) {
				$val['subject'] = $val['content'];
			}
			$istpc = ($val['pid'] == 'tpc');

			if ($ifdel) {
				$d_key = $istpc ? 'Delete' : 'Deleterp';
				$msg_delrvrc  = abs($creditset[$d_key]['rvrc']);
				$msg_delmoney = abs($creditset[$d_key]['money']);
			} else {
				$msg_delrvrc  = $msg_delmoney = 0;
			}
			if ($ifmsg) {
				$msg_delrvrc && $msg_delrvrc="-{$msg_delrvrc}";
				$msg_delmoney && $msg_delmoney="-{$msg_delmoney}";
				M::sendNotice(
					array($val['author']),
					array(
						'title' => getLangInfo('writemsg',$istpc ? 'deltpc_title' : 'delrp_title'),
						'content' => getLangInfo('writemsg',$istpc ? 'deltpc_content' : 'delrp_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $tid,
							'subject'	=> substrs($val['subject'],28),
							'postdate'	=> $val['postdate'],
							'forum'		=> strip_tags($forum[$fid]['name']),
							'affect'	=> "{$db_rvrcname}：{$msg_delrvrc}，{$db_moneyname}：{$msg_delmoney}",
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						)),
					)
				);
			}
		}

		/*更新图酷*/
		$tucoolService = L::loadClass('tucool', 'forum');
		$tucoolService->updateTucoolImageNum($tid);
		
		$refreshto = "read.php?tid=$tid&displayMode=1";
		$delarticle = L::loadClass('DelArticle', 'forum');

		if ($delarticle->delReply($threaddb, $db_recycle, $ifdel, true, array('reason' => $atc_content))) {
			$refreshto = "thread.php?fid=$fid";
		}
		if ($tpcdb['topped'] && $deltpc) {
			updatetop();
		}
		if ($foruminfo['allowhtm']) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($tid);
		}
		if (defined('AJAX')) {
			Showmsg(($deltpc && !$replies) ? "ajax_operate_success" : "ajaxma_success");
		} else {
			refreshto($refreshto,'operate_success');
		}
	}
} elseif ($action == 'commend') {

	PostCheck();
	$forumset = $foruminfo['forumset'];
	if (!$forumset['commend']) {
		Showmsg('commend_close');
	}
	if (strpos(",$forumset[commendlist],",",$tid,")!==false) {
		Showmsg('commend_exists');
	}
	$count = count(explode(',',$forumset['commendlist']));

	if ($forumset['commendnum'] && $count >= $forumset['commendnum']) {
		Showmsg('commendnum_limit');
	}
	$forumset['commendlist'] .= ($forumset['commendlist'] ? ',' : '').$tid;

	updatecommend($fid,$forumset);
	Showmsg('operate_success');

} elseif ($action == 'check') {

	PostCheck();
	//$db->update("UPDATE pw_threads SET ifcheck='1' WHERE tid=".S::sqlEscape($tid)."AND fid=".S::sqlEscape($fid)."AND ifcheck='0'");
	pwQuery::update('pw_threads', 'tid=:tid AND fid=:fid AND ifcheck=:ifcheck', array($tid,$fid,0), array('ifcheck'=>1));
    //* $threadList = L::loadClass("threadlist", 'forum');
    //* $threadList->refreshThreadIdsByForumId($fid);
	//* Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
	if ($db->affected_rows() > 0) {
		$rt = $db->get_one("SELECT tid,author,postdate,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid)."AND ifcheck='1' AND topped='0' ORDER BY lastpost DESC LIMIT 1");
		$lastpost = $rt['subject']."\t".$rt['author']."\t".$rt['postdate']."\t"."read.php?tid=$rt[tid]&displayMode=1&page=e#a";
		//* $db->update("UPDATE pw_forumdata SET topic=topic+'1',article=article+'1',tpost=tpost+'1',lastpost=".S::sqlEscape($lastpost,false)." WHERE fid='$fid'");
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET topic=topic+'1',article=article+'1',tpost=tpost+'1',lastpost=:lastpost WHERE fid=:fid", array('pw_forumdata', $lastpost, $fid)));
	}
	Showmsg('operate_success');

} elseif ($action == 'inspect') {
	$forumset = $foruminfo['forumset'];
	if (empty($forumset['inspect'])) {
		Showmsg('undefined_action');
	}
	S::gp(array('pid','page','p','nextto'));
	$pid = (int)$pid;
	$page = (int)$page;
	if (!empty($foruminfo['t_type']) && ($isGM || pwRights($isBM,'tpctype'))) {
		$iftypeavailable = 1;
	}
	$rt = $db->get_one('SELECT inspect FROM pw_threads WHERE tid='.S::sqlEscape($tid) . " AND fid=".S::sqlEscape($fid));
	empty($rt) && Showmsg('undefined_action');
	list($lou) = explode("\t",$rt['inspect']);
	($pid >= intval($lou)) && $lou = $pid;
	$inspect = $lou."\t".addslashes($windid);
	//$db->update('UPDATE pw_threads SET inspect='.S::sqlEscape($inspect).' WHERE tid='.S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('inspect'=>$inspect));	
	delfcache($fid,$db_fcachenum);
	if (!empty($nextto)) {
		if (!defined('AJAX')) {
			refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$tid",'operate_success');
		} else {
			$selids = $tid;
			Showmsg('ajax_nextto');
		}
	} else {
		refreshto("read.php?tid=$tid&displayMode=1&page=$page#$p",'operate_success');
	}

} elseif ($action == 'pingcp') {

	S::gp(array('pid'));
	S::gp(array('rpage','page'), 'GP', 2);

	if (empty($_POST['step'])) {

		$page < 1 && $page = 1;
		$db_perpage = 10;
		$sqlwhere = ' WHERE 1 AND ifhide=0';
		if (!is_numeric($pid)) {
			$sqlwhere .= " AND pid=0 AND tid=".S::sqlEscape($tid);
		} else {
			$sqlwhere .= " AND pid=".S::sqlEscape($pid);
		}
		if (!$total = $db->get_value("SELECT COUNT(*) AS count FROM pw_pinglog $sqlwhere")) {
			Showmsg('masigle_noping');
		}
		$creditnames = pwCreditNames();
		$pages = numofpage($total, $page, ceil($total/$db_perpage), "masingle.php?action=$action&fid=$fid&tid=$tid&pid=$pid&rpage=$rpage&", null, defined('AJAX') ? 'ajaxViewPingcp' : '');
		$query = $db->query("SELECT id,name,point,pinger,record,pingdate FROM pw_pinglog $sqlwhere ORDER BY id DESC" . S::sqlLimit(($page-1)*$db_perpage, $db_perpage));
		while ($rt = $db->fetch_array($query)) {
			$rt['point'] = $rt['point'] > 0 ? '+'.$rt['point'] : $rt['point'];
			$rt['pingdate'] = get_date($rt['pingdate']);
			$rt['record'] = str_replace(Chr(10)," ",$rt['record']);
			isset($creditnames[$rt['name']]) && $rt['name'] = $creditnames[$rt['name']];

			if (strpos($username,"'".$rt['pinger']."'") === false) {
				$username .= $username ? ",'".$rt['pinger']."'" : "'".$rt['pinger']."'" ;
			}
			$pingdb[] = $rt;
		}
		if ($username) {
			$query = $db->query("SELECT groupid,username FROM pw_members WHERE username IN($username)");
			while ($rt = $db->fetch_array($query)) {
				$userdb[$rt['username']] = $rt['groupid'];
			}
			foreach ($pingdb as $key => $val) {
				if ($groupid != 3 && $groupid != 4) {
					if ($userdb[$val['pinger']] == 3 || $userdb[$val['pinger']] == 4) {
						$pingdb[$key]['ifable'] = 1;
					}
				}
			}
		}
		require_once PrintEot($template);footer();

	} elseif ($_POST['step'] == 2) {

		PostCheck();
		S::gp(array('selid'));

		empty($selid) && Showmsg('masigle_nodata');
		$db->update("UPDATE pw_pinglog SET ifhide=1 WHERE id IN(" . S::sqlImplode($selid) . ')');
		$pingService = L::loadClass("ping", 'forum');
		$pingService->update_markinfo($tid, $pid);

		if (defined('AJAX')) {
			echo "success";
			ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$rpage",'operate_success');
		}
	}
} elseif ($action == 'viewip') {

	S::gp(array('page'),'GP',2);
	S::gp(array('pid'));
	$sqlsel = $sqltab = $sqladd = "";
	if (is_numeric($pid)) {
		$pid = intval($pid);
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.fid,t.userip,t.ipfrom";
		$sqltab = "$pw_posts t";
		$sqladd = " AND t.pid = " . S::sqlEscape($pid);
	} else {
		$pw_tmsgs = GetTtable($tid);
		$sqlsel = "t.fid,tm.userip,tm.ipfrom";
		$sqltab = "pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid = t.tid";
		$sqladd = '';
	}
	$readdb = $db->get_one("SELECT $sqlsel FROM $sqltab WHERE t.tid = " . S::sqlEscape($tid) . $sqladd);
	if (!$readdb || $readdb['fid'] <> $fid) {
		Showmsg('illegal_tid');
	}
	$ipban = $db->get_value("SELECT db_value FROM pw_config WHERE db_name = 'db_ipban'");
	$bannedIps = explode(',', $ipban);
	require_once PrintEot($template);
	footer();

} elseif ($action == 'banuserip') {

	//!$SYSTEM['banuserip'] &&  Showmsg('masingle_banip_noright');
	S::gp(array('tid', 'fid', 'pid', 'page', 'restore', 'step'));
	$tid = (int) $tid;
	$fid = (int) $fid;
	$page = (int) $page;
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	$sqlsel = $sqltab = $sqladd = '';
	if (is_numeric($pid)) {
		$pid = (int) $pid;
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.fid,t.subject,t.content,t.userip,t.postdate,t.anonymous,t.authorid,";
		$sqltab = "$pw_posts t LEFT JOIN pw_members m ON t.authorid = m.uid";
		$sqladd = " AND t.pid = " . S::sqlEscape($pid);
	} else {
		$pw_tmsgs = GetTtable($tid);
		$sqlsel = "t.fid,t.subject,t.postdate,t.ifshield,t.anonymous,tm.userip,";
		$sqltab = "pw_threads t LEFT JOIN pw_members m ON t.authorid=m.uid LEFT JOIN $pw_tmsgs tm ON tm.tid = t.tid";
	}
	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid = " . S::sqlEscape($tid) . $sqladd);
	if (!$readdb || $readdb['fid'] <> $fid) {
		Showmsg('illegal_tid');
	}
	empty($readdb['userip']) && Showmsg('masingle_noip');
	if ($restore) {
		require_once(R_P.'admin/cache.php');
		$ipban=$db->get_one("SELECT db_value FROM pw_config WHERE db_name = 'db_ipban'");
		$bannedIps = explode(',', $ipban['db_value']);
		!in_array($readdb['userip'], $bannedIps) && Showmsg('ip_not_banned');
		$bannedIps = array_filter(str_replace($readdb['userip'], '', $bannedIps));
		setConfig('db_ipban', implode(',', $bannedIps));
		updatecache_c();
		if (defined('AJAX')) {
			Showmsg('masingle_banip_free');
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$page",'masingle_banip_free');
		}
	}
	$readdb['subject'] = substrs($readdb['subject'],35);
	!$readdb['subject'] && is_numeric($pid) && $readdb['subject'] = substrs($readdb['content'],35);
	if (empty($step)) {
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			$reason_sel .= ($v=trim($v)) ? "<option value=\"$v\">$v</option>" : "<option value=\"\">-------</option>";
		}
		require_once PrintEot($template);
		footer();
	} else {
		PostCheck();
		require_once(R_P.'admin/cache.php');
		$ipban=$db->get_one("SELECT db_value FROM pw_config WHERE db_name = 'db_ipban'");
		if (!empty($ipban['db_value']) && strpos($ipban['db_value'], $readdb['userip']) !== false) Showmsg('ip_banned');
		$ipban['db_value'] .= ($ipban['db_value'] ? ',' : '') . $readdb['userip'];
		setConfig('db_ipban', $ipban['db_value']);
		updatecache_c();
		$log = array(
				'type'      => 'banuserip',
				'username1' => $readdb['username'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'banuserip_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'forum'		=> $forum[$fid]['name'],
				'subject'	=> substrs($readdb['subject'],28),
				'reason'	=> stripslashes($atc_content)
			);
		writelog($log);
		if (defined('AJAX')) {
			Showmsg('masingle_banip_ban');
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$page",'masingle_banip_ban');
		}
	}
} elseif ($action == 'bansignature') {

	!$SYSTEM['bansignature'] &&  Showmsg('masingle_bansignature_noright');
	S::gp(array('tid', 'fid', 'pid', 'page', 'isbanned', 'step', 'ifmsg'));
	$tid = (int) $tid;
	$fid = (int) $fid;
	$isbanned = (int) $isbanned;
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	$sqlsel = $sqltab = $sqladd = '';
	if (is_numeric($pid)) {
		$pid = (int) $pid;
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.fid,t.subject,t.content,t.postdate,t.anonymous,t.authorid,";
		$sqltab = "$pw_posts t LEFT JOIN pw_members m ON t.authorid = m.uid";
		$sqladd = " AND t.pid = " . S::sqlEscape($pid);
	} else {
		$sqlsel = "t.fid,t.subject,t.postdate,t.ifshield,t.anonymous,";
		$sqltab = "pw_threads t LEFT JOIN pw_members m ON t.authorid=m.uid";
	}
	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid = " . S::sqlEscape($tid) . $sqladd);
	if (!$readdb || $readdb['fid'] <> $fid) {
		Showmsg('illegal_tid');
	}
	$readdb['subject'] = substrs($readdb['subject'],35);
	!$readdb['subject'] && is_numeric($pid) && $readdb['subject'] = substrs($readdb['content'],35);
	if (empty($step)) {
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			$reason_sel .= ($v=trim($v)) ? "<option value=\"$v\">$v</option>" : "<option value=\"\">-------</option>";
		}
		$isbanned == 1 ? $check_N = 'checked' : $check_Y = 'checked';
		require_once PrintEot($template);
		footer();
	} else {
		PostCheck();
		$banSig = $db->get_value("SELECT id FROM pw_ban WHERE type = 1 and uid = ". S::sqlEscape($readdb['uid']));
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if ($step == 3) {
			!empty($banSig) && Showmsg('masingle_bansignature_hasrecord');
			$operate = 1;
			$userService->setUserStatus($readdb['uid'], PW_USERSTATUS_BANSIGNATURE, true);
			$insertArray = array($readdb['uid'], $readdb['username'], 1, $windid, stripslashes($atc_content), $timestamp);
			$_insertSql = 'INSERT INTO pw_ban (uid, username, type, admin, reason, time) VALUES (' . S::sqlImplode($insertArray) . ')';
			$db->update($_insertSql);
		} elseif ($step == 5) {
			empty($banSig) && Showmsg('masingle_bansignature_norecord');
			$operate = 0;
			$userService->setUserStatus($readdb['uid'], PW_USERSTATUS_BANSIGNATURE, false);
			$db->update("DELETE FROM pw_ban WHERE type = 1 and uid = ". S::sqlEscape($readdb['uid']));
		}
		if ($ifmsg) {
			M::sendNotice(
				array($readdb['username']),
				array(
					'title' => getLangInfo('writemsg','bansignature_title_'.$operate),
					'content' => getLangInfo('writemsg','bansignature_content_'.$operate,array(
						'manager'	=> $windid,
						'fid'		=> $fid,
						'tid'		=> $tid,
						'subject'	=> $readdb['subject'],
						'postdate'	=> get_date($readdb['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					)),
				)
			);
		}
		if ($step == 3) {
			$log = array(
				'type'      => 'signature',
				'username1' => $readdb['username'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'signature_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'forum'		=> $forum[$fid]['name'],
				'subject'	=> substrs($readdb['subject'],28),
				'reason'	=> stripslashes($atc_content)
			);
			writelog($log);
		}
		//* $_cache = getDatastore();
		//* $_cache->delete('UID_'.$readdb['uid']);
		$showMsg = $operate == 1 ? 'masingle_bansignature_ban' : 'masingle_bansignature_free';
		if (defined('AJAX')) {
			Showmsg($showMsg);
		} else {
			refreshto("read.php?tid=$tid&displayMode=1&page=$page",$showMsg);
		}
	}
}
?>
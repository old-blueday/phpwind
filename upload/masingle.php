<?php
if (isset($_GET['ajax'])) {
	define('AJAX','1');
}
require_once('global.php');
$groupid == 'guest' && Showmsg('not_login');

InitGP(array('action'),'GP',0);
InitGP(array('atc_content'),'P');

if (!in_array($action,array('banuser','delatc','topped','shield','remind','commend','check','inspect','pingcp'))) {
	Showmsg('undefined_action');
}

require_once(R_P.'require/forum.php');
require_once(R_P.'require/writelog.php');
require_once(R_P.'require/updateforum.php');
include_once(D_P.'data/bbscache/forum_cache.php');
/****/
if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}
(!$foruminfo || $foruminfo['type'] == 'category') && Showmsg('data_error');
wind_forumcheck($foruminfo);

$isGM = CkInArray($windid,$manager);
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
			$admincheck = pwRights($isBM,'banuser');
			break;
		case 'shield' :
			$admincheck = pwRights($isBM,'shield');
			break;
		case 'remind' :
			$admincheck = pwRights($isBM,'remind');
			break;
		case 'inspect' :
			$admincheck = pwRights($isBM,'inspect');
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
	if ($db_enterreason && !$atc_content) {
		Showmsg('enterreason');
	}
}

if ($action == "topped") {
	InitGP(array('step'),'GP');
	if (empty($step)) {
		//增加置顶
		InitGP(array('tid','pid','lou','page'),'GP');
		if (empty($pid) || empty($tid) || empty($lou)) {
			Showmsg('undefined_action');
		}
		$remindinfo = getLangInfo('bbscode','read_topped')."\t".addslashes($windid)."\t".$timestamp;
		$pw_posts = GetPtable('N',$tid);
		$pw_poststop = array('fid'=>'0','tid'=>$tid,'pid'=>$pid,'floor'=>$lou,'uptime'=>$timestamp,'overtime'=>'');
		$db->update("REPLACE INTO pw_poststopped SET " . pwSqlSingle($pw_poststop));
		$db->update("UPDATE $pw_posts SET remindinfo = ".pwEscape($remindinfo)." WHERE tid=$tid AND pid=$pid");
		
	}elseif($step == '2'){
		//删除置顶
		InitGP(array('tid','pid','page'),'GP');
		if (empty($pid) || empty($tid)) {
			Showmsg('undefined_action');
		}
		$remindinfo = getLangInfo('bbscode','read_deltopped')."\t".addslashes($windid)."\t".$timestamp;
		$pw_posts = GetPtable('N',$tid);
		$db->update("UPDATE $pw_posts SET remindinfo = '' WHERE tid= ".pwEscape($tid)." AND pid= " . pwEscape($pid));
		$db->update("DELETE FROM pw_poststopped WHERE tid = ".pwEscape($tid)." AND pid = " . pwEscape($pid));
	}
	$t_count = $db->get_value("SELECT COUNT(*) AS count FROM pw_poststopped WHERE tid = " . pwEscape($tid) . " AND fid = '0' ");
	$t_count = intval($t_count);
	$db->update("UPDATE pw_threads SET topreplays=".pwEscape($t_count,false)." WHERE tid=" . pwEscape($tid));
	$threads = L::loadClass('Threads', 'forum');
	$threads->delThreads($tid);
	refreshto("read.php?tid=$tid&page=$page#$pid",'operate_success');
} elseif ($action == "banuser") {
	InitGP(array('pid'));
	InitGP(array('page'),'GP',2);
	(!$tid || !$fid || !$pid) && Showmsg('masingle_data_error');
	$sqlsel = $sqltab = $sqladd = '';
	if (is_numeric($pid)) {
		$pw_posts = GetPtable('N',$tid);
		$sqlsel = "t.anonymous,t.userip,";
		$sqltab = "$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd = ' AND t.pid='.pwEscape($pid);
	} else {
		$pw_tmsgs = GetTtable($tid);
		$sqlsel = "t.anonymous,tm.userip,";
		$sqltab = "pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON t.authorid=m.uid";
	}
	$userdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid,m.userstatus FROM $sqltab WHERE t.tid=".pwEscape($tid)." AND t.fid=".pwEscape($fid).$sqladd);
	!$userdb && Showmsg('undefined_action');
	$username = $userdb['anonymous'] ? $db_anonymousname : $userdb['username'];

	if (isban($userdb,$fid)) {
		Showmsg('member_havebanned');
	} elseif ($userdb['groupid'] != '-1') {
		Showmsg('masigle_ban_fail');
	}
	$pwBanMax = pwRights($isBM,'banmax');
	if (empty($_POST['step'])) {
		$reason_sel = '';
		$reason_a	= explode("\n",$db_adminreason);
		foreach ($reason_a as $k=>$v) {
			if ($v = trim($v)) {
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else {
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot($template);footer();
	} else {

		InitGP(array('range','ifmsg','banip'),'P');
		InitGP(array('limit','type'),'P',2);
		$range = $range ? '0' : intval($fid);

		if ($limit > $pwBanMax) {
			Showmsg('masigle_ban_limit');
		}
		if (!$isGM && $type == '2' && !pwRights($isBM,'bantype')) {
			Showmsg('masigle_ban_right');
		}
		if (!$isGM && ($range == '0' || $banip) && pwRights($isBM,'banuser') != '2') {
			Showmsg('masigle_ban_range');
		}
		$pwSQL = pwSqlSingle(array(
			'uid'		=> $userdb['uid'],
			'fid'		=> $range,
			'type'		=> $type,
			'startdate'	=> $timestamp,
			'days'		=> $limit,
			'admin'		=> $windid,
			'reason'	=> $atc_content
		));
		$db->update("REPLACE INTO pw_banuser SET $pwSQL");

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if ($range) {
			$userService->setUserStatus($userdb['uid'], PW_USERSTATUS_BANUSER, true);
		} else {
			$userService->update($userdb['uid'], array('groupid' => 6));
		}

		if ($ifmsg) {
			M::sendNotice(
				array($userdb['username']),
				array(
					'title' => getLangInfo('writemsg','banuser_title'),
					'content' => getLangInfo('writemsg','banuser_content_'.$type,array(
						'reason'	=> stripslashes($atc_content),
						'manager'	=> $windid,
						'limit'		=> $limit
					)),
				)
			);
		}
		if ($banip && $userdb['userip']) {
			require_once(R_P.'admin/cache.php');
			$rs = $db->get_one("SELECT db_name,db_value FROM pw_config WHERE db_name='db_ipban'");
			$rs['db_value'] .= ($rs['db_value'] ? ',' : '').$userdb['userip'];
			setConfig('db_ipban', $rs['db_value']);
			updatecache_c();
		}

		$log = array(
			'type'      => 'banuser',
			'username1' => $userdb['username'],
			'username2' => $windid,
			'field1'    => $fid,
			'field2'    => '',
			'field3'    => '',
			'descrip'   => 'banuser_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'tid'		=> $tid,
			'forum'		=> $forum[$fid]['name'],
			'subject'	=> '',
			'affect'	=> '',
			'reason'	=> stripslashes($atc_content)
		);
		writelog($log);

		if ($foruminfo['allowhtm']) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($tid);
		}

		$_cache = getDatastore();
		$_cache->delete('UID_'.$userdb['uid']);

		if (defined('AJAX')) {
			Showmsg('banuser_success');
		} else {
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		}
	}
} elseif ($action == "shield") {

	InitGP(array('pid','page'));
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

	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid=".pwEscape($tid).$sqladd);
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
			$db->update("UPDATE $pw_posts SET ifshield=".pwEscape($ifshield).' WHERE pid='.pwEscape($pid).' AND tid='.pwEscape($tid));
		} else {
			$db->update('UPDATE pw_threads SET ifshield='.pwEscape($ifshield).' WHERE tid='.pwEscape($tid));
			if ($db_ifpwcache ^ 1) {
				$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id=".pwEscape($tid));
			}
			$threads = L::loadClass('Threads', 'forum');
			$threads->delThreads($tid);
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
		refreshto("read.php?tid=$tid&page=$page",'operate_success');
	}
} elseif ($action == 'remind') {

	InitGP(array('pid','page'));
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
	$readdb = $db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid=".pwEscape($tid).' AND t.fid='.pwEscape($fid).$sqladd);
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
			$db->update("UPDATE $pw_posts SET remindinfo=".pwEscape($remindinfo).' WHERE pid='.pwEscape($pid).' AND tid='.pwEscape($tid));
		} else {
			$db->update("UPDATE $pw_tmsgs SET remindinfo=".pwEscape($remindinfo).' WHERE tid='.pwEscape($tid));
			$threads = L::loadClass('Threads', 'forum');
			$threads->delThreads($tid);
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
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		}
	} else {

		PostCheck();
		if (is_numeric($pid)) {
			$db->update("UPDATE $pw_posts SET remindinfo='' WHERE pid=".pwEscape($pid).' AND tid='.pwEscape($tid));
		} else {
			$db->update("UPDATE $pw_tmsgs SET remindinfo='' WHERE tid=".pwEscape($tid));
		}
		if (defined('AJAX')) {
			echo "cancle\t";ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		}
	}
} elseif ($action == 'delatc') {

	InitGP(array('selid'));
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
			$authordb = $db->get_one('SELECT groupid FROM pw_members WHERE uid='.pwEscape($tpcdb['authorid']));
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
		$pids  = pwImplode($pids);
		$query = $db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,ifcheck FROM $pw_posts WHERE tid='$tid' AND fid='$fid' AND pid IN($pids)");
		while ($rt = $db->fetch_array($query)) {
			if ($winduid != $rt['authorid']) {
				$authordb = $db->get_one('SELECT groupid FROM pw_members WHERE uid='.pwEscape($rt['authorid']));
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
		InitGP(array('ifdel','ifmsg'),'P');
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
		
		$refreshto = "read.php?tid=$tid";
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
	$db->update("UPDATE pw_threads SET ifcheck='1' WHERE tid=".pwEscape($tid)."AND fid=".pwEscape($fid)."AND ifcheck='0'");
        $threadList = L::loadClass("threadlist", 'forum');
        $threadList->refreshThreadIdsByForumId($fid);
	if ($db->affected_rows() > 0) {
		$rt = $db->get_one("SELECT tid,author,postdate,subject FROM pw_threads WHERE fid=".pwEscape($fid)."AND ifcheck='1' AND topped='0' ORDER BY lastpost DESC LIMIT 1");
		$lastpost = $rt['subject']."\t".$rt['author']."\t".$rt['postdate']."\t"."read.php?tid=$rt[tid]&page=e#a";
		$db->update("UPDATE pw_forumdata SET topic=topic+'1',article=article+'1',tpost=tpost+'1',lastpost=".pwEscape($lastpost,false)." WHERE fid='$fid'");
	}
	Showmsg('operate_success');

} elseif ($action == 'inspect') {

	$forumset = $foruminfo['forumset'];
	if (empty($forumset['inspect'])) {
		Showmsg('undefined_action');
	}
	InitGP(array('pid','page','p'));

	if (empty($_POST['step'])) {
		if (!empty($foruminfo['t_type']) && ($isGM || pwRights($isBM,'tpctype'))) {
			$iftypeavailable = 1;
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
		require_once PrintEot($template);footer();

	} else {
		PostCheck();
		InitGP(array('ifmsg','nextto'));
		$rt = $db->get_one('SELECT inspect FROM pw_threads WHERE tid='.pwEscape($tid) . " AND fid=".pwEscape($fid));
		empty($rt) && Showmsg('undefined_action');
		list($lou) = explode("\t",$rt['inspect']);
		$pid > $lou && $lou = $pid;
		$inspect = $lou."\t".addslashes($windid);
		$db->update('UPDATE pw_threads SET inspect='.pwEscape($inspect).' WHERE tid='.pwEscape($tid));
		delfcache($fid,$db_fcachenum);
		if ($ifmsg) {
			$threaddb = $db->get_one("SELECT author,subject FROM pw_threads WHERE tid=".pwEscape($tid));
			$postdate = get_date($timestamp,'Y-m-d H:i');
			M::sendNotice(
				array($threaddb['author']),
				array(
					'title' => getLangInfo('writemsg','inspect_title'),
					'content' => getLangInfo('writemsg','inspect_content',array(
						'manager'	=> $windid,
						'tid'		=> $tid,
						'subject'	=> $threaddb['subject'],
						'postdate'	=> $postdate,
						'reason'	=> stripslashes($atc_content)
					)),
				)
			);
		}
		if (!empty($nextto)) {
			if (!defined('AJAX')) {
				refreshto("mawhole.php?action=$nextto&fid=$fid&seltid=$tid",'operate_success');
			} else {
				$selids = $tid;
				Showmsg('ajax_nextto');
			}
		} else {
			refreshto("read.php?tid=$tid&page=$page#$p",'operate_success');
		}
	}

} elseif ($action == 'pingcp') {

	InitGP(array('pid','page'));
	if (empty($_POST['step'])) {

		$sqlwhere = ' WHERE 1';
		if (!is_numeric($pid)) {
			$sqlwhere .= " AND pid=0 AND tid=".pwEscape($tid);
		} else {
			$sqlwhere .= " AND pid=".pwEscape($pid);
		}
		$query = $db->query("SELECT id,name,point,pinger,record,pingdate FROM pw_pinglog $sqlwhere ORDER BY pingdate DESC");
		while ($rt = $db->fetch_array($query)) {
			$rt['point'] = $rt['point'] > 0 ? '+'.$rt['point'] : $rt['point'];
			$rt['pingdate'] = get_date($rt['pingdate']);
			$rt['record'] = str_replace(Chr(10)," ",$rt['record']);

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
		!$pingdb && Showmsg('masigle_noping');
		require_once PrintEot($template);footer();
	} elseif ($_POST['step'] == 2) {
		PostCheck();
		InitGP(array('selid','record'));
		empty($selid) && Showmsg('masigle_nodata');

		foreach ($record as $key => $val) {
			$db->update("UPDATE pw_pinglog SET record=".pwEscape($val)." WHERE id=".pwEscape($key));
		}

		if (defined('AJAX')) {
			echo "success";
			ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		}
	}
}
?>
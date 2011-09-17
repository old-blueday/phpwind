<?php
!defined('W_P') && exit('Forbidden');
require_once (R_P . 'lib/forum/forum.class.php');
require_once (R_P . 'lib/forum/post.class.php');
include_once (D_P . 'data/bbscache/cache_post.php');

empty($winduid) && wap_msg('not_login');

InitGP(array('fid', 'type', 'action', 'tid'), 'GP');
!$action && $action = 'new';
if ($action == 'new') {
	$basename = "index.php?a=forum&fid=$fid";
} elseif ($action == 'reply') {
	$basename = "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e";
} elseif ($action == 'modify') {
	$basename = "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e";
} else {
	$basename .= "?fid=" . ($fid ? $fid : 0);
}

if ($fid) {
	$pwforum = new PwForum($fid);
	$pwpost = new PwPost($pwforum);
	$pwpost->errMode = true;
	$pwpost->forumcheck();
	!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
	$pwpost->postcheck();
	!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
	list($uploadcredit, $uploadmoney, , ) = explode("\t", $pwforum->forumset['uploadset']);
}

//权限控制
list($db_openpost, $db_poststart, $db_postend) = explode("\t", $db_openpost);
if ($db_openpost == 1 && $db_poststart < $db_postend && ($t['hours'] < $db_poststart || $t['hours'] > $db_postend)) {
	wap_msg("post_openpost", $basename);
}

if ($db_postallowtime && $timestamp - $winddb['regdate'] < $db_postallowtime * 3600) {
	wap_msg('post_newrg_limit', $basename);
}
if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']) {
	wap_msg('post_gp_limit', $basename);
}
if ($_G['postpertime'] && $timestamp - $winddb['lastpost'] <= $_G['postpertime']) {
	wap_msg('post_limit', $basename);
}

/**
 * 禁止受限制用户发言
 */
$groupid == '7' && wap_msg("post_check", $basename);
if ($groupid == 6 || getstatus($winddb['userstatus'], 1)) {
	$pwSQL = '';
	$flag = 0;
	$bandb = $delban = array();
	$query = $db->query("SELECT * FROM pw_banuser WHERE uid=" . pwEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		if ($rt['type'] == 1 && $timestamp - $rt['startdate'] > $rt['days'] * 86400) {
			$delban[] = $rt['id'];
		} elseif ($rt['fid'] == 0 || $rt['fid'] == $fid) {
			$bandb[$rt['fid']] = $rt;
		} else {
			$flag = 1;
		}
	}
	$delban && $db->update('DELETE FROM pw_banuser WHERE id IN(' . pwImplode($delban) . ')');
	($groupid == 6 && !isset($bandb[0])) && $pwSQL .= "groupid='-1',";
	if (getstatus($winddb['userstatus'], 1) && !isset($bandb[$fid]) && !$flag) {
		$pwSQL .= 'userstatus=userstatus&(~1),';
	}
	if ($pwSQL = rtrim($pwSQL, ',')) {
		$db->update("UPDATE pw_members SET $pwSQL WHERE uid=" . pwEscape($winduid));
	}
	if ($bandb) {
		$bandb = current($bandb);
		if ($bandb['type'] == 1) {
			$s_date = get_date($bandb['startdate']);
			$e_date = $bandb['startdate'] + $bandb['days'] * 86400;
			$e_date = get_date($e_date);
			wap_msg('ban_info1', $basename);
		} else {
			if ($bandb['type'] == 3) {
				Cookie('force', $winduid);
				wap_msg('ban_info3', $basename);
			} else {
				wap_msg('ban_info2', $basename);
			}
		}
	}
}

$showpost = 0;
$template = 'post';
if ($action == 'new') {
	if ($fid && $pwpost) {
		require_once (R_P . 'lib/forum/topicpost.class.php');
		$topicpost = new topicPost($pwpost);
		$topicpost->check();
		!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
		if (!$pwpost->admincheck && !$pwforum->allowpost($pwpost->user, $pwpost->groupid)) {
			wap_msg('postnew_forum_right', $basename);
		}
		if (!$pwforum->foruminfo['allowpost'] && !$pwpost->admincheck && $_G['allowpost'] == 0) {
			wap_msg('postnew_group_right', $basename);
		}
	
	}
	if (!$_POST) {
		if (!$fid) {
			$fids = array();
			$query = $db->query("SELECT fid FROM pw_forums WHERE password='' AND allowvisit='' AND f_type!='hidden'");
			while ($rt = $db->fetch_array($query)) {
				$fids[] = $rt['fid'];
			}
			$cates = '';
			foreach ($forum as $key => $value) {
				if (in_array($key, $fids) && $value['type'] != 'category' && !$value['cms']) {
					$add = $value['type'] == 'forum' ? "&gt;" : ($forum[$value['fup']]['type'] == 'forum' ? "&gt;&gt;" : "&gt;&gt;&gt;");
					$value['name'] = wap_cv(strip_tags($value['name']));
					$cates .= "<option value=\"$key\">$add$value[name]</option>\n";
				}
			}
			$refer = "index.php?a=post&action=new&amp;tmp=$timestamp";
		} else {
			$forumName = wap_cv(strip_tags($forum[$fid]['name']));
			$refer = "index.php?a=post&action=new&amp;fid=$fid&amp;tmp=$timestamp";
		}
		$showpost = 1;
	} else {
		if (!is_numeric($fid)) {
			wap_msg("post_nofid", $basename);
		}
		
		!$pwforum && $pwforum = new PwForum($fid);
		!$pwpost && $pwpost = new PwPost($pwforum);
		$pwpost->errMode = true;
		$pwpost->forumcheck();
		!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
		$pwpost->postcheck();
		!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
		list($uploadcredit, $uploadmoney, , ) = explode("\t", $pwforum->forumset['uploadset']);
		
		if (!$pwpost->admincheck && !$pwforum->allowpost($pwpost->user, $pwpost->groupid)) {
			wap_msg('postnew_forum_right', 'index.php?a=post&fid=0');
		}
		if (!$pwforum->foruminfo['allowpost'] && !$pwpost->admincheck && $_G['allowpost'] == 0) {
			wap_msg('postnew_group_right', 'index.php?a=post&fid=0');
		}
		
		InitGP(array('subject', 'content'), 'P', 0);
		$refer = "index.php?a=post&action=new&amp;fid=$fid&amp;tmp=$timestamp";
		checkWapPost();
		require_once (R_P . 'require/bbscode.php');
		$postdata = new topicPostData($pwpost);
		$postdata->setTitle(wap_cv($subject,false));
		$postdata->setContent(wap_cv($content,false));
		$postdata->setData('lastpost', $timestamp);
		$postdata->setStatus(6);
		$postdata->conentCheck();
		$postdata->checkdata();
		!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
		$topicpost->execute($postdata);
		$tid = $topicpost->getNewId();
		if (!$tid) {
			wap_msg("发帖失败，您没有在此版块发帖的权限", $basename);
		}
		if ($postdata->getIfcheck() == '0') {
			wap_msg("发帖成功，请等待管理员审核", $basename);
		}
		$rurl = "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e";
		if ($_POST['upload']) {
			$rurl = "index.php?a=upload&tid=$tid&fid=$fid&page=e";
			header("Location:$rurl");
		}
		/*删除缓存*/
		$_filename = D_P . "data/wapcache/wap_all_cache.php";
		if (file_exists($_filename)) P_unlink($_filename);
		$_filename = Pcv(D_P . "data/wapcache/wap_" . $fid . "_cache.php");
		if (file_exists($_filename)) P_unlink($_filename);
		
		if ($postdata->getIfcheck()) {
			if ($postdata->filter->filter_weight == 3) {
				$pinfo = 'enter_words';
				$banword = implode(',',$postdata->filter->filter_word);
			} elseif($postdata->filter->filter_weight == 2){
				$banword = implode(',',$postdata->filter->filter_word);
				$pinfo = 'post_word_check';
			}else{
				$pinfo = 'post_success';
			}
		}
		wap_msg($pinfo, $rurl);
	}
} elseif ($action == 'modify') {
	if (!($foruminfo = L::forum($fid))) {
		wap_msg('data_error', $basename);
	}
	InitGP(array('tid', 'step'), 'GP');
	if (!$tid) {
		wap_msg('undefined_action', $basename);
	}
	require_once (R_P . 'lib/forum/postmodify.class.php');
	$postmodify = new topicModify($tid, 0, $pwpost);
	$atcdb = $postmodify->init();
	$atcdb['content'] = str_replace(array('<','>','&nbsp;'),array('&lt;','&gt;',' '),$atcdb['content']);
	
	//修改权限控制
	if (!$pwpost->isGM && !pwRights($pwpost->isBM, 'deltpcs')) {
		if ($groupid == 'guest' || $atcdb['authorid'] != $winduid) {
			wap_msg('modify_noper', $basename); //您无权限编辑别人的帖子
		} elseif ($locked % 3 > 0) {
			wap_msg('modify_locked', $basename); //该帖已被锁定，不可编辑
		}
	}
	if ($winduid != $atcdb['authorid'] && $groupid != 3 && $groupid != 4) {
		$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid=" . pwEscape($atcdb['authorid']));
		if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4)) {
			wap_msg('modify_admin', $basename); //您无权编辑管理员或总版主的帖子
		}
	}
	if (empty($step)) {
		$template = 'threadmodify';
	} elseif ($step == '2') {
		if (!is_numeric($fid)) {
			wap_msg("post_nofid!", $basename);
		}
		InitGP(array('subject', 'content'), 'GP');
		checkWapPost();
		require_once (R_P . 'require/bbscode.php');
		if ($postmodify->type == 'topic') {
			$postdata = new topicPostData($pwpost);
			$postdata->initData($postmodify);
		}
		$postdata->setTitle(wap_cv($subject,false));
		$postdata->setContent(wap_cv($content,false));
		$postdata->checkdata();
		!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg, $basename);
		$postmodify->execute($postdata);
		
		$rurl = "index.php?a=read&tid=" . $tid;
		if ($_POST['upload']) {
			$rurl = "index.php?a=upload&tid=$tid&fid=$fid&page=e";
			header("Location:$rurl");
		}
		
		/*删除缓存*/
		$_filename = D_P . "data/wapcache/wap_all_cache.php";
		if (file_exists($_filename)) P_unlink($_filename);
		$_filename = Pcv(D_P . "data/wapcache/wap_" . $fid . "_cache.php");
		if (file_exists($_filename)) P_unlink($_filename);
		
		
		if ($postdata->getIfcheck()) {
			if ($postdata->filter->filter_weight == 3) {
				$pinfo = 'enter_words';
				$banword = implode(',',$postdata->filter->filter_word);
			} elseif($postdata->filter->filter_weight == 2){
				$banword = implode(',',$postdata->filter->filter_word);
				$pinfo = 'post_word_check';
			}else{
				$pinfo = 'post_success';
			}
		}else{
			$pinfo = 'post_success';
		}
		wap_msg($pinfo, $rurl);
	}
} elseif ($action == 'reply') {
	require_once (R_P . 'require/postfunc.php');
	if (!$tid) {
		wap_msg('undefined_action', $basename);
	}
	$rurl = "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e";
	/**
	 * 版块权限判断
	 */
	if (!$pwpost->admincheck && !$pwforum->allowreply($pwpost->user, $pwpost->groupid)) {
		wap_msg('reply_forum_right', $rurl);
	}
	/**
	 * 用户组权限判断
	 */
	if (!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $_G['allowrp'] == 0) {
		wap_msg('reply_group_right', $rurl);
	}
	if ($article == '0') {
		$pw_tmsgs = GetTtable($tid);
		$S_sql = ',m.uid,m.groupid,m.userstatus,tm.ifsign,tm.content';
		$J_sql = "LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid";
	} else {
		$S_sql = $J_sql = '';
	}
	$tpcarray = $db->get_one("SELECT t.tid,t.fid,t.locked,t.ifcheck,t.author,t.authorid,t.postdate,t.lastpost,t.ifmail,t.special,t.subject,t.type,t.ifshield,t.anonymous,t.ptable,t.replies,t.tpcstatus $S_sql FROM pw_threads t $J_sql WHERE t.tid=" . pwEscape($tid));
	$pw_posts = GetPtable($tpcarray['ptable']);
	$tpcarray['openIndex'] = getstatus($tpcarray['tpcstatus'], 2);
	if ($tpcarray['fid'] != $fid) {
		wap_msg('illegal_tid', $rurl);
	}
	
	if ($pwforum->forumset['lock'] && !$pwpost->isGM && $timestamp - $tpcarray['postdate'] > $pwforum->forumset['lock'] * 86400 && !pwRights($pwpost->isBM, 'replylock')) {
		$forumset['lock'] = $pwforum->forumset['lock'];
		wap_msg('forum_locked', $rurl);
	}
	if (!$pwpost->isGM && !$tpcarray['ifcheck'] && !pwRights($pwpost->isBM, 'viewcheck')) {
		wap_msg('reply_ifcheck', $rurl);
	}
	if (!$pwpost->isGM && $tpcarray['locked'] % 3 != 0 && !pwRights($pwpost->isBM, 'replylock')) {
		wap_msg('reply_lockatc', $rurl);
	}
	InitGP(array('subject', 'content'), 'P', 0);
	checkWapPost(0);
	require_once (R_P . 'lib/forum/replypost.class.php');
	$replypost = new replyPost($pwpost);
	$replypost->setTpc($tpcarray);
	$replypost->check();
	!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg);

	require_once (R_P . 'require/bbscode.php');
	$replypost->setTpc($tpcarray);
	$content = $content."\r\n\r\n[size=2][color=#a5a5a5]内容来自[/color][color=#4f81bd][url=".$db_bbsurl."/m/index.php][手机版][/url][/color] [/size]";
	
	$postdata = new replyPostData($pwpost);
	$postdata->setTitle(wap_cv($subject,false));
	$postdata->setContent(wap_cv($content,false));
	$postdata->conentCheck();
	$postdata->checkdata();
	!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg);
	$replypost->execute($postdata);
	$pid = $replypost->getNewId();
	pwHook::runHook('after_reply');
	$rurl = "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e";
	
	if ($postdata->getIfcheck() == '0') {
		wap_msg("发帖成功，请等待管理员审核", $rurl);
	}
	
	if ($_POST['upload']) {
		$rurl = "index.php?a=upload&tid=$tid&fid=$fid&page=e";
		header("Location:$rurl");
	}
	
	/*删除缓存*/
	$_filename = D_P . "data/wapcache/wap_all_cache.php";
	if (file_exists($_filename)) P_unlink($_filename);
	$_filename = Pcv(D_P . "data/wapcache/wap_" . $fid . "_cache.php");
	if (file_exists($_filename)) P_unlink($_filename);
	
	if ($postdata->getIfcheck()) {
		if ($postdata->filter->filter_weight == 3) {
			$pinfo = 'enter_words';
			$banword = implode(',',$postdata->filter->filter_word);
		} elseif($postdata->filter->filter_weight == 2){
			$banword = implode(',',$postdata->filter->filter_word);
			$pinfo = 'post_word_check';
		}else{
			$pinfo = 'post_success';
		}
	}
	wap_msg($pinfo, $rurl);
}
wap_header();
require_once PrintWAP($template);
wap_footer();

function checkWapPost($iftitle = 1){
	global $subject,$content,$db_titlemax,$db_postmax,$db_postmin,$refer;
	if ($iftitle && (empty($subject) || strlen($subject) > $db_titlemax)) {
		wap_msg("标题不能为空，且长度必须小于{$db_titlemax}字节",$refer);
	}
	if (strlen(trim($content)) >= $db_postmax || strlen(trim($content)) < $db_postmin) {
		$msg = $db_postmin ? "内容长度必须大于{$db_postmin}字节" : '';
		$msg .= $db_postmax ? "且小于{$db_postmax}字节" : '';
		wap_msg($msg,$refer);
	}
}
?>

<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 用户组权限判断
 */
if (!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $_G['allowrp'] == 0) {
	Showmsg('reply_group_right');
}
//实名认证权限
if ($db_authstate && !$pwpost->admincheck && $pwforum->forumset['auth_allowrp'] && true !== ($authMessage = $pwforum->authStatus($winddb['userstatus'],$pwforum->forumset['auth_logicalmethod']))) {
	Showmsg($authMessage . '_rp');
}

if ($article == '0') {
	$pw_tmsgs = GetTtable($tid);
	$S_sql = ',m.uid,m.groupid,m.userstatus,tm.ifsign,tm.content';
	$J_sql = "LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid";
} else {
	$S_sql = $J_sql = '';
}
$tpcarray = $db->get_one("SELECT t.tid,t.fid,t.locked,t.ifcheck,t.author,t.authorid,t.postdate,t.lastpost,t.ifmail,t.special,t.subject,t.type,t.ifshield,t.anonymous,t.ptable,t.replies,t.tpcstatus $S_sql FROM pw_threads t $J_sql WHERE t.tid=" . S::sqlEscape($tid));
$pw_posts = GetPtable($tpcarray['ptable']);
$tpcarray['openIndex'] = getstatus($tpcarray['tpcstatus'], 2);
//$t_date = $tpcarray['postdate'];//主题发表时间 bbspostguide 中用到
if ($tpcarray['fid'] != $fid) {
	Showmsg('illegal_tid');
}
$replytitle = $tpcarray['subject'];
/**
 * convert()需要$tpc_author变量
 */
$tpc_author = $tpcarray['author'];

if ($pwforum->forumset['lock']&& !$pwpost->isGM && $timestamp - $tpcarray['postdate'] > $pwforum->forumset['lock'] * 86400 && !pwRights($pwpost->isBM,'replylock')) {
	$forumset['lock'] = $pwforum->forumset['lock'];
	Showmsg('forum_locked');
}
if (!$pwpost->isGM && !$tpcarray['ifcheck'] && !pwRights($pwpost->isBM,'viewcheck')) {
	Showmsg('reply_ifcheck');
}
if (!$pwpost->isGM && $tpcarray['locked']%3<>0 && !pwRights($pwpost->isBM,'replylock')) {
	Showmsg('reply_lockatc');
}

$special = 0;
$icon = (int)$icon;

L::loadClass('replypost', 'forum', false);
$replypost = new replyPost($pwpost);
$replypost->setTpc($tpcarray);
$replypost->check();

if (empty($_POST['step'])) {
	##主题分类
	$db_forcetype = 0;
	require_once(R_P.'require/bbscode.php');

	$hideemail = 'disabled';
	if ($action == 'quote') {
		if ($article == '0') {
			$atcarray = $tpcarray;
		} else {
			!is_numeric($pid) && Showmsg('illegal_tid');
			$atcarray = $db->get_one("SELECT p.author,p.authorid,p.subject,p.ifsign,p.postdate,p.content,p.ifshield,p.anonymous,m.uid,m.groupid,m.userstatus FROM $pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid WHERE p.pid=".S::sqlEscape($pid));
		}
		if ($atcarray['ifshield']) {//单帖屏蔽
			$atcarray['content'] = shield($atcarray['ifshield']=='1' ? 'shield_article' : 'shield_del_article');
		} elseif ($pwforum->forumBan($atcarray)) {
			$atcarray['content'] = shield('ban_article');
		}
		$ifsign = $atcarray['ifsign'];
		$old_author = $atcarray['anonymous'] ? $db_anonymousname : $atcarray['author'];
		$replytitle = $atcarray['subject'];
		$wtof_oldfile = get_date($atcarray['postdate']);
		$old_content = $atcarray['content'];
		$old_content = preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is",getLangInfo('post','hide_post'),$old_content);
		$old_content = preg_replace("/\[post\](.+?)\[\/post\]/is",getLangInfo('post','post_post'),$old_content);
		$old_content = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is",getLangInfo('post','sell_post'),$old_content);
		$old_content = preg_replace("/\[quote\](.*)\[\/quote\]/is","",$old_content);
		$bit_content = explode("\n",$old_content);

		if (count($bit_content) > 5) {
			$old_content = "$bit_content[0]\n$bit_content[1]\n$bit_content[2]\n$bit_content[3]\n$bit_content[4]\n.......";
		}
		if (strpos($old_content,$db_bbsurl) !== false) {
			$old_content = str_replace('p_w_picpath',$db_picpath,$old_content);
			$old_content = str_replace('p_w_upload',$db_attachname,$old_content);
		}
		$old_content = preg_replace("/\<(.+?)\>/is","",$old_content);
		$atc_content = "[quote]".($article==0 ? getLangInfo('post','info_post_1') : getLangInfo('post','info_post_2'))."\n{$old_content} [url={$db_bbsurl}/job.php?action=topost&tid=$tid&pid=$pid][img]{$imgpath}/back.gif[/img][/url]\n[/quote]\n";
	}
	list($guidename, $forumtitle) = $pwforum->getTitle();
	$guidename .= "<em>&gt;</em><a href=\"read.php?tid=$tid\">$tpcarray[subject]</a>";
	if (!$replytitle) {
		$atc_title = "Re:$tpcarray[subject]";
		$forumtitle = "$atc_title|$forumtitle";
	} else {
		$atc_title = "Re:$replytitle";
		$forumtitle = "$atc_title|$tpcarray[subject]|$forumtitle";
	}
	$atc_title = substrs(str_replace('&nbsp;',' ',$atc_title), $db_titlemax - 3);
	$db_metakeyword = str_replace(array('|',' - '),',',$forumtitle).'phpwind';
	require_once(R_P.'require/header.php');
	$msg_guide = $pwforum->headguide($guidename);
	$post_reply = '';
	$review_reply = '';

	if ($db_showreplynum > 0) {
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		$pwAnonyHide = $pwpost->isGM || pwRights($pwpost->isBM,'anonyhide');
		$query = $db->query("SELECT p.pid,p.author,p.authorid,p.subject,p.postdate,p.content,p.anonymous,p.ifconvert,p.ifwordsfb,p.ifshield,m.uid,m.groupid,m.userstatus FROM $pw_posts p LEFT JOIN pw_members m ON p.authorid=m.uid WHERE tid=".S::sqlEscape($tid)."AND ifcheck='1' ORDER BY postdate DESC LIMIT 0,$db_showreplynum");
		while ($rt = $db->fetch_array($query)) {
			$tpc_author = ($rt['anonymous'] && !$pwAnonyHide && $windid != $rt['author']) ? $db_anonymousname : $rt['author'];
			$tpc_pid = $rt['pid'];
			if ($rt['ifshield']) {
				$groupid != '3' && $rt['content'] = shield($rt['ifshield'] == '1' ? 'shield_article' : 'shield_del_article');
			} elseif ($groupid != 3 && $db_shield && $pwforum->forumBan($rt)) {
				$rt['content'] = shield('ban_article');
			} else {
				if (!$wordsfb->equal($rt['ifwordsfb'])) {
					$rt['content'] = $wordsfb->convert($rt['content']);
				}
				$rt['ifconvert'] == 2 && $rt['content'] = convert($rt['content'],$db_windpost);
				if (strpos($rt['content'],'[p:') !== false || strpos($rt['content'],'[s:') !== false) {
					$rt['content'] = showface($rt['content']);
				}
			}
			$review_content = substrs(stripWindCode($rt['content']),255);
			$post_reply .= "<table width=\"100%\"><tr><td><div class=\"h b\">$tpc_author:$rt[subject]</div><div class=\"p10\">$rt[content]</div></td></tr></table>";
			$review_reply .= "<table width=\"100%\"><tr><td><div class=\"h b\">$tpc_author:$rt[subject]</div><div class=\"p10\">$review_content</div></td></tr></table>";
		}
	}
	if ($winduid && $tpcarray['special'] == 5) {
		$debatestand = $db->get_value("SELECT standpoint FROM pw_debatedata WHERE pid='0' AND tid=".S::sqlEscape($tid)."AND authorid=".S::sqlEscape($winduid));
		$debatestand = (int)$debatestand;
		${'debate_'.$debatestand} = 'SELECTED';
	}
	$postMinLength = empty($pwpost->forum->foruminfo['forumset']['contentminlen']) ? $db_postmin : $pwpost->forum->foruminfo['forumset']['contentminlen'];
	/**
	 * 索引设计时为了减少空间,回复的主题可能为空,所以默认为回复主题!
	 */
	require_once PrintEot('post');
	CloudWind::yunSetCookie(SCR);
	footer();

} elseif ($_POST['step'] == 2) {

	S::gp(array('atc_title','atc_content','quote_content'), 'P', 0);
	S::gp(array('atc_anonymous','atc_hideatt','atc_enhidetype','atc_credittype','flashatt','replytouser','_usernames'), 'P');
	S::gp(array('atc_iconid','atc_convert','atc_autourl','atc_usesign','atc_html','atc_hide','atc_requireenhide','atc_rvrc','atc_requiresell', 'atc_money', 'go_lastpage'), 'P', 2);
	
	S::gp(array('iscontinue'),'P');//ajax提交时有敏感词时显示是否继续
	($db_sellset['price'] && (int) $atc_money > $db_sellset['price']) && Showmsg('post_price_limit');
	require_once(R_P . 'require/bbscode.php');

	if ($action == 'quote' && $quote_content) {
		$atc_content = $quote_content . $atc_content;
	}
	$postdata = new replyPostData($pwpost);
	$postdata->setTitle($atc_title);
	!$postdata->setContent($atc_content) && Showmsg('post_price_limit');

	$postdata->setConvert($atc_convert, $atc_autourl);
	$postdata->setAnonymous($atc_anonymous);
	$postdata->setHideatt($atc_hideatt);
	$postdata->setIconid($atc_iconid);
	$postdata->setIfsign($atc_usesign, $atc_html);

	$postdata->setHide($atc_hide);
	$postdata->setEnhide($atc_requireenhide, $atc_rvrc, $atc_enhidetype);
	$postdata->setSell($atc_requiresell, $atc_money, $atc_credittype);
	$postdata->setAtUsers($_usernames);
	//$replypost->checkdata();
	$postdata->conentCheck();
	L::loadClass('attupload', 'upload', false);
	/*上传错误检查
	$return = PwUpload::checkUpload();
	$return !== true && Showmsg($return);
	end*/
	if (PwUpload::getUploadNum() || $flashatt) {
		S::gp(array('savetoalbum', 'albumid'), 'P', 2);
		$postdata->att = new AttUpload($winduid, $flashatt, $savetoalbum, $albumid);
		$postdata->att->check();
	}
	$replypost->setToUser($replytouser);
	$postdata->iscontinue = (int)$iscontinue;
	$postdata->setIfGoLastPage($go_lastpage);
	$replypost->execute($postdata);
	$pid = $replypost->getNewId();
	// defend start	
	CloudWind::yunUserDefend('postreply', $winduid, $windid, $timestamp, ($cloud_information[1] ? $timestamp - $cloud_information[1] : 0), ($pid ? 101 : 102),'',$postdata->data['content'],'','');
	// defend end
	if ($winduid && $tpcarray['special'] == 5) {
		L::loadClass("post_5", 'forum/special', false);
		$postdebate = new postSpecial($pwpost);
		$postdebate->reply($tid, $pid);
	}
	pwHook::runHook('after_reply');

	//defend start
	CloudWind::YunPostDefend ( $winduid, $windid, $groupid, $pid, $atc_title, $atc_content, 'reply',array('tid'=>$tid,'fid'=>$fid,'forumname'=>$pwforum->foruminfo['name']) );
	//defend end
	
	//job sign
	/*
	require_once(R_P.'require/functions.php');
	$_cacheService = Perf::gatherCache('pw_threads');
	$thread = ($page>1) ? $_cacheService->getThreadByThreadId($tid) : $_cacheService->getThreadAndTmsgByThreadId($tid);	
	initJob($winduid,"doReply",array('tid'=>$tid,'user'=>$thread['author']));
	*/
	$j_p = "read.php?tid=$tid&ds=1&page=e#a";
	if ($db_htmifopen)
		$j_p = urlRewrite ( $j_p );
	$pinfo = getLangInfo('refreshto', 'enter_thread');
	defined('AJAX') && $pinfo = "success\t" . $j_p;
	$flag = false;
	if (!$iscontinue && $postdata->getIfcheck()) {
		if (!isset($_POST['go_lastpage']) && 'ajax_addfloor' == $_POST['type']) {
			require_once S::escapePath(R_P.'require/addfloor.php');
			exit;
		}
		defined('AJAX') && $flag && ($pinfo = "continue\t" . getLangInfo('refreshto', $pinfo));
	} elseif ($go_lastpage && !$postdata->getIfcheck()) {
		$pinfo = getLangInfo('refreshto', 'success_check');
	}
	refreshto($j_p,$pinfo);
}
?>
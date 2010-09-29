<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 用户组权限判断
 */
if (!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $_G['allowrp'] == 0) {
	Showmsg('reply_group_right');
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
			$atcarray = $db->get_one("SELECT p.author,p.authorid,p.subject,p.ifsign,p.postdate,p.content,p.ifshield,p.anonymous,m.uid,m.groupid,m.userstatus FROM $pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid WHERE p.pid=".pwEscape($pid));
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
	$atc_title = substrs(str_replace('&nbsp;',' ',$atc_title), $db_titlemax - 2);
	$db_metakeyword = str_replace(array('|',' - '),',',$forumtitle).'phpwind';

	require_once(R_P.'require/header.php');
	$msg_guide = $pwforum->headguide($guidename);
	$post_reply = '';

	if ($db_showreplynum > 0) {
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		$pwAnonyHide = $pwpost->isGM || pwRights($pwpost->isBM,'anonyhide');
		$query = $db->query("SELECT p.author,p.authorid,p.subject,p.postdate,p.content,p.anonymous,p.ifconvert,p.ifwordsfb,p.ifshield,m.uid,m.groupid,m.userstatus FROM $pw_posts p LEFT JOIN pw_members m ON p.authorid=m.uid WHERE tid=".pwEscape($tid)."AND ifcheck='1' ORDER BY postdate DESC LIMIT 0,$db_showreplynum");

		while ($rt = $db->fetch_array($query)) {
			$tpc_author = ($rt['anonymous'] && !$pwAnonyHide && $windid != $rt['author']) ? $db_anonymousname : $rt['author'];
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
			$post_reply .= "<table align=center width=70% cellspacing=1 cellpadding=2 style='TABLE-LAYOUT: fixed;WORD-WRAP: break-word'><tr><td width=100%>$tpc_author:$rt[subject]<br /><br />$rt[content]</td></tr></table><hr size=1 color=$tablecolor width=80%>";
		}
	}
	if ($winduid && $tpcarray['special'] == 5) {
		$debatestand = $db->get_value("SELECT standpoint FROM pw_debatedata WHERE pid='0' AND tid=".pwEscape($tid)."AND authorid=".pwEscape($winduid));
		$debatestand = (int)$debatestand;
		${'debate_'.$debatestand} = 'SELECTED';
	}
	/**
	 * 索引设计时为了减少空间,回复的主题可能为空,所以默认为回复主题!
	 */
	require_once PrintEot('post');footer();

} elseif ($_POST['step'] == 2) {

	InitGP(array('atc_title','atc_content'), 'P', 0);
	InitGP(array('atc_anonymous','atc_hideatt','atc_enhidetype','atc_credittype','flashatt','replytouser'), 'P');
	InitGP(array('atc_iconid','atc_convert','atc_autourl','atc_usesign','atc_html','atc_hide','atc_requireenhide','atc_rvrc','atc_requiresell', 'atc_money'), 'P', 2);

	require_once(R_P . 'require/bbscode.php');

	$postdata = new replyPostData($pwpost);
	$postdata->setTitle($atc_title);
	$postdata->setContent($atc_content);

	$postdata->setConvert($atc_convert, $atc_autourl);
	$postdata->setAnonymous($atc_anonymous);
	$postdata->setHideatt($atc_hideatt);
	$postdata->setIconid($atc_iconid);
	$postdata->setIfsign($atc_usesign, $atc_html);

	$postdata->setHide($atc_hide);
	$postdata->setEnhide($atc_requireenhide, $atc_rvrc, $atc_enhidetype);
	$postdata->setSell($atc_requiresell, $atc_money, $atc_credittype);
	//$replypost->checkdata();
	$postdata->conentCheck();

	L::loadClass('attupload', 'upload', false);
	if (PwUpload::getUploadNum() || $flashatt) {
		$postdata->att = new AttUpload($winduid, $flashatt);
		$postdata->att->check();
		$postdata->att->transfer();
		PwUpload::upload($postdata->att);
	}

	$replypost->setToUser($replytouser);
	$replypost->execute($postdata);
	$pid = $replypost->getNewId();

	if ($winduid && $tpcarray['special'] == 5) {
		L::loadClass("post_5", 'forum/special', false);
		$postdebate = new postSpecial($pwpost);
		$postdebate->reply($tid, $pid);
	}

	//job sign
	require_once(R_P.'require/functions.php');
	$threads = L::loadClass('Threads', 'forum');
	$thread = $threads->getThreads($tid,!($page>1));
	initJob($winduid,"doReply",array('tid'=>$tid,'user'=>$thread['author']));

	if ($postdata->getIfcheck()) {
		if ($postdata->filter->filter_weight == 3) {
			$pinfo = 'enter_words';
			$banword = implode(',',$postdata->filter->filter_word);
		} else {
			$pinfo = 'enter_thread';
		}
		$j_p = "read.php?tid=$tid&page=e#a";
		/*Begin Add by liaohu for addfloor*/
		if('on' != $_POST['go_lastpage'] && 'ajax_addfloor' == $_POST['type']){
			require_once Pcv(R_P.'require/addfloor.php');
			exit;
		}
		refreshto($j_p,$pinfo);
		/*Begin Add by liaohu for addfloor*/
	} else {
		if ($postdata->filter->filter_weight == 2) {
			$banword = implode(',',$postdata->filter->filter_word);
			$pinfo = 'post_word_check';
		} elseif ($postdata->linkCheckStrategy) {
			$pinfo = 'post_link_check';
		}  else {
			$pinfo = 'post_check';
		}
		refreshto("thread.php?fid=$fid",$pinfo);
	}
}
?>
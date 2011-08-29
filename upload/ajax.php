<?php
define('AJAX','1');
require_once('global.php');
L::loadClass('forum', 'forum', false);
L::loadClass('post', 'forum', false);

$groupid == 'guest' && Showmsg('not_login');
empty($fid) && Showmsg('undefined_action');

$pwforum = new PwForum($fid);
$pwpost  = new PwPost($pwforum);
$pwpost->forumcheck();
$pwpost->postcheck();

//list($uploadcredit,$uploadmoney,$downloadmoney,$downloadimg) = explode("\t", $pwforum->forumset['uploadset']);

if ($groupid == 6 || getstatus($winddb['userstatus'], PW_USERSTATUS_BANUSER)) {
	$pwSQL = '';
	$flag  = 0;
	$bandb = $delban = array();
	$query = $db->query("SELECT * FROM pw_banuser WHERE uid=".S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		if ($rt['type'] == 1 && $timestamp - $rt['startdate'] > $rt['days']*86400) {
			$delban[] = $rt['id'];
		} elseif ($rt['fid'] == 0 || $rt['fid'] == $fid) {
			$bandb[$rt['fid']] = $rt;
		} else {
			$flag = 1;
		}
	}
	$delban && $db->update('DELETE FROM pw_banuser WHERE id IN('.S::sqlImplode($delban).')');

	$updateUser = array();
	if ($groupid == 6 && !isset($bandb[0])) {
		$updateUser['groupid'] = -1;
	}
	if (getstatus($winddb['userstatus'], PW_USERSTATUS_BANUSER) && !isset($bandb[$fid]) && !$flag) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->setUserStatus($winduid, PW_USERSTATUS_BANUSER, false);
	}
	if (count($updateUser)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, $updateUser);

		//* $_cache = getDatastore();
		//* $_cache->delete('UID_'.$winduid);
	}
	if ($bandb) {
		$bandb = current($bandb);
		if ($bandb['type'] == 1) {
			$s_date = get_date($bandb['startdate']);
			$e_date = $bandb['startdate'] + $bandb['days']*86400;
			$e_date = get_date($e_date);
			Showmsg('ban_info1');
		} else {
			if ($bandb['type'] == 3) {
				Cookie('force',$winduid);
				Showmsg('ban_info3');
			} else {
				Showmsg('ban_info2');
			}
		}
	}
}
if (GetCookie('force') && $winduid != GetCookie('force')) {
	$force = GetCookie('force');
	$bandb = $db->get_one('SELECT type FROM pw_banuser WHERE uid='.S::sqlEscape($force).' AND fid=0');
	if ($bandb['type'] == 3) {
		Showmsg('ban_info3');
	} else {
		Cookie('force','',0);
	}
}
$userlastptime = $groupid != 'guest' ?  $winddb['lastpost'] : GetCookie('userlastptime');
$tdtime  >= $winddb['lastpost'] && $winddb['todaypost'] = 0;
$montime >= $winddb['lastpost'] && $winddb['monthpost'] = 0;

if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']) {
	Showmsg('post_gp_limit');
}
list($postq,$showq)	= explode("\t",$db_qcheck);
S::gp(array('action'));

if ($action == 'modify') {

	S::gp(array('pid','article'));
	L::loadClass('postmodify', 'forum', false);
	if ($pid && is_numeric($pid)) {
		$postmodify = new replyModify($tid, $pid, $pwpost);
	} else {
		$postmodify = new topicModify($tid, 0, $pwpost);
	}
	$atcdb = $postmodify->init();

	if (empty($atcdb) || $atcdb['fid'] != $fid) {
		Showmsg('illegal_tid');
	}
	if (!$pwpost->isGM && !pwRights($pwpost->isBM, 'deltpcs')) {
		if ($groupid == 'guest' || $atcdb['authorid'] != $winduid) {
			Showmsg('modify_noper');
		} elseif ($atcdb['locked']%3 > 0) {
			Showmsg('modify_locked');
		}
	}
	if ($winduid != $atcdb['authorid']) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$authordb = $userService->get($atcdb['authorid']);
	  /**Begin modify by liaohu*/
		$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
		if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
			Showmsg('modify_admin');
		}
		/**End modify by liaohu*/
	}

	//版块编辑时间限制
	global $postedittime;
	L::loadClass('forum', 'forum', false);
	$pwforum = new PwForum($atcdb['fid']);
	$isBM = $pwforum->isBM($windid);
	$userSystemRight =  userSystemRight($windid, $isBM, 'deltpcs');
	$postedittime = $pwforum->foruminfo['forumset']['postedittime'];
	if (!$userSystemRight && $winduid == $atcdb['authorid'] && $postedittime !== "" &&   $postedittime != 0 && ($timestamp - $atcdb['postdate']) >  $postedittime * 60) {
		Showmsg('modify_forumtimelimit');
	}
	if ( $winduid == $atcdb['authorid'] && $_G['edittime'] && ($timestamp - $atcdb['postdate']) > $_G['edittime'] * 60) {
		Showmsg('modify_timelimit');
	}

	if (empty($_POST['step'])) {

		$atcdb['anonymous'] && $atcdb['author'] = $db_anonymousname;
		$atc_content = str_replace(array('<','>','&nbsp;'),array('&lt;','&gt;',' '),$atcdb['content']);
		if (strpos($atc_content,$db_bbsurl) !== false) {
			$atc_content = str_replace('p_w_picpath',$db_picpath,$atc_content);
			$atc_content = str_replace('p_w_upload',$db_attachname,$atc_content);
		}
		$atc_title = $atcdb['subject'];
		require_once PrintEot('ajax');ajax_footer();

	} else {

		PostCheck(1,($db_gdcheck & 4) && (!$db_postgd || $winddb['postnum'] < $db_postgd),$db_ckquestion & 4 && (!$postq || $winddb['postnum'] < $postq));

		S::gp(array('atc_title','atc_content'), 'P', 0);
		require_once(R_P.'require/bbscode.php');

		if ($postmodify->type == 'topic') {
			$postdata = new topicPostData($pwpost);
		} else {
			$pid = 'tpc';
			$postdata = new replyPostData($pwpost);
		}
		$postdata->initData($postmodify);
		$postdata->setTitle($atc_title);
		$postdata->setContent($atc_content);
		$postdata->setConvert(1);
		$postdata->setIfcheck();
		$postmodify->execute($postdata);

		extract(L::style());

		$leaveword = $atcdb['leaveword'] ? leaveword($atcdb['leaveword']) : '';
		$content   = convert($postdata->data['content'] . $leaveword, $db_windpost);

		if (strpos($content,'[p:') !== false || strpos($content,'[s:') !== false) {
			$content = showface($content);
		}
		if ($atcdb['ifsign'] < 2) {
			$content = str_replace("\n",'<br />',$content);
		}
		if ($postdata->data['ifwordsfb'] == 0) {
			$content = addslashes(wordsConvert(stripslashes($content)));
		}
		$creditnames = pwCreditNames();

		if ($atcdb['attachs']) {
			$attachShow = new attachShow(($pwpost->isGM || pwRights($pwpost->isBM, 'delattach')), $pwforum->forumset['uploadset']);
			$attachShow->setData($atcdb['attachs']);
			$attachShow->parseAttachs($pid, $content, $winduid == $atcdb['authorid']);
		}
		$alterinfo && $content .= "<div id=\"alert_$pid\" style=\"color:gray;margin-top:30px\">[ $alterinfo ]</div>";
		$atcdb['icon'] = $atcdb['icon'] ? "<img src=\"$imgpath/post/emotion/$atcdb[icon].gif\" align=\"left\" border=\"0\" />" : '';

		if (!$postdata->getIfcheck()) {
			if ($postdata->filter->filter_weight == 2) {
				$banword = implode(',',$postdata->filter->filter_word);
				$pinfo = 'post_word_check';
			} elseif ($postdata->linkCheckStrategy) {
				$pinfo = 'post_link_check';
			} else {
				$pinfo = 'post_check';
			}
			Showmsg($pinfo);
		}
		echo "success\t".stripslashes($atcdb['icon']."&nbsp;".$atc_title)."\t".str_replace(array("\r","\t"), array("",""), stripslashes($content));
		ajax_footer();
	}
} elseif ($action == 'quote') {

	if (!$pwpost->admincheck && !$pwforum->allowreply($pwpost->user, $pwpost->groupid)) {
		Showmsg('reply_forum_right');
	}
	if (!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $_G['allowrp'] == 0) {
		Showmsg('reply_group_right');
	}
	S::gp(array('pid','article','page'));
	$page = (int)$page;
	if ($article == '0') {
		$pw_tmsgs = GetTtable($tid);
		$S_sql = ',tm.ifsign,tm.content,m.uid,m.groupid,m.userstatus';
		$J_sql = "LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid LEFT JOIN pw_members m ON t.authorid=m.uid";
	} else {
		$S_sql = $J_sql = '';
	}
	$tpcarray = $db->get_one("SELECT t.fid,t.locked,t.ifcheck,t.author,t.authorid,t.subject,t.postdate,t.ifshield,t.anonymous,t.ptable $S_sql FROM pw_threads t $J_sql WHERE t.tid=".S::sqlEscape($tid));
	$pw_posts = GetPtable($tpcarray['ptable']);

	if ($tpcarray['fid'] != $fid) {
		Showmsg('illegal_tid');
	}
	if ($pwforum->forumset['lock'] && !$pwpost->isGM && $timestamp - $tpcarray['postdate'] > $pwforum->forumset['lock'] * 86400 && !pwRights($pwpost->isBM,'replylock')) {
		Showmsg('forum_locked');
	}
	if (!$pwpost->isGM && !$pwpost->isBM && !$tpcarray['ifcheck']) {
		Showmsg('reply_ifcheck');
	}
	if (!$pwpost->isGM && $tpcarray['locked']%3<>0 && !pwRights($pwpost->isBM,'replylock')) {
		Showmsg('reply_lockatc');
	}

	require_once(R_P.'require/bbscode.php');
	if ($article == '0') {
		$atcarray = $tpcarray;
	} else {
		!is_numeric($pid) && Showmsg('illegal_tid');
		$atcarray = $db->get_one("SELECT p.author,p.subject,p.postdate,p.content,p.ifshield,p.anonymous,m.uid,m.groupid,m.userstatus FROM $pw_posts p LEFT JOIN pw_members m ON p.authorid=m.uid WHERE p.pid=".S::sqlEscape($pid));
	}
	if ($atcarray['ifshield'] == '1') {
		$atcarray['content'] = shield('shield_article');
	} elseif ($atcarray['ifshield'] == '2') {
		$atcarray['content'] = shield('shield_del_article');
	} elseif ($pwforum->forumBan($atcarray)) {
		$atcarray['content'] = shield('ban_article');
	}
	if ($atcarray['anonymous']) {
		$old_author = $db_anonymousname;
		$replytouser = '';
	} else {
		$old_author = $replytouser = $atcarray['author'];
	}
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

	$replytitle =='' ? $atc_title = 'Re:'.$tpcarray['subject'] : $atc_title = 'Re:'.$replytitle;
	require_once PrintEot('ajax');ajax_footer();

} elseif ($action == 'subject') {

	(!$pwpost->isGM && !pwRights($pwpost->isBM, 'deltpcs')) && Showmsg('undefined_action');
	$atcdb = $db->get_one('SELECT authorid,subject FROM pw_threads WHERE tid=' . S::sqlEscape($tid));
	empty($atcdb) && Showmsg('illegal_tid');
	if ($winduid != $atcdb['authorid']) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$authordb = $userService->get($atcdb['authorid']);
		/**Begin modify by liaohu*/
		$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
		if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
			Showmsg('modify_admin');
		}
		/**End modify by liaohu*/
	}
	if (empty($_POST['step'])) {

		$atcdb['subject'] = str_replace(array("&lt;","&gt;","\t"),array('<','>',''),$atcdb['subject']);
		echo "success\t".$atcdb['subject'];
		ajax_footer();

	} else {

		PostCheck();
		S::gp(array('atc_content'),'P');
		$atc_content = html_entity_decode(urldecode($atc_content));
		!$atc_content && Showmsg('content_empty');
		if (!$atc_content || strlen($atc_content) > $db_titlemax) {
			Showmsg('postfunc_subject_limit');
		}
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($atc_content)) !== false) {
			Showmsg('title_wordsfb');
		}

		//$db->update('UPDATE pw_threads SET subject=' . S::sqlEscape($atc_content) . ' WHERE tid=' . S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('subject'=>$atc_content));
		//临时修改，待改进
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tid);
		$rt = $db->get_one('SELECT titlefont FROM pw_threads WHERE tid='.S::sqlEscape($tid));
		if ($rt['titlefont']) {
			$detail = explode("~",$rt['titlefont']);
			$detail[0] && $atc_content = "<font color=$detail[0]>$atc_content</font>";
			$detail[1] && $atc_content = "<b>$atc_content</b>";
			$detail[2] && $atc_content = "<i>$atc_content</i>";
			$detail[3] && $atc_content = "<u>$atc_content</u>";
		}
		echo "success\t".str_replace("\t","",stripslashes($atc_content));ajax_footer();
	}
}
?>
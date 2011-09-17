<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('tid', 'fid','article','pid'), 'GP', 2);
if ($tid < 1 || $fid < 1) quickPostMessage('undefined_action');

L::loadClass('forum', 'forum', false);
$pwforum = new PwForum($fid);
if (!$pwforum->isForum()) quickPostMessage('data_error');

list($isGM, $isBM, $forumset, $foruminfo) = array(S::inArray($windid, $manager), $pwforum->isBM($windid), $pwforum->forumset, $pwforum->foruminfo);
$cacheService = Perf::gatherCache('pw_threads');
$read = $cacheService->getThreadAndTmsgByThreadId($tid);
if (!$read) quickPostMessage('illegal_tid');

list($tpc_locked, $admincheck)  = array(($read['locked'] % 3 <> 0) ? 1 : 0, ($isGM || $isBM) ? 1 : 0);
//实名认证权限
if ($db_authstate && !$admincheck && $forumset['auth_allowrp'] && true !== ($authMessage = $pwforum->authStatus($winddb['userstatus'],$forumset['auth_logicalmethod']))) {
	quickPostMessage($authMessage . '_rp');
}

//quote required
require_once(R_P.'require/bbscode.php');

if ($article == '0') {
	$atcarray = $read;
	$userservice = L::loadClass('userservice','user');
	$userinfo = $userservice->get($read['authorid']);
	$userinfo  && $atcarray = array_merge($atcarray,$userinfo);
} else {
	!is_numeric($pid) && Showmsg('illegal_tid');
	$pw_posts = GetPtable($read['ptable']);
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
$old_content = preg_replace('/\[(\/)?(b|u|i|list|sub|color|font|hr|size|align|sup|strike|code|paragraph)[^\]]*]/i', '', $old_content);
$quote_content = substrs($old_content, 260);

$quote_content = ltrim($quote_content);
$atc_content = "[quote]".($article==0 ? getLangInfo('post','info_post_1') : getLangInfo('post','info_post_2'))."{$quote_content}[color=gray]&nbsp;({$wtof_oldfile})&nbsp;[/color][url={$db_bbsurl}/job.php?action=topost&tid=$tid&pid=$pid][img]{$imgpath}/back.gif[/img][/url]\n[/quote]\n";

//filter quote
$quote_content = substrs($old_content, 130);
$quote_content = preg_replace('/(\[s:[^]]+\])+/', '[表情]', $quote_content); //face
$quote_content = preg_replace('/(\[attachment=\d+\])+/', "<img src='{$imgpath}/wind/file/img.gif' />", $quote_content); //face

//title
list($guidename, $forumtitle) = $pwforum->getTitle();
if (!$replytitle) {
	$atc_title = "Re:$read[subject]";
	//$forumtitle = "$atc_title|$forumtitle";
} else {
	$atc_title = "Re:$replytitle";
	//$forumtitle = "$atc_title|$tpcarray[subject]|$forumtitle";
}

//time
list($postTime) = getLastDate($atcarray['postdate']);

$atc_title = substrs(str_replace('&nbsp;',' ',$atc_title), $db_titlemax - 3);
//quote

if ((!$tpc_locked || $SYSTEM['replylock']) && ($admincheck || $pwforum->allowreply($winddb, $groupid))) {
	if (!$admincheck && !$foruminfo['allowrp'] && !$_G['allowrp']) quickPostMessage('reply_group_right');
	require_once PrintEot('quickpost');
	ajax_footer();
}
if (!$isGM && $tpc_locked && !pwRights($isBM,'replylock')) {//locked
	quickPostMessage('reply_lockatc');
}
quickPostMessage('reply_group_right');

function quickPostMessage($message) {
	$message = getLangInfo('msg', $message);
	echo $message;
	ajax_footer();
}
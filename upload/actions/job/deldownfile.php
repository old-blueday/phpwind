<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array(
	'aid',
	'page'
));
empty($aid) && Showmsg('job_attach_error');

$pw_attachs = L::loadDB('attachs', 'forum');
$attach = $pw_attachs->get($aid);
!$attach && Showmsg('job_attach_error');
if (empty($attach['attachurl']) || strpos($attach['attachurl'], '..') !== false) {
	Showmsg('job_attach_error');
}
$fid = $attach['fid'];
$aid = $attach['aid'];
$tid = $attach['tid'];
$pid = $attach['pid'];
if (!($foruminfo = L::forum($fid))) Showmsg('data_error');
require_once (R_P . 'require/forum.php');
require_once (R_P . 'require/updateforum.php');
wind_forumcheck($foruminfo);

$isGM = S::inArray($windid, $manager); //获取管理权限
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
if ($isGM || pwRights($isBM, 'delattach')) {
	$admincheck = 1;
} else {
	$admincheck = 0;
}
if ($groupid != 'guest' && ($admincheck || $attach['uid'] == $winduid)) {
	pwDelatt($attach['attachurl'], $db_ifftp);
	pwFtpClose($ftp);
	$pw_attachs->delete($aid);
	$ifupload = getattachtype($tid);
	$ifaid = $ifupload === false ? 0 : 1;
	if ($pid) {
		$pw_posts = GetPtable('N', $tid);
		//$db->update("UPDATE $pw_posts SET aid=" . S::sqlEscape($ifaid, false) . "WHERE tid=" . S::sqlEscape($tid, false) . "AND pid=" . S::sqlEscape($pid, false));
		pwQuery::update($pw_posts, 'tid=:tid AND pid=:pid', array($tid, $pid), array('aid' => $ifaid));
	} else {
		$pw_tmsgs = GetTtable($tid);
		//* $db->update("UPDATE $pw_tmsgs SET aid=" . S::sqlEscape($ifaid, false) . " WHERE tid=" . S::sqlEscape($tid, false));
		pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('aid'=>$ifaid));
	}
	$ifupload = (int) $ifupload;
	//$db->update('UPDATE pw_threads SET ifupload=' . S::sqlEscape($ifupload) . ' WHERE tid=' . S::sqlEscape($tid));
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('ifupload'=>$ifupload));
	if ($foruminfo['allowhtm'] && $page == 1) {
		$StaticPage = L::loadClass('StaticPage');
		$StaticPage->update($tid);
		empty($j_p) && $j_p = "read.php?tid=$tid&ds=1";
		refreshto($j_p, 'operate_success');
	} else {
		refreshto("read.php?tid=$tid&ds=1&page=$page", 'operate_success');
	}
} else {
	Showmsg('job_attach_right');
}

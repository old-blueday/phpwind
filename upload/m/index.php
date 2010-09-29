<?php
require_once('wap_global.php');

InitGP(array('prog'));
if (!in_array($prog,array('index','cate','bbsinfo','login','quit','phone'))) {
	$prog = 'index';
}
include_once(D_P.'data/bbscache/olcache.php');
$total = $userinbbs + $guestinbbs;
wap_header('index',$db_bbsname);

if ($prog == 'cate') {
	$fids	= array();
	$query	= $db->query("SELECT fid FROM pw_forums WHERE password='' AND allowvisit='' AND f_type!='hidden'");
	while ($rt = $db->fetch_array($query)) {
		$fids[] = $rt['fid'];
	}
} elseif ($prog == 'bbsinfo') {
	$rt = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
	$rs = $db->get_one("SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
	$topic	 = $rs['topic'] + $rs['subtopic'];
	$article = $rs['article'];
	$tposts  = $rs['tposts'];
} elseif ($prog == 'login') {
	InitGP(array('lgt','pwuser','pwpwd','question','customquest','answer'),'P');
	if ($windid) {
		wap_msg('login_have');
	} elseif ($pwuser && $pwpwd) {
		$safecv = $db_ifsafecv ? wap_quest($question,$customquest,$answer) : '';
		wap_login($pwuser,md5($pwpwd),$safecv,$lgt);
	}
} elseif ($prog == 'quit') {
	require_once(R_P.'require/checkpass.php');
	Loginout();
	wap_msg('wap_quit','index.php');
} elseif ($prog == 'phone') {
	$pwServer['HTTP_ACCEPT_LANGUAGE'] = GetServer('HTTP_ACCEPT_LANGUAGE');
}
require_once PrintEot('wap_index');
wap_footer();
?>
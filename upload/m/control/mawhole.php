<?php
!defined('W_P') && exit('Forbidden');
! $winduid && wap_msg ( 'not_login' );
InitGP(array('action','fid','seltid','selpid'),'GP');
$template = 'read';
if (!($foruminfo = L::forum($fid))) {
	wap_msg('data_error','index.php?a=mawhole&fid='.$fid.'&seltid='.$seltid);
}

//validate
if (!$seltid || !$fid) {
	wap_msg('data_error');
}
//权限检查
$isGM = CkInArray($windid,$manager);
if (!$isGM) {
	$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
	$admincheck = pwRights($isBM,'delatc');
	!$admincheck && wap_msg('mawhole_right');
}
if ($action == 'del') {
	require_once(R_P.'require/msg.php');
	require_once(R_P.'require/writelog.php');
	InitGP(array('ifdel','ifmsg','atc_content'));
	if (empty($atc_content) && $db_enterreason) {
		wap_msg('enterreason','index.php?a=mawhole&fid='.$fid.'&seltid='.$seltid);
	}
	require_once(R_P.'require/credit.php');
	$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);
	$msg_delrvrc  = $ifdel ? abs($creditset['Delete']['rvrc']) : 0;
	$msg_delmoney = $ifdel ? abs($creditset['Delete']['money']) : 0;
	
	$delarticle = L::loadClass('DelArticle','forum');
	$readdb = array();
	if ($selpid) {
		$delids = array($selpid);
		$tpcdb = $db->get_one("SELECT t.tid,t.fid,t.author,t.authorid,t.postdate,t.ptable,t.subject FROM pw_threads t WHERE t.tid=".pwEscape($seltid));
		$pw_posts = GetPtable($tpcdb['ptable']);
		$query = $db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,
			ifcheck FROM $pw_posts WHERE tid=".pwEscape($seltid)." AND fid='$fid' AND pid=".pwEscape($selpid));
		while ($rt = $db->fetch_array($query)) {
			$rt['ptable'] = $tpcdb['ptable'];
			$readdb[] = $rt;
		}echo $pw_posts;
	}else{
		$delids = array($seltid);
		$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($delids));
	}
	$msgdb = $logdb = array();
	foreach ($readdb as $key => $read) {
		$read['fid'] != $fid && wap_msg('admin_forum_right');
		if ($ifmsg) {
			$msgdb[] = array( 
				'toUser'	=> $read['author'],
				'subject'	=> getLangInfo('writemsg','del_title'),
				'content'	=> getLangInfo('writemsg','del_content', array(
					'manager'	=> $windid,
					'fid'		=> $read['fid'],
					'tid'		=> $read['tid'],
					'subject'	=> $read['subject'] ? $read['subject'] : $tpcdb['subject'],
					'postdate'	=> get_date($read['postdate']),
					'forum'		=> strip_tags($forum[$fid]['name']),
					'affect'    => "{$db_rvrcname}:-{$msg_delrvrc},{$db_moneyname}:-{$msg_delmoney}",
					'admindate'	=> get_date($timestamp),
					'reason'	=> stripslashes($atc_content)
				))
			);
		}
	}
	$jurl = "index.php?a=forum&fid=".$fid;
	if ($selpid) {
		$delarticle->delReply($readdb, $db_recycle, $ifdel, true, array('reason' => $atc_content));
		$jurl = "index.php?a=read&tid=$seltid";
	} else {
		$delarticle->delTopic($readdb, $db_recycle, $ifdel, array('reason' => $atc_content));
	}
	
	$credit->runsql();
	
	foreach ($msgdb as $key => $val) {
		pwSendMsg($val);
	}
	if ($db_ifpwcache ^ 1) {
		$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id IN(" . pwImplode($delids) . ')');
	}
	P_unlink(D_P.'data/bbscache/c_cache.php');
	wap_msg("wap_post_del",$jurl);
}else{
	$sql = "SELECT * FROM pw_threads WHERE tid = " . pwEscape($seltid);
	$threadb = $db->get_one($sql);
	if (!$threadb) {
		wap_msg('data_error');
	}
	$threadb['postdate'] = get_date($threadb ['postdate'],"m-d H:i");
	
	//获取回复信息
	if ($selpid) {
		$ptables = GetPtable('N',$seltid);
		$replydb = $db->get_one("SELECT * FROM $ptables WHERE pid=".pwEscape($selpid));
	}
	$template = 'mawhole';	
}

wap_header ();
require_once PrintWAP ( $template );
wap_footer ();
?>
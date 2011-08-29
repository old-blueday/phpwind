<?php
!defined('W_P') && exit('Forbidden');
require_once (R_P . 'require/imgfunc.php');
require_once (W_P . 'include/threadfunction.php');
InitGP ( array ('tid', 'page', 'pid', 'c_page','action') );
(! is_numeric ( $tid ) || $tid < 1) && $tid = 1;

if (empty($action)) {
	if ($tid) {
		$pw_tmsgs = GetTtable ( $tid );
		$rt = $db->get_one ( "SELECT t.fid,t.tid,t.subject,t.author,t.authorid,t.replies,t.locked,t.postdate,t.anonymous,t.ptable,tm.content FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid=" . pwEscape ( $tid ) . " AND ifcheck=1" );
		if ($rt ['locked'] == 2) {
			wap_msg ( 'read_locked' );
		}
		if (! $rt) {
			wap_msg ( 'illegal_tid' );
		}
		$fid = $rt ['fid'];
		forumCheck ( $fid, 'read' );
		
		
		//读取板块信息
		if (!($foruminfo = L::forum($fid))) {
			wap_msg('data_error');
		}
		$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
		
		//获得管理权限
		$editright = ($isGM || pwRights($isBM,'deltpcs') || ($rt['authorid'] == $winduid));
		$delright =  ($isGM || pwRights($isBM,'modother'));
		$pwAnonyHide = 0;
		if (!$isGM) {
			$pwSystem = pwRights($isBM);
			$pwAnonyHide = $pwSystem['anonyhide'];
		}

		$forumName = wap_cv ( strip_tags( $forum [$fid] ['name']) );
		( int ) $page < 1 && $page = 1;
		$content = $rt ['content'];
		$replies = $rt ['replies'];
		$per = 10;
		$total = ceil ( $replies / $per );
		$total == 0 ? $page = 1 : ($page > $total ? $page = $total : '');
		$nextp = $page + 1;
		if ($nextp > $total)
			$nextp = 1;
		$prep = $page - 1;
		if ($prep < 1)
			$prep = $total;
		$rt ['subject'] = str_replace ( '&nbsp;', '', wap_cv ( $rt ['subject'] ) );
		$ht = viewOneReply ( $tid, $pid ,$rt ['ptable'] );
		$nextr = nextReply ( $tid, $pid, $rt ['ptable'], 1 );
		$prer = nextReply ( $tid, $pid, $rt ['ptable'], - 1 );
	
	} else {
		wap_msg ( 'illegal_tid' );
	}
} elseif($action=='modify') {
	InitGP(array('step'),'GP',2);
	$pw_tmsgs = GetTtable ( $tid );
	$rt = $db->get_one ( "SELECT t.fid,t.tid,t.subject,t.author,t.authorid,t.replies,t.locked,t.postdate,t.anonymous,t.ptable,tm.content FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid=" . pwEscape ( $tid ) . " AND ifcheck=1" );
	//读取板块信息
	if (!($foruminfo = L::forum($rt[fid]))) {
		wap_msg('data_error');
	}
	$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);

	//获得管理权限
	$editright = ($isGM || pwRights($isBM,'deltpcs') || ($rt['authorid'] == $winduid));
	
	!$editright && wap_msg ( '您没有权限编辑此回复' , 'index.php?a=reply&tid='.$tid.'&pid='.$pid );
	
	if (empty($step)) {
		$pw_posts = GetPtable ( $rt ['ptable'] );
		$reply = $db->get_one("SELECT * FROM $pw_posts WHERE pid = ".pwEscape($pid));
		$reply['content'] = str_replace(array('<','>','&nbsp;'),array('&lt;','&gt;',' '),$reply['content']);
	} elseif ($step == 2) {
		InitGP(array('content'),'GP');
		$pw_posts = GetPtable('N',$tid);
		$db->update("UPDATE $pw_posts SET content = " . pwEscape(wap_cv($content)) . " WHERE pid = ".pwEscape($pid));
		wap_msg ( 'operate_success' , 'index.php?a=reply&tid='.$tid.'&pid='.$pid );
	}
}
Cookie("wap_scr", serialize(array("page"=>"reply","extra"=>array("tid"=>$tid,"pid"=>$pid))));
wap_header ();
require_once PrintWAP ( 'reply' );
wap_footer ();
?>

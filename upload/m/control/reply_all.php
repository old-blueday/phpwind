<?php
!defined('W_P') && exit('Forbidden');
require_once (R_P.'require/bbscode.php');
require_once (W_P . 'include/threadfunction.php');
InitGP ( array ('tid', 'page', 'order' ) );
(! is_numeric ( $tid ) || $tid < 1) && $tid = 1;
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
	$forumName = wap_cv ( strip_tags($forum [$fid] ['name']) );
	$page == 'e' && $page = 65535;
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

	$postdb = viewReply ( $tid, $page, $replies, $per, 90, $rt ['ptable'], $order );

} else {
	wap_msg ( 'illegal_tid' );
}
Cookie("wap_scr", serialize(array("page"=>"reply","extra"=>array("tid"=>$tid))));
wap_header ();
require_once PrintWAP ( 'reply_all' );
wap_footer ();
?>

<?php
! function_exists ( 'adminmsg' ) && exit ( 'Forbidden' );
include_once (R_P . "require/postindex.php");
$repliesArray = array ('1000', '5000', '10000', '20000');
$postIndexDB = new PostIndexDB ( );
$postIndexDB->setPerpage ( 5 );
S::gp(array('sub'),'G');
if (empty($sub)) {
	S::gp ( array ('replies', 'page'), 'GP' ); 
	if (empty($action) || $action == "search") {
		$threads = $postIndexDB->getALLIndexedThreads($page);
	}elseif ($action == "reset") {
		S::gp ( array ('tid','step','s_step' ), 'G' );
		!$step && $step = 1;
		!$s_step && $s_step = 1;
		$total = 2;
		if ($tid && $step <= $total) {
			$s_step = $postIndexDB->resetPostIndex ( $tid,$step,$s_step );
			$next = $s_step ? $step : $step + 1;
		}
		if ($next) {
			$j_url = "$basename&action=reset&step=$next&s_step=$s_step&tid=$tid&page=$page";
			adminmsg('updatecache_total_step',EncodeUrl($j_url));
		}else{
			$basename = "$basename&action=search&page=$page";
			adminmsg ( "operate_success" );
		}
	}elseif ($action == "delete") {
		S::gp ( array ('tids','step','s_step'), 'GP' );
		!is_array($tids) && $tids = explode(',',$tids);
		!$step && $step = 1;
		!$s_step && $s_step = 1;
		$index = $step - 1;
		$total = count($tids);
		if ($total > $index) {
			$s_step = $postIndexDB->deletePostIndex ( $tids[$index],$s_step );
			$next = $s_step ? $step : $step + 1;
		}
		if ($next) {
			$tids = implode(',',$tids);
			$j_url = "$basename&action=delete&step=$next&s_step=$s_step&tids=$tids";
			adminmsg('updatecache_total_step',EncodeUrl($j_url));
		}else{
			adminmsg ( "operate_success" );
		}
	} 
}else{
	S::gp ( array ('replies', 'page', 'tid' ), 'GP' );
	if ($action == "search") {
		if ($tid) {
			$threads = $postIndexDB->getThreadsById($tid);
		}else{
			$threads = $postIndexDB->getThreadsByReplies ( $replies, $page );
		}
	} elseif ($action == "update") {
		S::gp ( array ('threads' ), 'GP' );
		if (!is_array($threads)) {
			$threads = explode(',',$threads); 
		}
		S::gp(array('step','t_step'),'GP');
		!$step && $step = 1;
		!$t_step && $t_step = 1;
		$index = $step - 1;
		$total = count($threads);
		if ($total > $index) {
			$t_step = $postIndexDB->addPostIndex($threads[$index],$t_step);
			$next = $t_step ? $step : $step+1;
		}
		if ($next) {
			$threads = implode(',',$threads);
			$j_url = "$basename&sub=y&action=update&step=$next&t_step=$t_step&threads=$threads";
			adminmsg('updatecache_total_step',EncodeUrl($j_url));
		}else{
			$basename = "$basename&sub=y&action=search";
			adminmsg ( "operate_success" );
		}
	} 
}
include PrintEot ( 'postindex' );
exit ();
?>
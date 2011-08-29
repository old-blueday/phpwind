<?php
!defined('P_W') && exit('Forbidden');
define('AJAX','1');
S::gp(array('type'));
if ($type == 'addcomment') {
	if (!$winduid) Showmsg('not_login');
	S::gp(array('content','stopic_id'));
	$stopic_id = intval($stopic_id);
	if (!$stopic_id) Showmsg('undefined_action');
	$comment = L::loadClass('commentservice','stopic');
	$data = array(
		'uid' 		 => $winduid,
		'stopic_id'  => $stopic_id,
		'content' 	 => $content,
		'postdate' 	 => $timestamp,
		'ip'		 => $onlineip
	);
	
	if (!$comment->insert($data)) Showmsg('添加失败');
	echo "success";
	ajax_footer();
}
if ($type == 'delcomment') {
	S::gp(array('commentid'));
	$commentid = intval($commentid);
	if ($commentid < 1 || !S::inArray($windid,$manager)) Showmsg('undefined_action');
	$comment = L::loadClass('commentservice','stopic');
	$result = $comment->getByCommentid($commentid);
	if (!$result || $result['uid'] != $winduid || !S::inArray($windid,$manager)) Showmsg('undefined_action');
	if (!$comment->deleteByCommentid($commentid)) Showmsg('删除失败');
	
	$commentReply = L::loadClass('CommentReplyService','stopic');
	$commentReply->deleteByCommentid($commentid);
	echo "success";
	ajax_footer();
}
if ($type == 'listcomment') {
	S::gp(array('stopic_id','page'));
	$perpage = $perpage ? $perpage : $db_perpage;
	$stopic_id = intval($stopic_id);
	$page = intval($page);
	if ($stopic_id < 1) Showmsg('undefined_action');
	
	!$page && $page = 1;
	$comment = L::loadClass('commentservice','stopic');
	$commentNum = $comment->getCommentsCountByStopicId($stopic_id);
	if ($commentNum) {
		$commentList = $comment->getCommentsByStopicId($stopic_id,$page,$perpage);
		$numofpage = ceil($commentNum/$perpage);
		$commentPages = numofpage($commentNum,$page,$numofpage,"pw_ajax.php?action=stopiccomment&type=listcomment&stopic_id=$stopic_id&",$perpage,'getCommentList');
	}
	require_once PrintEot('stopic_comment');
	ajax_footer();
}
if ($type == 'addreply') {
	if (!$winduid) Showmsg('not_login');
	S::gp(array('content','commentid'));
	$commentid = intval($commentid);
	if (!$commentid) Showmsg('undefined_action');
	$data = array(
		'uid' 		 => $winduid,
		'commentid'  => $commentid,
		'content' 	 => $content,
		'postdate' 	 => $timestamp,
		'ip'		 => $onlineip
	);
	$commentReply = L::loadClass('CommentReplyService','stopic');
	if ($commentReply->insert($data)) {
		$comment = L::loadClass('commentservice','stopic');
		$comment->updateReplynumByCommentid('+1',$commentid);
		echo "success";
	}
	ajax_footer();
}
if ($type == 'listreply') {
	S::gp(array('commentid'));
	$commentid = intval($commentid);
	if ($commentid < 1) Showmsg('undefined_action');
	$commentReply = L::loadClass('CommentReplyService','stopic');
	$replyList = $commentReply->getCommentsByCommentid($commentid);
	require_once PrintEot('stopic_comment');
	ajax_footer();
}
if ($type == 'delreply') {
	S::gp(array('replyid','commentid'));
	$replyid = intval($replyid);
	$commentid = intval($commentid);
	if ($replyid < 1 || $commentid < 1) Showmsg('undefined_action');

	$commentReply = L::loadClass('CommentReplyService','stopic');
	$result = $commentReply->getByReplyid($replyid);
	if (!$result || $result['uid'] != $winduid || !S::inArray($windid,$manager)) Showmsg('undefined_action');

	if ($commentReply->deleteByReplyid($replyid)) {
		$comment = L::loadClass('commentservice','stopic');
		$comment->updateReplynumByCommentid('-1',$commentid);
		echo "success";
	}
	ajax_footer();
}
if ($type == 'stopiclogin') {
	S::gp(array('requesturl'));
	
	$jumpurl = $requesturl;
	$descript = '请先登录，再继续操作';
	require_once PrintEot('poplogin');
	ajax_footer();
}

<?php
!defined('P_W') && exit('Forbidden');
!defined('M_P') && define('M_P', R_P . "mode/cms/");
if (!$winduid) Showmsg('undefined_action');
define('AJAX','1');
S::gp(array('type'));
require_once (D_P . 'mode/cms/require/core.php');
if ($type == 'addcomment') {
	S::gp(array('atc_content','article_id'));
	$article_id = intval($article_id);
	if (!$article_id) Showmsg('undefined_action');
	if (!checkReplyPurview()) Showmsg('您没有权限');
	$cmscomment = C::loadClass('cmscommentservice');
	if (($return = $cmscomment->addCheck($atc_content,$groupid)) !== true) Showmsg($return);
	$data = array(
		'uid' 		 => $winduid,
		'article_id' => $article_id,
		'content' 	 => $atc_content,
		'postdate' 	 => $timestamp,
		'ip'		 => $onlineip
	);
	
	if (!$cmscomment->insert($data)) echo "fail";
	echo "success";
	ajax_footer();
}
if ($type == 'delcomment') {
	S::gp(array('commentid'),'P','2');
	if ($commentid < 1) Showmsg('undefined_action');
	$cmscomment = C::loadClass('cmscommentservice');
	$data = $cmscomment->getByCommentid($commentid);
	if (!$data) Showmsg('data_error');
	
	$articleService = C::loadClass('articleservice');
	$articleModule = $articleService->getArticleModule($data['article_id']);
	if ($data['uid'] != $winduid && !checkEditPurview($windid,$articleModule->columnId)) Showmsg('您没有权限');
	
	if (!$cmscomment->deleteByCommentid($commentid)) Showmsg('删除失败');
	$cmscommentreplyservice = C::loadClass('cmscommentreplyservice');
	$cmscommentreplyservice->deleteByCommentid($commentid);
	echo "success";
	ajax_footer();
}
if ($type == 'listcomment') {
	S::gp(array('id','page'));
	$reply_perpage = 10;
	$id = intval($id);
	$page = intval($page);
	if ($id < 1) Showmsg('undefined_action');
	!$page && $page = 1;
	$cmscomment = C::loadClass('cmscommentservice');
	$replyCount = $cmscomment->getCommentsCountByArticleId($id);
	$cmsReplyList = $cmscomment->getCommentsByArticleId($id,$page,$reply_perpage);
	$replynumofpage=ceil($replyCount/$reply_perpage);
	$replyPages = numofpage($replyCount,$page,$replynumofpage,"pw_ajax.php?action=cmsreply&type=listcomment&id=$id&",$reply_perpage,'getCommentList');
	require_once PrintEot('cmsreply');
	ajax_footer();
}
if ($type == 'addreply') {
	S::gp(array('content','commentid'));
	$commentid = intval($commentid);
	if (!$commentid) Showmsg('undefined_action');
	if (!checkReplyPurview()) Showmsg('您没有权限');
	$cmscomment = C::loadClass('cmscommentservice');
	if (($return = $cmscomment->addCheck($content,$groupid)) !== true) Showmsg($return);
	$data = array(
		'uid' 		 => $winduid,
		'commentid'  => $commentid,
		'content' 	 => $content,
		'postdate' 	 => $timestamp,
		'ip'		 => $onlineip
	);
	$cmsreply = C::loadClass('cmscommentreplyservice');
	if (!$cmsreply->insert($data)) echo "fail";
	$cmscomment->updateReplynumByCommentid('+1',$commentid);
	echo "success";
	ajax_footer();
}
if ($type == 'listreply') {
	S::gp(array('commentid'));
	$commentid = intval($commentid);
	if ($commentid < 1) Showmsg('undefined_action');
	$cmscommentreplyservice = C::loadClass('cmscommentreplyservice');
	$replyList = $cmscommentreplyservice->getCommentsByCommentid($commentid);
	require_once PrintEot('cmsreply');
	ajax_footer();
}
if ($type == 'delreply') {
	S::gp(array('replyid','commentid','P',2));
	if ($replyid < 1 || $commentid < 1) Showmsg('undefined_action');
	
	$cmscomment = C::loadClass('cmscommentservice');
	$data = $cmscomment->getByCommentid($commentid);
	if (!$data) Showmsg('data_error');
	$cmscommentreplyservice = C::loadClass('cmscommentreplyservice');
	$replyData = $cmscommentreplyservice->getByReplyid($replyid);
	
	$articleService = C::loadClass('articleservice');
	$articleModule = $articleService->getArticleModule($data['article_id']);
	if ($replyData['uid'] != $winduid && !checkEditPurview($windid,$articleModule->columnId)) Showmsg('您没有权限');
	
	
	if (!$cmscommentreplyservice->deleteByReplyid($replyid)) echo "fail";
	$cmscomment->updateReplynumByCommentid('-1',$commentid);
	echo "success";
	ajax_footer();
}

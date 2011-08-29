<?php
!defined('W_P') && exit('Forbidden');
require_once(R_P.'lib/forum/forum.class.php');
require_once(R_P.'lib/forum/post.class.php');

InitGP(array('tid', 'fid', 'step'));
if(!($tid && $fid)){
	wap_msg('undefined_action','index.php?a=forum');
}
$pwforum = new PwForum($fid);
$pwpost  = new PwPost($pwforum);
$returnedit = "index.php?a=upload&tid=$tid&fid=$fid&page=e";
$pwpost->errMode = true;
$pwpost->forumcheck();
!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg,$returnedit);
$pwpost->postcheck();
!empty($pwpost->errMsg) && wap_msg($pwpost->errMsg,$returnedit);
require_once(R_P . 'lib/forum/postmodify.class.php');
require_once(R_P . 'require/bbscode.php');
$postmodify = new topicModify($tid, 0, $pwpost);
$postmodify->init();
if ($postmodify->type == 'topic') {
	$postdata = new topicPostData($pwpost);
	$postdata->initData($postmodify);
} else {
	$postdata = new replyPostData($pwpost);
	$postdata->initData($postmodify);
}
//获得附件信息
if($postmodify->hasAtt()){
	$atthtml = '';
	$attachs = $postmodify->atcdb['attachs'];
	foreach ($attachs as $key => $var) {
		$atthtml .= '<label><input type="checkbox" name="keep'.$key.'" value="'.$key.'" checked />' . $var['name'] . '<label><br/>';
	}
}
if ($step == 2) {
	list($uploadcredit,$uploadmoney,,) = explode("\t", $pwforum->forumset['uploadset']);
	//处理旧附件删除
	if ($postmodify->hasAtt() && is_array($attachs)) {

		$keep = array();
		$deleteAtt = array();
		foreach ($attachs as $key => $value) {
			$kname = "keep".$key;
			if (!$_POST["keep".$key]) {
				$deleteAtt[$key] = $value;
			}
		}
		if ($deleteAtt) {
			require_once (R_P . 'require/functions.php');
			require_once (R_P . 'require/updateforum.php');
			delete_att($deleteAtt);
		}
	}
	require_once(W_P . 'include/wapupload.php');
	if (PwUpload::getUploadNum()) {
		$ext = strtolower(substr(strrchr($_FILES['attachment_']['name'],'.'),1));
		$imageType = array('gif','jpg','jpeg','png','bmp','swf');
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
		foreach ($db_uploadfiletype as $key => $var) {
			if (!in_array($key,$imageType)) { 
				unset($db_uploadfiletype[$key]);
			}
		}
		if (!array_key_exists($ext,$db_uploadfiletype)) {
			wap_msg('非法图片类型',$returnedit);
		}
		$filesize = $db_uploadfiletype[$ext];
		if ($_FILES['attachment_']['size'] < 1 || $_FILES['attachment_']['size'] > $filesize*1024) {
			wap_msg('pro_loadimg_limit_wap',$returnedit);
		}
		$postdata->att = new WapUpload($winduid);
		checkupload($postdata->att);
	}

	$postmodify->execute($postdata);
	wap_msg('post_success', "index.php?a=read&tid=$tid&amp;fid=$fid&amp;page=e");
}else{
	wap_header();
	require_once PrintWAP('upload');
	wap_footer();
}

function checkupload(&$upatt) {
	global $db_allowupload,$returnedit;
	if (!$db_allowupload) {
		wap_msg('upload_close',$returnedit);
	} elseif (!$upatt->forum->allowupload($upatt->post->user, $upatt->post->groupid)) {
		wap_msg('upload_forum_right',$returnedit);
	} elseif (!$upatt->forum->foruminfo['allowupload'] && $upatt->post->_G['allowupload'] == 0) {
		wap_msg('upload_group_right',$returnedit);
	}
	if ($upatt->post->user['uploadtime'] < $GLOBALS['tdtime']) {
		$upatt->post->user['uploadnum'] = 0;
	}
	if ($upatt->post->_G['allownum'] > 0 && ($upatt->post->user['uploadnum'] + count($_FILES) + count($upatt->flashatt)) >= $upatt->post->_G['allownum']) {
		wap_msg('upload_num_error',$returnedit);
	}
	if ($upatt->post->_G['allowupload'] == 1 && $upatt->uploadmoney) {
		global $credit;
		require_once(R_P.'require/credit.php');
		if ($upatt->uploadmoney < 0 && $credit->get($upatt->post->uid, $upatt->uploadcredit) < abs($upatt->uploadmoney)) {
			$GLOBALS['creditname'] = $credit->cType[$upatt->uploadcredit];
			wap_msg('upload_money_limit',$returnedit);
		}
	}
}
?>

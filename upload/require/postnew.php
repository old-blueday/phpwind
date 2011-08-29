<?php
!function_exists('readover') && exit('Forbidden');

$article = 0;
//主题分类
if (!$theSpecialFlag) {//分类、团购、活动不启用主题分类
	$t_db = (array)$foruminfo['topictype'];
	$tdbJson = array();
	if ($t_db) {
		foreach ($t_db as $key => $value) {
			if ($value['ifsys'] && $gp_gptype != 'system') {
				unset($t_db[$key]);
				continue;
			}
			$tdbJson[$value['id']]['name'] = $value['name'];
			$tdbJson[$value['id']]['upid'] = $value['upid'];
			if ($value['upid'] != 0) {
				$tdbJson[$value['upid']]['sub'][] = $value['id'];
			}
		}
	}
	$tdbJson = pwJsonEncode($tdbJson);

	$t_per = $pwforum->foruminfo['t_type'];
}
$db_forcetype = 0; // 是否需要强制主题分类
if ($t_db && $t_per=='2' && !$pwpost->admincheck && !S::inArray($groupid, array(3,4))) {
	$extraGroups = array();
	$winddb['groups'] && $extraGroups = array_filter(explode(',', $winddb['groups']));
	$compareGroups = array_intersect($extraGroups, array(3, 4));
	empty($compareGroups) && $db_forcetype = 1;
}
if (!$pwforum->foruminfo['allowpost'] && !$pwpost->admincheck && $_G['allowpost'] == 0) {
	Showmsg('postnew_group_right');
}
$postSpecial = null;
if ($special && file_exists(R_P . "lib/forum/special/post_{$special}.class.php")) {
	L::loadClass("post_{$special}", 'forum/special', false);
	$postSpecial = new postSpecial($pwpost);
	$postSpecial->postCheck();
} elseif ($modelid > 0) {/*主题分类*/
	if ($postTopic) {
		$postTopic->postCheck();
	}
	$selectmodelhtml = $postTopic->getModelHtml();
	$topichtml = $postTopic->getTopicHtml($modelid);
	$special = 0;
} elseif ($pcid > 0) {/*团购*/
	if ($postCate) {
		$postCate->postCheck();
	}
	$selectmodelhtml = $postCate->getPcHtml();
	$topichtml = $postCate->getCateHtml($pcid);
	$special = 0;
} elseif ($actmid > 0) {/*活动*/
	if ($postActForBbs) {
		$postActForBbs->postCheck();
	}
	$selectmodelhtml = $postActForBbs->getActSelHtml($actmid,$fid);
	$topichtml = $postActForBbs->getActHtml($actmid);
	$previewForm = $postActForBbs->getPreviewForm($actmid,$tid);
	$special = 0;
}

$icon = (int)$icon;

L::loadClass('topicpost', 'forum', false);
$topicpost = new topicPost($pwpost);
if ($cyid) {
	require_once(R_P . 'apps/groups/lib/colonypost.class.php');
	$topicpost->extraBehavior = new PwColonyPost($cyid);
}
$topicpost->check();

if (empty($_POST['step'])) {

	if ($special && method_exists($postSpecial, 'setInfo')) {
		$set = $postSpecial->setInfo();
	}
	list($guidename, $forumtitle) = $pwforum->getTitle();
	if($cyid){
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		$newColony = new PwColony($cyid);
		$guidename .= "<em>&gt;</em><a href=\"thread.php?cyid={$cyid}\">".$newColony->info['cname']."</a>";
	}
	$db_metakeyword = str_replace(array('|',' - '),',',$forumtitle).'phpwind';

	require_once(R_P.'require/header.php');
	$msg_guide = $pwforum->headguide($guidename);
	$postMinLength = empty($pwpost->forum->foruminfo['forumset']['contentminlen']) ? $db_postmin : $pwpost->forum->foruminfo['forumset']['contentminlen'];
	require_once PrintEot('post');footer();

} elseif ($_POST['step'] == 2) {

	S::gp(array('atc_title','atc_content'), 'P', 0);
	S::gp(array('replayorder','atc_anonymous','atc_newrp','atc_tags','atc_hideatt','magicid','magicname','atc_enhidetype','atc_credittype','flashatt'),'P');
	S::gp(array('atc_iconid','atc_email','digest','topped','atc_hide','atc_requireenhide','atc_rvrc','atc_requiresell','atc_money', 'atc_usesign', 'atc_html', 'p_type', 'p_sub_type', 'atc_convert', 'atc_autourl'), 'P', 2);

	S::gp(array('iscontinue'),'P');//ajax提交时有敏感词时显示是否继续
	($db_sellset['price'] && (int) $atc_money > $db_sellset['price']) && Showmsg('post_price_limit');
	require_once(R_P . 'require/bbscode.php');

	$postdata = new topicPostData($pwpost);
	$replayorder = ( $replayorder == 1 || $replayorder == 2 ) ? $replayorder : 0 ;
	$postdata->setStatus('3',decbin($replayorder));
	$postdata->setWtype($p_type, $p_sub_type, $t_per, $t_db, $db_forcetype);
	$postdata->setTitle($atc_title);
	!$postdata->setContent($atc_content) && Showmsg('post_price_limit');

	$postdata->setConvert($atc_convert, $atc_autourl);
	$postdata->setTags($atc_tags);
	$postdata->setAnonymous($atc_anonymous);
	$postdata->setHideatt($atc_hideatt);
	$postdata->setIfmail($atc_email,$atc_newrp);
	$postdata->setDigest($digest);
	$postdata->setTopped($topped);
	$postdata->setIconid($atc_iconid);
	$postdata->setIfsign($atc_usesign, $atc_html);
	$postdata->setMagic($magicid,$magicname);

	$postdata->setHide($atc_hide);
	$postdata->setEnhide($atc_requireenhide, $atc_rvrc, $atc_enhidetype);
	$postdata->setSell($atc_requiresell, $atc_money, $atc_credittype);
	//$newpost->checkdata();
	$postdata->conentCheck();

	if ($postSpecial) {
		$postSpecial->initData();
		$postdata->setData('special', $postSpecial->special);
	}
	if ($postTopic) {//分类主题初始化
		$postTopic->initData();
		$postdata->setData('modelid', $postTopic->modelid);
	}
	if ($postCate) {//团购初始化
		$postCate->initData();
		$postdata->setData('special', 20+$postCate->pcid);
	}
	if ($postActForBbs) {//活动初始化
		$postActForBbs->initData();
		$postdata->setData('special', 8);
	}
	L::loadClass('attupload', 'upload', false);
	/*上传错误检查
	$return = PwUpload::checkUpload();
	$return !== true && Showmsg($return);
	end*/
	if (PwUpload::getUploadNum() || $flashatt) {
		S::gp(array('savetoalbum', 'albumid'), 'P', 2);
		$postdata->att = new AttUpload($winduid, $flashatt, $savetoalbum, $albumid);
		$postdata->att->check();
	}
	$postdata->iscontinue = (int)$iscontinue;
	$topicpost->execute($postdata);
	
	$tid = $topicpost->getNewId();
	defined('AJAX') && $pinfo = $pinfo.$tid;

	if ($postSpecial) {
		$postSpecial->insertData($tid);
	}
	if ($postTopic) {//分类主题插入数据
		$postTopic->insertData($tid,$fid);
	}
	if ($postCate) {//团购插入数据
		$postCate->insertData($tid,$fid);
	}
	if ($postActForBbs) {//活动初始化
		$postActForBbs->insertData($tid,$fid);
	}
	$isAtcEmail = (int) $atc_email;
	$isAtcNewrp = (int) $atc_newrp;
	$userService = L::loadClass('UserService', 'user');
	$userService->setUserStatus($winduid, PW_USERSTATUS_REPLYEMAIL, $isAtcEmail);
	$userService->setUserStatus($winduid, PW_USERSTATUS_REPLYSITEEMAIL, $isAtcNewrp);

	$j_p = "read.php?tid=$tid&displayMode=1";
	if ($db_htmifopen)
		$j_p = urlRewrite ( $j_p );
	if (empty($j_p) || $pwforum->foruminfo['cms']) $j_p = "read.php?tid=$tid&displayMode=1";
	$pinfo = defined('AJAX') ? "success\t" . $j_p  : "";
	
	if (!$iscontinue) {
		if ($postdata->getIfcheck()) {
			if($prompts = $pwpost->getprompt()){
				isset($prompts['allowhide'])   && $pinfo = getLangInfo('refreshto',"post_limit_hide");
				isset($prompts['allowsell'])   && $pinfo = getLangInfo('refreshto',"post_limit_sell");
				isset($prompts['allowencode']) && $pinfo = getLangInfo('refreshto',"post_limit_encode");
			}
		}
	}
	//job sign
	require_once(R_P.'require/functions.php');
	initJob($winduid,"doPost",array('fid'=>$fid));
	refreshto($j_p, $pinfo);
}
?>
<?php
!function_exists('readover') && exit('Forbidden');
//require_once(R_P.'require/updateforum.php');

//主题分类
//$t_typedb = $t_subtypedb = array();
$t_per = 0;
//$t_exits = 0;$t_sub_exits = 0;
$t_db = (array)$foruminfo['topictype'];
$tdbJson = array();
if ($t_db) {
	foreach ($t_db as $key => $value) {
		if ($value['ifsys'] && $gp_gptype != 'system') {
			unset($t_db[$key]);
			continue;
		}
		$tdbJson[$value['id']]['name'] = strip_tags($value['name']);
		$tdbJson[$value['id']]['upid'] = $value['upid'];
		if ($value['upid'] != 0) {
			$tdbJson[$value['upid']]['sub'][] = $value['id'];
		}
	}
}
$tdbJson = pwJsonEncode($tdbJson);
/*
if ($t_db) {
	foreach ($t_db as $value) {
		if ($value['upid'] == 0) {
			$t_typedb[$value['id']] = $value;
		} else {
			$t_subtypedb[$value['upid']][$value['id']] = strip_tags($value['name']);
		}
		$t_exits = 1;
	}
}
if ($t_subtypedb) {
	$t_subtypedb = pwJsonEncode($t_subtypedb);
	$t_sub_exits = 1;
}
*/
$t_per = $pwforum->foruminfo['t_type'];
$db_forcetype = 0; // 是否需要强制主题分类
if ($t_db && $t_per=='2' && !$pwpost->admincheck && !S::inArray($groupid, array(3,4))) {
	$extraGroups = array();
	$winddb['groups'] && $extraGroups = array_filter(explode(',', $winddb['groups']));
	$compareGroups = array_intersect($extraGroups, array(3, 4));
	empty($compareGroups) && $db_forcetype = 1;
}
L::loadClass('postmodify', 'forum', false);
if ($pid && is_numeric($pid)) {
	$postmodify = new replyModify($tid, $pid, $pwpost);
} else {
	$postmodify = new topicModify($tid, 0, $pwpost);
}
$atcdb = $postmodify->init();
$postmodify->check();

if ($postmodify->type == 'topic') {
	$atc_email = ( ($atcdb ['ifmail'] == 1) || ($atcdb ['ifmail'] == 3) ) ? 'checked' : "";
	$atc_newrp = ( ($atcdb ['ifmail'] == 2) || ($atcdb ['ifmail'] == 3) ) ? 'checked' : "";
	list($magicid,$magicname) = explode("\t", $atcdb['magic']);
	$type	 = $atcdb['type'];
	$special = $atcdb['special'];
	$modelid = $atcdb['modelid'];
	$pcid = $atcdb['special'] > 20 ? $atcdb['special'] - 20 : 0;

	if ($atcdb['special'] == 8) {
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);
		$actmid = $postActForBbs->getActmid($tid);
	}

	$isCheck_hiddenPost = ($atcdb['shares']) ? "checked" : "";
	$isCheck_anonymous = ($atcdb['anonymous']) ? "checked" : "";
} else {
	$special = $modelid = $pcid = 0;
}

$theSpecialFlag = false;
if ($pcid > 0 || $modelid > 0 || $actmid > 0) {
	$db_forcetype = 0;
	$theSpecialFlag = true;
}

if ($modelid) {//分类主题
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($pwpost);
	if ($postTopic) {
		$postTopic->postCheck();
	}
	$topichtml = $postTopic->getTopicHtml($modelid);
}

if ($pcid > 0) {//团购
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($pwpost);
	if ($postCate) {
		$postCate->postCheck();
	}
	$topichtml = $postCate->getCateHtml($pcid);
}

if ($actmid > 0) {//活动
	if ($postActForBbs) {
		$postActForBbs->postCheck();
	}
	$authorid = $atcdb['authorid'];

	$postActForBbs->setPeopleAlreadyPaid($postActForBbs->peopleAlreadyPaid($tid));
	$postActForBbs->setPeopleAlreadySignup($postActForBbs->peopleAlreadySignup($tid));

	$topichtml = $postActForBbs->getActHtml($actmid,$tid);
	$previewForm = $postActForBbs->getPreviewForm($actmid,$tid);
}

$page = floor($article/$db_readperpage) + 1;

$hideemail = 'disabled';
$icon = (int)$icon;

if (empty($_POST['step'])) {

	$attach = '';
	if ($atcdb['attachs']) {
		ksort($atcdb['attachs']);
		reset($atcdb['attachs']);
		foreach ($atcdb['attachs'] as $key => $value) {
			list($value['attachurl'],) = geturl($value['attachurl'],'lf');
			$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
		}
		$attach = rtrim($attach,',');
	}
	if ($postmodify->type == 'topic') {
		if ($pwforum->foruminfo['cms']) {
			include_once(R_P.'require/c_search.php');
			list($tids,$kname) = search_tid($tid);
		}
		if ($t_db[$type]['upid']) {
			$ptype = $t_db[$type]['upid'];
			$psubtype = $type;
		} else {
			$ptype = $type;
		}
		if ($special && file_exists(R_P . "lib/forum/special/post_{$special}.class.php")) {
			L::loadClass("post_{$special}", 'forum/special', false);
			$postSpecial = new postSpecial($pwpost);
			$set = $postSpecial->resetInfo($tid, $atcdb);
		}
		list($tags) = explode("\t", $atcdb['tags']);
		$tags = htmlspecialchars($tags);
	}
	//empty($subject) && $subject = ' ';

	$htmcheck = $atcdb['ifsign'] < 2 ? '' : 'checked';
	//$htmlpost = !$htmlpost &&  ? 'checked' : ''; //TODO 回复隐藏
	!$ifanonymous && $atcdb['anonymous'] && $ifanonymous = 'checked';
	!$attachHide && $atcdb['ifhide'] && $attachHide = 'checked';
	$atc_title = $atcdb['subject'];
	$icon = (int)$atcdb['icon'];
	$replayorder = bindec(getstatus($atcdb['tpcstatus'],4).getstatus($atcdb['tpcstatus'],3));
	$replayorder_asc = $replayorder_desc = $replayorder_default = '';
	if ($replayorder == '1') {
		$replayorder_asc = 'checked';
	} elseif ($replayorder == '2') {
		$replayorder_desc = 'checked';
	} else {
		$replayorder_default = 'checked';
	}
	empty($atc_title) && $atc_title = ' ';
	$atc_content = str_replace(array('<','>'),array('&lt;','&gt;'), $atcdb['content']);

	if (strpos($atc_content,$db_bbsurl) !== false) {
		$atc_content = str_replace('p_w_picpath',$db_picpath,$atc_content);
		$atc_content = str_replace('p_w_upload',$db_attachname,$atc_content);
	}
	list($guidename, $forumtitle) = $pwforum->getTitle();
	$guide_subject = $atcdb['subject'] ? $atcdb['subject'] : $atcdb['tsubject'];
	if (trim($guide_subject)) {
		$guidename .= "<em>&gt;</em><a href=\"read.php?tid=$tid\">$guide_subject</a>";
	}
	$db_metakeyword = str_replace(array('|',' - '),',',$forumtitle).'phpwind';
	$db_metadescrip = substrs(strip_tags(str_replace('"','&quot;',$atc_content)),50);

	require_once(R_P.'require/header.php');
	$msg_guide = $pwforum->headguide($guidename);
	$postMinLength = empty($pwpost->forum->foruminfo['forumset']['contentminlen']) ? $db_postmin : $pwpost->forum->foruminfo['forumset']['contentminlen'];

	require_once PrintEot('post');footer();

} elseif ($_POST['step'] == 1) {

	if (!$pwpost->isGM) {
		if ($winduid != $atcdb['authorid'] && !pwRights($pwpost->isBM,'modother')) {
			Showmsg('modify_del_right');
		} elseif ($_G['allowdelatc'] == 0) {
			Showmsg('modify_group_right');
		}
	}
	$pw_posts = GetPtable('N', $tid);
	$rt = $db->get_one("SELECT COUNT(*) AS count FROM $pw_posts WHERE tid=".S::sqlEscape($tid)." AND ifcheck='1'");
	$count = $rt['count'] + 1;
	//admincheck
	$isGM = S::inArray($windid,$manager);
	$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
	$admincheck = ($isGM || $isBM) ? 1 : 0;
	if ($article == 0 && !$admincheck && $count > 1) {
		Showmsg('modify_replied');
	}
	$rs = $db->get_one("SELECT replies,topped,tpcstatus FROM pw_threads WHERE tid=".S::sqlEscape($tid));
	$thread_tpcstatus = $rs['tpcstatus'];
	if ($rs['replies'] != $rt['count']) {
		//$db->update("UPDATE pw_threads SET replies=".S::sqlEscape($rt['count'])."WHERE tid=".S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid' , array($tid), array('replies'=>$rt['count']));
	}
	require_once(R_P.'require/credit.php');
	$creditset = $credit->creditset($creditset,$db_creditset);
	if ($atcdb['aid']) {
		require_once(R_P.'require/functions.php');
		require_once(R_P.'require/updateforum.php');
		delete_att($atcdb['aid']);
		pwFtpClose($ftp);
	}
	if ($article == 0) {
		$deltype  = 'deltpc';
		$deltitle = substrs($subject,28);
		if ($count == 1) {
			//* $db->update("DELETE FROM $pw_tmsgs WHERE tid=".S::sqlEscape($tid));
			pwQuery::delete($pw_tmsgs, 'tid=:tid', array($tid));
			# $db->update("DELETE FROM pw_threads WHERE tid=".S::sqlEscape($tid));
			# ThreadManager
            //* $threadManager = L::loadClass("threadmanager", 'forum');
			//* $threadManager->deleteByThreadId($fid,$tid);
			$threadService = L::loadclass('threads', 'forum');
			$threadService->deleteByThreadId($tid);	
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));		

			P_unlink(R_P."$db_readdir/$fid/".date('ym',$postdate)."/$tid.html");
		} else {
			$rt = $db->get_one("SELECT * FROM $pw_posts WHERE tid=".S::sqlEscape($tid)."ORDER BY postdate LIMIT 1");
			if ($count == 2) {
				$lastpost	= $rt['postdate'];
				$lastposter	= $rt['author'];
			} else {
				$lt = $db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid=".S::sqlEscape($tid)."ORDER BY postdate DESC LIMIT 1");
				$lastpost	= $lt['postdate'];
				$lastposter	= $lt['author'];
			}
			$count -= 2;
			//$db->update("DELETE FROM $pw_posts WHERE pid=".S::sqlEscape($rt['pid']));
			pwQuery::delete($pw_posts, 'pid=:pid', array($rt['pid']));
			$pwSQL = $rt['subject'] ? array('subject'=>$rt['subject']) : array();
			$pwSQL += array(
				'icon'		=> $rt['icon'],
				'author'	=> $rt['author'],
				'authorid'	=> $rt['authorid'],
				'postdate'	=> $rt['postdate'],
				'lastpost'	=> $lastpost,
				'lastposter'=> $lastposter,
				'replies'	=> $count
			);
			//$db->update("UPDATE pw_threads SET ".S::sqlSingle($pwSQL,false)." WHERE tid=".S::sqlEscape($tid));
			pwQuery::update('pw_threads', 'tid=:tid' , array($tid), $pwSQL);
                        # memcache reflesh
                        //* $threadList = L::loadClass("threadlist", 'forum');
                        //* $threadList->updateThreadIdsByForumId($fid,$tid);
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));   
			/**                     
			$db->update("UPDATE $pw_tmsgs SET " . S::sqlSingle(array(
				'aid'		=> $rt['aid'],				'userip'	=> $rt['userip'],
				'ifsign'	=> $rt['ifsign'],			'ipfrom'	=> $rt['ipfrom'],
				'alterinfo'	=> $rt['alterinfo'],		'ifconvert'	=> $rt['ifconvert'],
				'content'	=> $rt['content']
			),false) . " WHERE tid=".S::sqlEscape($tid));
			**/
			pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array(
				'aid'		=> $rt['aid'],				'userip'	=> $rt['userip'],
				'ifsign'	=> $rt['ifsign'],			'ipfrom'	=> $rt['ipfrom'],
				'alterinfo'	=> $rt['alterinfo'],		'ifconvert'	=> $rt['ifconvert'],
				'content'	=> $rt['content']
			));			
			
		}
		$msg_delrvrc  = abs($creditset['Delete']['rvrc']);
		$msg_delmoney = abs($creditset['Delete']['money']);
		$credit->addLog('topic_Delete',$creditset['Delete'],array(
			'uid'		=> $authorid,
			'username'	=> $author,
			'ip'		=> $onlineip,
			'fname'		=> strip_tags($forum[$fid]['name']),
			'operator'	=> $windid
		));
		$credit->sets($authorid,$creditset['Delete'],false);

		if ($thread_tpcstatus && getstatus($thread_tpcstatus, 1)) {
			$db->update("DELETE FROM pw_argument WHERE tid=".S::sqlEscape($tid));
		}
	} else {
		$deltype  = 'delrp';
		$deltitle = $subject ? substrs($subject,28) : substrs($content,28);
		//$db->update("DELETE FROM $pw_posts WHERE pid=".S::sqlEscape($pid));
		pwQuery::delete($pw_posts, 'pid=:pid', array($pid));
		//$db->update("UPDATE pw_threads SET replies=replies-1 WHERE tid=".S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('replies=replies-1')));
		$msg_delrvrc  = abs($creditset['Deleterp']['rvrc']);
		$msg_delmoney = abs($creditset['Deleterp']['money']);
		$credit->addLog('topic_Deleterp',$creditset['Deleterp'],array(
			'uid'		=> $authorid,
			'username'	=> $author,
			'ip'		=> $onlineip,
			'fname'		=> strip_tags($forum[$fid]['name']),
			'operator'	=> $windid
		));
		$credit->sets($authorid,$creditset['Deleterp'],false);
	}
	$credit->setMdata($authorid,'postnum',-1);
	$credit->runsql();

	if ($db_guestread) {
		require_once(R_P.'require/guestfunc.php');
		clearguestcache($tid,$rs['replies']);
	}
	//* P_unlink(D_P.'data/bbscache/c_cache.php');
	pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
	require_once R_P . 'require/updateforum.php';
	updateforum($fid);
	if ($rs['topped']) {
		updatetop();
	}
	$msg_delrvrc = floor($msg_delrvrc/10);
	require_once(R_P.'require/writelog.php');
	$log = array(
		'type'      => 'delete',
		'username1' => $author,
		'username2' => $windid,
		'field1'    => $fid,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => $deltype.'_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
		'tid'		=> $tid,
		'forum'		=> $pwforum->foruminfo['name'],
		'subject'	=> $deltitle,
		'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
		'reason'	=> 'edit delete article!'
	);
	writelog($log);
	if ($pwforum->foruminfo['allowhtm'] && $article<=$db_readperpage ) {
		$StaticPage = L::loadClass('StaticPage');
		$StaticPage->update($tid);
	}
	if ($deltype == 'delrp') {
		refreshto("read.php?tid=$tid",'after_delete');
	} else {
		refreshto("thread.php?fid=$fid",'after_delete');
	}
} elseif ($_POST['step'] == 2) {

	S::gp(array('atc_title','atc_content'), 'P', 0);
	S::gp(array('atc_email','replayorder','atc_anonymous','atc_newrp','atc_tags','atc_hideatt','magicid','magicname','atc_enhidetype','atc_credittype','flashatt'),'P');
	S::gp(array('atc_iconid','atc_hide','atc_requireenhide','atc_rvrc','atc_requiresell','atc_money', 'atc_usesign', 'atc_html', 'p_type', 'p_sub_type', 'atc_convert', 'atc_autourl','isAttachOpen'), 'P', 2);

	S::gp(array('iscontinue'),'P');//ajax提交时有敏感词时显示是否继续
	($db_sellset['price'] && (int) $atc_money > $db_sellset['price']) && Showmsg('post_price_limit');
	require_once(R_P . 'require/bbscode.php');
	if ($postmodify->type == 'topic') {
		$postdata = new topicPostData($pwpost);
		$postdata->initData($postmodify);
		$postdata->setWtype($p_type, $p_sub_type, $t_per, $t_db, $db_forcetype);
		$postdata->setTags($atc_tags);
		$postdata->setMagic($magicid,$magicname);
		$postdata->setIfmail($atc_email, $atc_newrp);
		if ($replayorder == 1) {
			$postdata->setStatus('3','01');
		} elseif ($replayorder == 2) {
			$postdata->setStatus('3','10');
		} else {
			$postdata->setStatus('3','00');
		}
	} else {
		$postdata = new replyPostData($pwpost);
		$postdata->initData($postmodify);
	}

	$postdata->setTitle($atc_title);
	!$postdata->setContent($atc_content) && Showmsg('post_price_limit');

	$postdata->setConvert($atc_convert, $atc_autourl);
	$postdata->setAnonymous($atc_anonymous);
	$isAttachOpen && $postdata->setHideatt($atc_hideatt);
	$postdata->setIconid($atc_iconid);
	$postdata->setIfsign($atc_usesign, $atc_html);

	$postdata->setHide($atc_hide);
	$postdata->setEnhide($atc_requireenhide, $atc_rvrc, $atc_enhidetype);
	$postdata->setSell($atc_requiresell, $atc_money, $atc_credittype);

	if ($special && file_exists(R_P . "lib/forum/special/post_{$special}.class.php")) {
		L::loadClass("post_{$special}", 'forum/special', false);
		$postSpecial = new postSpecial($pwpost);
		$postSpecial->modifyData($tid);
	}
	if ($postmodify->hasAtt()) {
		S::gp(array('keep','oldatt_special','oldatt_needrvrc'), 'P', 2);
		S::gp(array('oldatt_ctype','oldatt_desc'), 'P');
		$postmodify->initAttachs(/*$keep, */$oldatt_special, $oldatt_needrvrc, $oldatt_ctype, $oldatt_desc);
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
		$postdata->att->setReplaceAtt($postmodify->replacedb);
	}
	$postdata->iscontinue = $iscontinue;
	$postmodify->execute($postdata);

	if ($postSpecial) {
		$postSpecial->updateData($tid);
	}
	if ($postTopic) {//分类主题
		$postTopic->initData();
		$postTopic->insertData($tid,$fid);
	}
	if ($postCate) {//团购
		$postCate->initData();
		$postCate->insertData($tid,$fid);
	}

	if ($postActForBbs) {//活动
		$postActForBbs->initData();
		$postActForBbs->insertData($tid,$fid);
	}
	if ($postmodify->type == 'topic') {
		$isAtcEmail = (int) $atc_email;
		$isAtcNewrp = (int) $atc_newrp;
		$userService = L::loadClass('UserService', 'user');
		$userService->setUserStatus($winduid, PW_USERSTATUS_REPLYEMAIL, $isAtcEmail);
		$userService->setUserStatus($winduid, PW_USERSTATUS_REPLYSITEEMAIL, $isAtcNewrp);
	}
	defined('AJAX') && $pinfo = "success\t" . "read.php?tid=$tid&displayMode=1&page=$page&toread=1#$pid";
	$flag = false;
	if(!$iscontinue){
		if ($postdata->getIfcheck()) {
			if($prompts = $pwpost->getprompt()){
				isset($prompts['allowhide'])   && $pinfo = getLangInfo('refreshto',"post_limit_hide");
				isset($prompts['allowsell'])   && $pinfo = getLangInfo('refreshto',"post_limit_sell");
				isset($prompts['allowencode']) && $pinfo = getLangInfo('refreshto',"post_limit_encode");
			}else{
				defined('AJAX') && $pinfo = "success\t" . "read.php?tid=$tid&displayMode=1&page=$page&toread=1#$pid";
			}
		}
	}
	defined('AJAX') && $flag && $pinfo = "continue\t" . getLangInfo('refreshto', $pinfo);	
	refreshto("read.php?tid=$tid&displayMode=1&page=$page&toread=1#$pid", $pinfo);
}
?>
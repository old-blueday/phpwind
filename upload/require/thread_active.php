<?php
!function_exists('readover') && exit('Forbidden');

if (!defined('A_P')) define('A_P', R_P . 'apps/');

$pwforum = new PwForum($fid);
if (!$pwforum->isForum(true)) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$groupRight =& $newColony->getRight();
$pwModeImg = "$imgpath/apps";
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');

require_once(R_P . 'require/header.php');
list($guidename, $forumtitle) = $pwforum->getTitle();
$msg_guide = $pwforum->headguide($guidename);

$styleid = $colony['styleid'];
$basename = "thread.php?cyid=$cyid&showtype=active";
S::gp(array('job'));

if (empty($job)) {

	S::gp(array('page'), '', 2);
	$page < 1 && $page = 1;
	$db_perpage = 10;
	
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	$total = $newActive->getActiveCount($cyid);
	$pages = numofpage($total, $page, ceil($total/$db_perpage), "{$basename}&");
	$activedb = $newActive->getActiveList($cyid, $db_perpage, ($page-1)*$db_perpage);
	list($newactivedb) = $newActive->searchList('', 3, 0, 'id', 'DESC');
	$hotactivedb = $newActive->getHotActive(3);
	
	require_once PrintEot('thread_active');footer();

} elseif ($job == 'view') {

	S::gp(array('id','page'), '', 2);
	$page < 1 && $page = 1;
	
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'require/showimg.php');
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if (!($active = $newActive->getActiveInfoById($id)) || $active['cid'] != $cyid) {
		Showmsg('data_error');
	}
	//检查是否是群组的成员
	$isJoin = $newActive->isJoin($id,$winduid);
	$tmpUrlAdd = '&job=view&id=' . $id;
	list($active['icon']) = showfacedesign($active['icon'], 1, 'm');
	$active = $newActive->convert($active);
	$actMembers = $newActive->getActMembers($id, 20);
	
	$active['content'] = str_replace("\n", '<br />', $active['content']);
	require_once(R_P . 'require/bbscode.php');
	$active['content'] = convert($active['content'], $db_windpost);
	if ($attachs = $newActive->getAttById($id)) {
		$attachShow = new attachShow(($isGM || $pwSystem['delattach']), '', 0, 'active');
		$attachShow->setData($attachs);
		$active += $attachShow->parseAttachs('tpc', $active['content'], $winduid == $active['uid']);
	}
	$newActive->updateHits($id);
	list($newactivedb) = $newActive->searchList(array('cid'=>$cyid), 3, 0, 'id', 'DESC');
	$hotactivedb = $newActive->getHotActive(3);
	$relateactivedb = $newActive->getRelateActive($id, 3);

	list($commentdb, $subcommentdb, $pages, $count) = getCommentDbByTypeid('active', $id, $page, "{$basename}&job=$job&id=$id&");
	$comment_type = 'active';
	$comment_typeid = $id;

	require_once PrintEot('thread_active');footer();

} elseif ($job == 'actmember' || $job == 'membermanage') {
	
	S::gp(array('id', 'page'), '', 2);
	$page < 1 && $page = 1;
	$tmpUrlAdd = '&job=' . $job . '&id=' . $id;
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
		Showmsg('data_error');
	}
	$db_perpage = 20;
	$pages = numofpage($active['members'], $page, ceil($active['members']/$db_perpage), "{$basename}&job=$job&id=$id&");
	$actMembers = $newActive->getActMembers($id, $db_perpage, ($page - 1) * $db_perpage);

	require_once PrintEot('thread_active');footer();

} elseif ($job == 'post' || $job == 'edit') {
	
	!$ifadmin && Showmsg('undefined_action');
	$active = array();

	$tmpUrlAdd = '&job=' . $job;
	if ($job == 'edit') {
		S::gp(array('id'));
		$tmpUrlAdd .= '&id=' . $id;
		require_once(A_P . 'groups/lib/active.class.php');
		$newActive = new PW_Active();
		if (!$active = $newActive->getActiveById($id)) {
			Showmsg('data_error');
		}
	}
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

	if (empty($_POST['step'])) {
		
		if ($active) {
			$active = $newActive->convert($active);
			$atc_content = $active['content'];
			${'typeCheck_' . $active['type']} = ' checked';
			${'objecterCheck_' . $active['objecter']} = ' checked';
		} else {
			$typeCheck_6 = $objecterCheck_0 = ' checked';
		}
		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
		$attachAllow = pwJsonEncode($db_uploadfiletype);
		$imageAllow = pwJsonEncode(getAllowKeysFromArray($db_uploadfiletype, array('jpg','jpeg','gif','png','bmp')));
		$attachUrl = 'pweditor.php?action=attach';

		$attach = '';
		if ($job == 'edit' && $attachdb = $newActive->getAttById($id)) {
			foreach ($attachdb as $key => $value) {
				list($value['attachurl'],) = geturl($value['attachurl'], 'lf');
				$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
			}
			$attach = rtrim($attach,',');
		}
		if ($db_allowupload && $_G['allowupload']) {
			$attachsService = L::loadClass('attachs', 'forum');
			$mutiupload = intval($attachsService->countMultiUpload($winduid));
		}
		require_once PrintEot('thread_active');footer();

	} else {

		S::gp(array('title', 'begintime', 'endtime', 'deadline', 'address', 'price','introduction','atc_content','limitnum'), 'P');
		S::gp(array('type', 'objecter'), 'P', 2);

		if(strlen($title) > $db_titlemax) Showmsg('active_title_length');
		if(strlen($address) > $db_titlemax) Showmsg('active_address_length');
		if(strlen($introduction) > 130) Showmsg('active_introduction_length');
		if(strlen($atc_content) > 50000) Showmsg('active_atc_content_length');

		require_once(A_P . 'groups/lib/activepost.class.php');

		$activePost = new PwActivePost($cyid);
		if ($job == 'edit') {
			$activePost->initData($active);
			if ($attachdb = $newActive->getAttById($id)) {
				S::gp(array('keep'), 'P', 2);
				S::gp(array('oldatt_desc'), 'P');
				$activePost->initAttachs($attachdb, $keep, $oldatt_desc);
			}
		}
		$activePost->setTitle($title);
		$activePost->setContent($atc_content);
		$activePost->setType($type);
		$activePost->setActiveTime($begintime, $endtime, $deadline);
		$activePost->setAddress($address);
		$activePost->setLimitnum($limitnum);
		$activePost->setObjecter($objecter);
		$activePost->setPrice($price);
		$activePost->setIntroduction($introduction);

		if (($ret = $activePost->checkData()) !== true) {
			Showmsg($ret);
		}
		if ($job == 'edit') {
			$activePost->updateData($id);
			refreshto("{$basename}&job=view&id=$id",'活动编辑成功!');
		} else {
			require_once(R_P . 'u/require/core.php');
			$id = $activePost->insertData();
			//activitynum加一
			//* $db->update("UPDATE pw_colonys SET activitynum=activitynum+'1' WHERE id=" . S::sqlEscape($cyid));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET activitynum=activitynum+'1' WHERE id=:id", array('pw_colonys', intval($cyid))));
			$colony['activitynum']++;
			updateGroupLevel($colony['id'], $colony);
			refreshto("{$basename}&job=view&id=$id",'活动发布成功!');
		}
	}
} elseif ($job == 'join') {
	
	define('AJAX', 1);
	ob_end_clean();
	ObStart();
	!$winduid && Showmsg('not_login');

	S::gp(array('id'));
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if (!$active = $newActive->getActiveById($id)) {
		Showmsg('data_error');
	}
	if (($return = $newActive->checkJoinStatus($id, $winduid)) !== true) {
		Showmsg($return);
	}
	if (empty($_POST['step'])) {
		
		require_once PrintEot('thread_active_ajax');
		ajax_footer();

	} else {

		S::gp(array('realname','phone','mobile','address','anonymous'));
			
		$return = $newActive->appendMember($id, $winduid, array(
			'realname'	=> $realname,
			'phone'		=> $phone,
			'mobile'	=> $mobile,
			'address'	=> $address,
			'anonymous'	=> $anonymous
		));
		$return !== true && Showmsg($return);

		Showmsg("报名成功\treload");
	}
} elseif ($job == 'quit') {

	define('AJAX', 1);
	ob_end_clean();
	ObStart();

	S::gp(array('id'));
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if (!$active = $newActive->getActiveById($id)) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		
		require_once PrintEot('thread_active_ajax');
		ajax_footer();

	} else {

		$newActive->quitActive($id, $winduid);
		Showmsg("退出成功!\treload");
	}
} elseif ($job == 'del') {
	
	if (empty($_POST)) {
		define('AJAX', 1);
		ob_end_clean();
		ObStart();
	}
	S::gp(array('id'));
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if (!$active = $newActive->getActiveById($id)) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		
		require_once PrintEot('thread_active_ajax');
		ajax_footer();

	} else {
		
		if ($winduid != $active['uid']) {
			Showmsg('您不是活动的创建者，无权取消！');
		}
		$newActive->delActive($id);

		refreshto("{$basename}", '取消成功!');
	}
}

?>
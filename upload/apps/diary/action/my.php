<?php
!defined('A_P') && exit('Forbidden');
//TODO 删除不用的请求处理分支，分离出ajax请求
if(!$winduid){ 
	S::gp(array('a','q','did'));
	$did = (int)S::getGP('did');
	('detail' !== $a) && ('diary' !== $q) && !is_numeric($did) && Showmsg("not_login");
	$diaryService = L::loadClass('Diary', 'diary');
	$diaryTemp = $diaryService->get($did);
	$url = $db_bbsurl."/apps.php?q=diary&a=detail&did=".$did."&uid=".$diaryTemp['uid'];
	ObHeader($url);
}
$USCR = 'user_diary';
//TODO 暂时调用		

S::gp(array('s','diraryAjax'));
if ($diraryAjax == 1) define('AJAX', '1');

$a = isset($a) ? $a : 'list';

$basename = 'apps.php?q='.$q.'&';
$temp_basename = 'apps.php?q='.$q.'&a='.$a.'&';

if ($a == 'list') {//我的日志列表

	$dtid = (int)S::getGP('dtid');//TODO 查看日志分类ID
	$diaryTypeId = $dtid == '-1' ? 0 : (is_numeric($dtid) && $dtid > 0 ? $dtid : null);
	$diaryPrivacy = $dtid == '-2' ? array(2) : array();
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($winduid, 0);	//TODO	右侧分类Start
	$count = (int)$diaryService->countUserDiarys($winduid, $diaryTypeId, $diaryPrivacy);
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$diaryDb = ($count) ? $diaryService->findUserDiarysInPage($winduid, $page, $db_perpage, $diaryTypeId, $diaryPrivacy) : array();
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$basename}a=$a&dtid=$dtid&");

} elseif ($a == 'detail') {//查看我的日志
	$stylepath = L::style('stylepath');
	$did = (int)S::getGP('did');
	$fuid = (int)S::getGP('fuid');
	!$did && Showmsg("日志不存在");
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($winduid, 0);//TODO	右侧分类Start
	$diaryTemp = $diaryService->get($did);
	if ($diaryTemp['uid'] != $winduid) {//转跳处理
		$url = $db_bbsurl."/apps.php?q=diary&a=detail&did=".$did."&uid=".$diaryTemp['uid'];
		ObHeader($url);
	}
	$diary = $diaryService->getDiaryDbView($diaryTemp);
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('diary',$did,$page,"{$basename}a={$a}&did={$did}&#createcommentbox");
	$comment_type = 'diary';
	$comment_typeid = $did;
	$myOuserData = array();
	$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	$myOuserData = $ouserdataService->get($winduid);
	$weiboPriv = false;
	if (!$myOuserData['index_privacy'] && !$myOuserData['diary_privacy'] && !$diary['privacy']){
		$weiboPriv = true;
	}

	$diaryNextName=getNextOrPreDiaryName($did, $fuid,'next');
	$diaryPreName=getNextOrPreDiaryName($did, $fuid,'pre');
} elseif ($a == 'friendslists') {//好友日志列表

	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	$friendsUids = array();
	$friendsUids = $friendsService->findFriendsByUid($winduid);
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$count = (int)$diaryService->countFriendsDiarys($friendsUids);
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$diaryDb = ($count) ? $diaryService->findFriendsDiarysInPage($friendsUids, $page, $db_perpage) : array();
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$pages = numofpage($count, $page, ceil($count/$db_perpage), $basename."a=$a&");

} elseif ($a == 'friendlist') {//单个好友列表

	S::gp(array('fuid', 'dtid'));
	!$fuid && Showmsg('好友不存在');

	$ouserPrivacy = array();
	if ($isGM) {
		$ouserPrivacy['index'] = true;
		$ouserPrivacy['diary'] = true;
	} else{
		$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
		$ouserDb = array();
		$ouserDb = $ouserdataService->get($fuid);
		list(,$ouserPrivacy) = pwUserPrivacy($fuid,$ouserDb);
	}
	!$ouserPrivacy['index'] &&  Showmsg('该朋友的空间设置了查看权限');
	!$ouserPrivacy['diary'] &&  Showmsg('该朋友的日志设置了查看权限');

	$diaryTypeId = ($dtid == '-1') ? 0 : ( (is_numeric($dtid) && $dtid > 0) ? $dtid : null );
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	if ($friendsService->isFriend($winduid,$fuid) !== true) Showmsg('好友不存在');
	$diaryPrivacy = array(0,1);
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($fuid, $diaryPrivacy);//TODO	右侧分类Start
	$count = (int)$diaryService->countUserDiarys($fuid, $diaryTypeId, $diaryPrivacy);
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$diaryDb = ($count) ? $diaryService->findUserDiarysInPage($fuid, $page, $db_perpage, $diaryTypeId, $diaryPrivacy) : array();
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$basename}a=$a&fuid=$fuid&dtid=$dtid&");

} elseif ($a == 'frienddetail') {//查看好友日志

	S::gp(array('did', 'fuid'));
	!$did && Showmsg("日志不存在");
	!$fuid && Showmsg("好友不存在");

	$ouserPrivacy = array();
	if ($isGM) {
		$ouserPrivacy['index'] = true;
		$ouserPrivacy['diary'] = true;
	} else{
		$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
		$ouserDb = array();
		$ouserDb = $ouserdataService->get($fuid);
		list(,$ouserPrivacy) = pwUserPrivacy($fuid,$ouserDb);
	}
	!$ouserPrivacy['index'] &&  Showmsg('该朋友的空间设置了查看权限');
	!$ouserPrivacy['diary'] &&  Showmsg('该朋友的日志设置了查看权限');

	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$diaryTemp = $diaryService->get($did);

	$diaryTemp['uid'] != $fuid && Showmsg('日志不存在');
	$diaryTemp['privacy'] == 2 && Showmsg("日志不存在");
	$diary = $diaryService->getDiaryDbView($diaryTemp);

	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	if ($friendsService->isFriend($winduid,$fuid) !== true) Showmsg('好友不存在');
	$diaryPrivacy = array(0,1);

	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($fuid, $diaryPrivacy);

	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('diary',$did,$page,"{$basename}a={$a}&fuid={$fuid}&did={$did}&");
	$comment_type = 'diary';
	$comment_typeid = $did;

	$myOuserData = array();
	$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	$myOuserData = $ouserdataService->get($fuid);
	$weiboPriv = false;
	if (!$myOuserData['index_privacy'] && !$myOuserData['diary_privacy'] && !$diary['privacy']){
		$weiboPriv = true;
	}
	$friendDiaryNextName=getNextOrPreDiaryName($did, $fuid,'next');
	$friendDiaryPreName=getNextOrPreDiaryName($did, $fuid,'pre');
} elseif ($a == 'write') {

	//权限设置
	/**
	* 禁止受限制用户发言
	*/
	banUser();
	/*
	* 新注册会员发日志时间限制
	*/
	if ($db_postallowtime && $timestamp - $winddb['regdate'] < $db_postallowtime*60) {
		Showmsg('post_newd_limit');
	}

	/*
	* 用户组发日志权限限制
	*/
	if ($groupid != 3 && $o_diary_groups && strpos($o_diary_groups,",$groupid,") === false) {
		Showmsg('diary_group_right');
	}

	/*
	* 灌水机制
	*/
	$endtime = $tdtime + 24*3600;
	$postdate = $db->get_value("SELECT postdate FROM pw_diary WHERE uid=".S::sqlEscape($winduid)." ORDER BY postdate DESC LIMIT 1");
	$todaycount = $db->get_value("SELECT COUNT(*) as count FROM pw_diary WHERE uid=".S::sqlEscape($winduid)." AND postdate>=".S::sqlEscape($tdtime)." AND postdate<".S::sqlEscape($endtime));

	$tdtime  >= $postdate && $todaycount = 0;

	if ($groupid != 3 && $o_diarylimit && $todaycount >= $o_diarylimit) {
		Showmsg('diary_gp_limit');
	}

	if ($groupid != 3 && $o_diarypertime && $timestamp >= $postdate && $timestamp - $postdate <= $o_diarypertime) {
		Showmsg('diary_limit');
	}
	//权限设置

	$db_uploadfiletype = $o_uploadsize = !empty($o_uploadsize) ? unserialize($o_uploadsize) : array();
	$imageAllow = pwJsonEncode($db_uploadfiletype);

	$myAppsData = array();
	$ouserDataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserDataService PW_Ouserdata */
	$myAppsData = $ouserDataService->get($winduid);

	$appsDiaryPrivacy = false;
	$myAppsData['index_privacy'] < 1 && $myAppsData['diary_privacy'] < 1 && $appsDiaryPrivacy = true;


	$sendWeiboPrivacy = $appsDiaryPrivacy;
	$weibocheck = $appsDiaryPrivacy === true ? 'checked=checked' : '';
	$weibodisplay = $appsDiaryPrivacy === true ? '' : 'style="display:none"';

	if (!$_POST['step']) {

		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
		$dtsel = '';
		$query = $db->query("SELECT * FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid)." ORDER BY dtid");
		while ($rt = $db->fetch_array($query)) {
			$dtsel .= "<option value=\"$rt[dtid]\">$rt[name]</option>";
		}
		$convertChecked = $checked = 'checked';
		$disabled = '';

	} elseif ($_POST['step'] == 2) {

		S::gp(array("privacy"));
		require_once(R_P.'require/postfunc.php');
		PostCheck(1,$o_diary_gdcheck,$o_diary_qcheck);
		S::gp(array('dtid','privacy','ifcopy','ifsendweibo','flashatt','atc_title'),'P');
		require_once(R_P.'require/bbscode.php');

		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($_POST['atc_title'])) !== false) {
			Showmsg('diary_title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($_POST['atc_content'], false)) !== false) {
			Showmsg('diary_content_wordsfb');
		}
		if (!$atc_title) $_POST['atc_title'] = get_date($timestamp,'Y.m.d').' 日志';
		list($atc_title,$atc_content,$ifconvert,$ifwordsfb) = check_data('new');
		if ($db_tcheck) { //内容验证
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userDataInfo = $userService->get($winduid, false, true, false);
			$postcheck = unserialize($userDataInfo['postcheck']);
			$postcheck['diary'] == ($diaryCheck = tcheck($atc_content)) && Showmsg('diary_content_same');
		}
		//$db_tcheck && $winddb['postcheck'] == tcheck($atc_content) && Showmsg('diary_content_same'); //内容验证
		$dtid = (int)$dtid;
		$privacy = (int)$privacy;
		$ifcopy = (int)$ifcopy;
		$ifupload = 0;
//		!$privacy && $ifcopy = 1;
		
		$aids = $attachs = array();
		L::loadClass('diaryupload', 'upload', false);
		if (PwUpload::getUploadNum() || $flashatt) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$diaryUpload = new DiaryUpload($winduid, $flashatt, $savetoalbum, $albumid);
			$diaryUpload->check();
			PwUpload::upload($diaryUpload);
			$aids = $diaryUpload->getAids();
			$attachs = $diaryUpload->getAttachs();
			$attachIds = $diaryUpload->getAttachIds();
			$ifupload = $diaryUpload->ifupload;
		}
       /**
		$pwSQL = S::sqlSingle(array(
			'uid'		=> $winduid,
			'dtid'		=> $dtid,
			'aid'		=> (!empty($attachs) ? addslashes(serialize($attachs)) : ''),
			'username'	=> $windid,
			'privacy'	=> $privacy,
			'subject'	=> $atc_title,
			'content'	=> $atc_content,
			'ifcopy'	=> $ifcopy,
			'ifconvert'	=> $ifconvert,
			'ifupload'	=> $ifupload,
			'ifwordsfb'	=> $ifwordsfb,
			'postdate'	=> $timestamp,
		));
		$db->update("INSERT INTO pw_diary SET $pwSQL");**/
		$pwSQL = array(
			'uid'		=> $winduid,
			'dtid'		=> $dtid,
			'aid'		=> (!empty($attachs) ? addslashes(serialize($attachs)) : ''),
			'username'	=> $windid,
			'privacy'	=> $privacy,
			'subject'	=> $atc_title,
			'content'	=> $atc_content,
			'ifcopy'	=> $ifcopy,
			'ifconvert'	=> $ifconvert,
			'ifupload'	=> $ifupload,
			'ifwordsfb'	=> $ifwordsfb,
			'postdate'	=> $timestamp,
		);
		pwQuery::insert('pw_diary', $pwSQL);
		$did = $db->insert_id();
		$db->update("UPDATE pw_diarytype SET num=num+1 WHERE uid=".S::sqlEscape($winduid)." AND dtid=".S::sqlEscape($dtid));//更新分类日志数

		if ($aids) {
			$diaryService = L::loadClass('Diary', 'diary');
			$diaryService->updateDiaryContentByAttach($did, $attachIds);
			$db->update("UPDATE pw_attachs SET did=" . S::sqlEscape($did) . " WHERE aid IN(" . S::sqlImplode($aids) . ")");
		}

		if (!$privacy && !$myAppsData['index_privacy'] && !$myAppsData['diary_privacy']) {
			$userCache = L::loadClass('Usercache', 'user');
			$userCache->delete($winduid, 'carddiary');
			updateDatanalyse($did,'diaryNew',$timestamp);
			if ($sendWeiboPrivacy && $ifsendweibo) {
				$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
				$atc_content = substrs(stripWindCode($weiboService->escapeStr($atc_content)), 125);
				$weiboExtra = array(
					'did' => $did,
					'title' => stripslashes($atc_title),
				);
				$weiboService->send($winduid,$atc_content,'diary',$did,$weiboExtra);
			}
		}
		countPosts('+1');
		//积分变动
		require_once(R_P.'require/credit.php');
		$o_diary_creditset = unserialize($o_diary_creditset);
		$creditset = getCreditset($o_diary_creditset['Post']);
		$creditset = array_diff($creditset,array(0));
		if (!empty($creditset)) {
			$credit->sets($winduid,$creditset,true);
			updateMemberid($winduid);
		}

		if ($creditlog = unserialize($o_diary_creditlog)) {
			addLog($creditlog['Post'],$windid,$winduid,'diary_Post');
		}
		updateUserAppNum($winduid,'diary');
		if ($db_tcheck) {
			$postcheck['diary'] = $diaryCheck;
			$userService->update($winduid, array(), array('postcheck' => serialize($postcheck)));
		}
		$url = "{$basename}a=detail&did=$did";
		$msg = defined('AJAX') ?  "success\t".$url : 'operate_success';	
		// defend start
		CloudWind::YunPostDefend ( $winduid, $windid, $groupid, $did, $atc_title, $atc_content, 'diary' );
		// defend end
		refreshto($url,$msg);
	}
} elseif ($a == 'edit') {

	$db_uploadfiletype = $o_uploadsize = !empty($o_uploadsize) ? unserialize($o_uploadsize) : array();
	$imageAllow = pwJsonEncode($db_uploadfiletype);

	$sendWeiboPrivacy = false;

	if (!$_POST['step']) {

		$did = (int)S::getGP('did');
		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
		$dtsel = '';
		$diary = $db->get_one("SELECT did,dtid,aid,privacy,subject,content,ifcopy,ifconvert FROM pw_diary WHERE uid=".S::sqlEscape($winduid)." AND did=".S::sqlEscape($did));

		!$diary && Showmsg('illegal_request');
		$attach = '';
		if ($diary['aid']) {
			$attachs = unserialize($diary['aid']);
			if (is_array($attachs)) {
				foreach ($attachs as $key => $value) {
					list($value['attachurl'],) = geturl($value['attachurl'], 'lf');
					$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[desc]'],";
				}
				$attach = rtrim($attach,',');
			}
		}

		$atc_content = $diary['content'];
		${'privacy_'.$diary['privacy']} = 'selected';
		$diary['ifcopy'] && $checked = 'checked';


		$diary['ifconvert'] == 2 && $convertChecked = 'checked';


		($diary['privacy'] == '2') && $disabled = 'disabled';

		$query = $db->query("SELECT * FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid)." ORDER BY dtid");
		while ($rs = $db->fetch_array($query)) {
			$selected = '';
			$rs['dtid'] == $diary['dtid'] && $selected .= 'selected';
			$dtsel .= "<option value=\"$rs[dtid]\" $selected>$rs[name]</option>";
		}
		if (strpos($atc_content,$db_bbsurl) !== false) {
			$atc_content = str_replace('p_w_picpath',$db_picpath,$atc_content);
			$atc_content = str_replace('p_w_upload',$db_attachname,$atc_content);
		}
		
	} elseif ($_POST['step'] == 2) {

		S::gp(array('did','dtid','dtided','privacy','privacyed','ifcopy','flashatt'),'P');

		require_once(R_P.'require/bbscode.php');
		require_once(R_P.'require/postfunc.php');
		PostCheck(1,$o_diary_gdcheck,$o_diary_qcheck);

		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($_POST['atc_title'])) !== false) {
			Showmsg('diary_title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($_POST['atc_content'], false)) !== false) {
			Showmsg('diary_content_wordsfb');
		}

		list($atc_title,$atc_content,$ifconvert,$ifwordsfb) = check_data('modify');
		//$db_tcheck && $winddb['postcheck'] == tcheck($atc_content) && Showmsg('diary_content_same'); //内容验证

		$dtid = (int)$dtid;
		$dtided = (int)$dtided;
		$privacy = (int)$privacy;
		$ifcopy = (int)$ifcopy;
		$ifupload = 0;

		/**
		* 附件修改
		*/
		$oldattach = $replacedb = $unsetattach = array();

		$aid = $db->get_value("SELECT aid FROM pw_diary WHERE uid=".S::sqlEscape($winduid)." AND did=".S::sqlEscape($did));

		if ($aid) {
			S::gp(array('oldatt_desc'), 'P');
			$oldattach = unserialize(stripslashes($aid));
			foreach ($oldattach as $key => $value) {
				$v = array(
					'special'	=> 0,		'ctype'		=> '',
					'needrvrc'	=> 0,		'desc'		=> $oldatt_desc[$key]
				);
				$oldattach[$key] = array_merge($oldattach[$key], $v);

				if (array_key_exists('replace_'.$key, $_FILES)) {
					$db_attachnum++;
					$replacedb[$key] = $oldattach[$key];
				} elseif ($value['desc'] <> $v['desc']) {
					$runsql[] = 'UPDATE pw_attachs SET ' . S::sqlSingle(array(
						'needrvrc'	=> $v['needrvrc'],
						'descrip'	=> $v['desc'],
						'special'	=> $v['special'],
						'ctype'		=> $v['ctype']
					)) . ' WHERE aid=' . S::sqlEscape($key);
				}
			}
		}

		$aids = $attachs = array();
		L::loadClass('diaryupload', 'upload', false);
		if (PwUpload::getUploadNum() || $flashatt) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$diaryUpload = new DiaryUpload($winduid, $flashatt, $savetoalbum, $albumid);
			$diaryUpload->check();
			$diaryUpload->setReplaceAtt($replacedb);
			PwUpload::upload($diaryUpload);
			$aids = $diaryUpload->getAids();
			$attachs = $diaryUpload->getAttachs();
			$attachIds = $diaryUpload->getAttachIds();
			$ifupload = $diaryUpload->ifupload;
			if ($oldattach && $diaryUpload->replacedb) {
				foreach ($diaryUpload->replacedb as $key => $value) {
					$oldattach[$key] = $value;
				}
			}
		}

		if ($attachs) {
			foreach ($attachs as $key => $value) {
				$oldattach[$key] = $value;
			}
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->update($winduid, array(), array('uploadtime'=>$winddb['uploadtime'], 'uploadnum'=>$winddb['uploadnum']));
		}

		if ($oldattach) {
			$oldattach = addslashes(serialize($oldattach));
		} else {
			$oldattach = '';
		}
		/**
		* 附件修改
		*/
       /**       
		$pwSQL = S::sqlSingle(array(
			'dtid'		=> $dtid,
			'aid'		=> $oldattach,
			'privacy'	=> $privacy,
			'subject'	=> $atc_title,
			'content'	=> $atc_content,
			'ifcopy'	=> $ifcopy,
			'ifconvert'	=> $ifconvert,
			'ifupload'	=> $ifupload,
			'ifwordsfb'	=> $ifwordsfb,
		));
		$db->update("UPDATE pw_diary SET $pwSQL WHERE uid=".S::sqlEscape($winduid)." AND did=".S::sqlEscape($did));
		**/
		$pwSQL = array(
			'dtid'		=> $dtid,
			'aid'		=> $oldattach,
			'privacy'	=> $privacy,
			'subject'	=> $atc_title,
			'content'	=> $atc_content,
			'ifcopy'	=> $ifcopy,
			'ifconvert'	=> $ifconvert,
			'ifupload'	=> $ifupload,
			'ifwordsfb'	=> $ifwordsfb,
		);
		pwQuery::update('pw_diary', 'uid =:uid AND did =:did', array($winduid, $did), $pwSQL);

		if ($aids) {
			$diaryService = L::loadClass('Diary', 'diary');
			$diaryService->updateDiaryContentByAttach($did, $attachIds);
			$db->update("UPDATE pw_attachs SET did=" . S::sqlEscape($did) . " WHERE aid IN(" . S::sqlImplode($aids) . ")");
		}

		if ($dtided != $dtid) {
			$db->update("UPDATE pw_diarytype SET num=num-1 WHERE uid=".S::sqlEscape($winduid)." AND dtid=".S::sqlEscape($dtided));
			$db->update("UPDATE pw_diarytype SET num=num+1 WHERE uid=".S::sqlEscape($winduid)." AND dtid=".S::sqlEscape($dtid));
		}

		if ($privacyed == 2 && $privacy !=2) {
			countPosts('+1');
		} elseif ($privacyed != 2 && $privacy ==2) {
			if ($affected_rows = delAppAction('diary',$did)) {
				countPosts("-$affected_rows");
			}
		}
		// defend start
		CloudWind::YunPostDefend ( $winduid, $windid, $groupid, $did, $atc_title, $atc_content, 'editdiary' );
		// defend end
		$url = "{$basename}a=detail&did=$did";
		$msg = defined('AJAX') ?  "success\t".$url : 'operate_success';	
		refreshto($url, $msg);
	}
} elseif ($a == 'copydiary') {
	define('AJAX', 1);
	define('F_M',true);
	banUser();
	S::gp(array('did'));

	empty($did) && Showmsg('data_error');

	$dtsel = '';
	$query = $db->query("SELECT * FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid)." ORDER BY dtid");
	while ($rt = $db->fetch_array($query)) {
		$dtsel .= "<option value=\"$rt[dtid]\">$rt[name]</option>";
	}
	require_once PrintEot('m_ajax');ajax_footer();

} elseif ($a == 'next') {

	define('AJAX',1);
	$did = (int)S::getGP('did');
	$fuid = (int)S::getGP('fuid');

	$uid = $fuid ? $fuid : $winduid;
	$sqladd = "WHERE uid=".S::sqlEscape($uid);
	if ($uid != $winduid) {
		$sqladd .= " AND privacy!=2 AND did>".S::sqlEscape($did);
		$basename = $basename."a=frienddetail&fuid=$uid&";
	} else {
		$sqladd .= " AND did>".S::sqlEscape($did);
		$basename = $basename."a=detail&";
	}
	$did = $db->get_value("SELECT MIN(did) FROM pw_diary $sqladd");
	echo "success\t$did\t$basename";
	ajax_footer();

} elseif ($a == 'pre') {

	define('AJAX',1);
	$did = (int)S::getGP('did');
	$fuid = (int)S::getGP('fuid');
	$uid = $fuid ? $fuid : $winduid;
	$sqladd = "WHERE uid=".S::sqlEscape($uid);
	if ($uid != $winduid) {
		$sqladd .= " AND privacy!=2 AND did<".S::sqlEscape($did);
		$basename = $basename."a=frienddetail&fuid=$uid&";
	} else {
		$sqladd .= " AND did<".S::sqlEscape($did);
		$basename = $basename."a=detail&";
	}

	$did = $db->get_value("SELECT MAX(did) FROM pw_diary $sqladd");
	echo "success\t$did\t$basename";
	ajax_footer();

}
if($s) require_once PrintEot('m_diary_bottom');
else require_once PrintEot('m_diary');

pwOutPut();



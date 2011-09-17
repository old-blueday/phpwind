<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('a'));

$pwforum = new PwForum($fid);
if (!$pwforum->isForum(true)) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$groupRight =& $newColony->getRight();
$pwModeImg = "$imgpath/apps";
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');

require_once(R_P . 'u/require/core.php');
require_once(R_P . 'require/header.php');
list($guidename, $forumtitle) = $pwforum->getTitle();
$msg_guide = $pwforum->headguide($guidename);

$styleid = $colony['styleid'];
$basename = "thread.php?cyid=$cyid&showtype=galbum";
if (empty($a)) {

	S::gp(array('page'), null, 2);
	$photonum = $db->get_value("SELECT SUM(photonum) AS photonum FROM pw_cnalbum WHERE ownerid=".S::sqlEscape($cyid));
	$photonum = (int)$photonum;
	$db_perpage = 10;
	list($pages,$limit) = pwLimitPages($colony['albumnum'],$page,"{$basename}&");
	$album = array();
	$query = $db->query("SELECT aid,aname,photonum,lastphoto,private,lasttime,memopen,crtime FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . " ORDER BY aid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['sub_aname'] = substrs($rt['aname'],16);
		$rt['lasttime'] = get_date($rt['lasttime'],'Y-m-d');
		$rt['crtime'] = get_date($rt['crtime'],'Y-m-d');
		$rt['forbidden'] = ($colony['albumopen'] && $rt['private'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1'));
		$rt['lastphoto'] = $rt['forbidden'] ? "u/images/n.gif" : getphotourl($rt['lastphoto']);
		$album[] = $rt;
		$rt['memopen'] == 1 && $colony['ifFullMember'] && $uploadAvaliable = true;
	}
	require_once PrintEot('thread_galbum');
	footer();

} elseif ($a == "photolist") {

	if (!$colony['albumopen'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	$photonum = $db->get_value("SELECT count(*) AS count FROM pw_cnphoto t LEFT JOIN pw_cnalbum m ON t.aid=m.aid WHERE atype='1' AND m.ownerid=".S::sqlEscape($cyid));
	$photonum || $photonum = 0;
	S::gp(array('page'), null, 2);
	$db_perpage = 21;
	list($pages,$limit) = pwLimitPages($photonum,$page,"{$basename}&a=$a&");
	$photolist= array();
	$query = $db->query("SELECT t.* FROM pw_cnphoto t LEFT JOIN pw_cnalbum m ON t.aid=m.aid WHERE atype='1' AND m.ownerid=".S::sqlEscape($cyid) . " ORDER BY t.pid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['photopath'] = getphotourl($rt['path']);
		$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['path'] = $pwModeImg.'/banuser.gif';
		}
		$rt['uptime'] = get_date($rt['uptime']);
		$rt['sub_pintro'] = substrs($rt['pintro'],25);
		$cnpho[] = $rt;
	}
	require_once PrintEot('thread_galbum');
	footer();

} elseif ($a == 'album') {

	S::gp(array('aid','page'), null, 2);

	$cnpho = array();
	$album = $db->get_one("SELECT aname,aintro,ownerid,photonum,private,memopen FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($aid));
	if (empty($album)) {
		Showmsg('data_error');
	}
	/*
	if ($colony['albumopen'] && $album['private'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	*/
	if ($album['private'] && !$ifadmin && !$colony['ifFullMember']) {
		Showmsg('colony_cnmenber');
	}
	$db_perpage = 21;
	list($pages,$limit) = pwLimitPages($album['photonum'],$page,"{$basename}&a=album&aid=$aid&");
	$tmpUrlAdd = '&a=album&aid=' . $aid;
	$query = $db->query("SELECT c.pid,c.path,c.ifthumb,c.pintro,c.uptime,c.uploader,m.groupid FROM pw_cnphoto c LEFT JOIN pw_members m ON c.uploader=m.username WHERE c.aid=" . S::sqlEscape($aid) . " ORDER BY c.pid $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['path'] = $pwModeImg.'/banuser.gif';
		}
		$rt['uptime'] = get_date($rt['uptime']);
		$rt['sub_pintro'] = substrs($rt['pintro'],25);
		$cnpho[] = $rt;
	}
//	require_once(R_P.'require/header.php');
	require_once PrintEot('thread_galbum');
	footer();

} elseif ($a == 'view') {

	require_once(R_P.'require/bbscode.php');

	S::gp(array('pid','page'), null, 2);

	$tmpUrlAdd = '&a=view&pid=' . $pid;
	$nearphoto = array();
	$register = array('db_shield'=>$db_shield,"groupid"=>$groupid,"pwModeImg"=>$pwModeImg);
	L::loadClass('showpicture', 'colony', false);
	$sp = new PW_ShowPicture($register);
	list($photo,$nearphoto,$prePid,$nextPid) = $sp->getGroupsPictures($pid,$aid);

	empty($photo) && Showmsg('data_error');

	if ($photo['private'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	$db->update("UPDATE pw_cnphoto SET hits=hits+1 WHERE pid=" . S::sqlEscape($pid));

	$aid = $photo['aid'];
	$photo['uptime'] = get_date($photo['uptime']);
	$photo['path'] = getphotourl($photo['basepath']);
	if ($photo['groupid'] == 6 && $db_shield && $groupid != 3) {
		$photo['path'] = $pwModeImg.'/banuser.gif';
		$photo['pintro'] = appShield('ban_photo_pintro');
	}
	$num = $db->get_value("SELECT COUNT(*) AS sum FROM pw_cnphoto WHERE aid=" . S::sqlEscape($photo['aid']) . ' AND pid>=' . S::sqlEscape($pid));
	$page = empty($page) ? 1 : $page;
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('groupphoto',$pid,$page,"thread.php?cyid=$cyid&showtype=galbum&a=view&pid=$pid&");

	$comment_type = 'groupphoto';
	$comment_typeid = $pid;
//	require_once(R_P.'require/header.php');
	require_once PrintEot('thread_galbum');
	footer();

} elseif ($a == 'upload') {

	if (!$ifadmin && !$colony['ifFullMember']) {
		Showmsg('colony_cnmenber');
	}
	banUser();
	S::gp(array('aid', 'job'));

	$tmpUrlAdd .= '&a=upload' . ($job ? '&job=' . $job : '') . '&aid=' . $aid;

	if (empty($_POST['step'])) {

		$extra_url = $options = '';
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid));
		if (empty($count)) {
			$db->update("INSERT INTO pw_cnalbum SET " . S::sqlSingle(array(
				'aname'		=> '默认相册',		'aintro'	=> '',
				'atype'		=> 1,				'private'	=> 0,
				'ownerid'	=> $cyid,			'owner'		=> $colony['cname'],
				'lasttime'	=> $timestamp,		'crtime'	=> $timestamp,
				'memopen'   => 1
			)));
			//* $db->update("UPDATE pw_colonys SET albumnum=albumnum+1 WHERE id=" . S::sqlEscape($cyid));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET albumnum=albumnum+1 WHERE id=:id", array('pw_colonys', intval($cyid))));
		}
		$query = $db->query("SELECT aid,aname,memopen FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . ' ORDER BY aid DESC');
		while ($rt = $db->fetch_array($query)) {
			if ($ifadmin || ($colony['ifFullMember'] && $rt['memopen'] == 1)) {
				$memopen = 1;
			} else {
				$memopen = 0;
			}
			if ($memopen == 1) {
				$options .= "<option value=\"$rt[aid]\"" . (($aid && $rt['aid'] == $aid) ? ' selected' : '') . ">$rt[aname]</option>";
			}
		}
		$aid && $extra_url = '&aid=' . $aid;
		require_once PrintEot('thread_galbum');
		footer();

	} else {

		S::gp(array('pintro'),'P');
		!$aid && Showmsg('colony_albumclass');

		PostCheck(1,$o_photos_gdcheck,$o_photos_qcheck && $db_question);
		empty($pintro) && $pintro = array();

		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		foreach ($pintro as $k => $v) {
			if (($banword = $wordsfb->comprise($v)) !== false) {
				Showmsg('content_wordsfb');
			}
		}
		$rt = $db->get_one("SELECT aname,photonum,ownerid,lastphoto,memopen FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($aid));
		if (empty($rt)) {
			Showmsg('undefined_action');
		} elseif ($cyid <> $rt['ownerid']) {
			Showmsg('colony_phototype');
		} elseif ($rt['memopen'] == 0 && !$ifadmin) {
			Showmsg('colony_album_memopen');
		}
		$rt['photonum'] >= $groupRight['maxphotonum'] && Showmsg('colony_photofull');

		L::loadClass('photoupload', 'upload', false);
		if ($rt['photonum'] + PwUpload::getUploadNum() > $groupRight['maxphotonum']) {
			$uploadlimit = $groupRight['maxphotonum'] - $rt['photonum'];
			$uploadlimit = $uploadlimit > 0 ? $uploadlimit : 0;
			Showmsg('uploadphoto_leave');
		}
		require_once(R_P . 'require/functions.php');
		$img = new PhotoUpload($aid);
		PwUpload::upload($img);
		pwFtpClose($ftp);

		if (!$photos = $img->getAttachs()) {
			Showmsg('colony_uploadnull');
		}
		$photoNum = count($photos);
		$pid = $img->getNewID();
		$lastpid = getLastPid($aid, 4);
		array_unshift($lastpid, $pid);

		$db->update("UPDATE pw_cnalbum SET photonum=photonum+" . S::sqlEscape($photoNum) . ",lasttime=" . S::sqlEscape($timestamp) . ',lastpid=' . S::sqlEscape(implode(',',$lastpid)) . (!$rt['lastphoto'] ? ',lastphoto='.S::sqlEscape($img->getLastPhoto()) : '') . " WHERE aid=" . S::sqlEscape($aid));
		countPosts("+$photoNum");

		require_once(R_P.'apps/groups/lib/group.class.php');
		$colony = getGroupByCyid($cyid);

		//* $db->update("UPDATE pw_colonys SET photonum=photonum+" . S::sqlEscape($photoNum) . " WHERE id=" . S::sqlEscape($cyid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum+:photonum WHERE id=:id", array('pw_colonys',$photoNum,intval($cyid))));

		$colony['photonum']+=$photoNum;
		updateGroupLevel($colony['id'], $colony);
		//积分变动
		require_once(R_P.'require/credit.php');
		$creditset = getCreditset($o_groups_creditset['Uploadphoto']);
		$creditset = array_diff($creditset,array(0));
		if (!empty($creditset)) {
			$credit->sets($winduid,$creditset,true);
			updateMemberid($winduid);
			addLog($creditlog,$windid,$winduid,'groups_Uploadphoto');
		}

		if ($creditlog = $o_groups_creditlog) {
			addLog($creditlog['Post'],$windid,$winduid,'groups_Uploadphoto');
		}
		refreshto("{$basename}&a=view&pid=$pid",'operate_success');
	}
} elseif ($a == 'selalbum') {

	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	S::gp(array('page', 'selaid','aid'), null, 2);
	$tmpUrlAdd .= '&aid='.$aid.'&a=selalbum&selaid='.$selaid;
	$calbum = $db->get_one("SELECT aid,aname,memopen,photonum FROM pw_cnalbum WHERE atype='1' AND aid=".S::sqlEscape($aid));

	//if($calbum['memopen']==0 && !$ifadmin){
		//Showmsg('colony_album_memopen');
	//}

	$db_perpage = 10;
	$total = $db->get_value("SELECT COUNT(*) FROM pw_cnalbum WHERE atype='0' AND ownerid=" . S::sqlEscape($winduid));
	list($pages,$limit) = pwLimitPages($total, $page, "{$basename}&a=$a&");

	$album = array();
	$query = $db->query("SELECT aid,aname,photonum,lastphoto FROM pw_cnalbum WHERE atype='0' AND ownerid=" . S::sqlEscape($winduid) . " ORDER BY aid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['sub_aname'] = substrs($rt['aname'],18);
		$rt['lastphoto'] = getphotourl($rt['lastphoto']);
		$album[] = $rt;
	}
	//增加select选项列表
	$options = '';
	$calbum = Array();
	$query = $db->query("SELECT aid,aname,photonum,memopen FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . ' ORDER BY aid DESC');

	while ($rt = $db->fetch_array($query)) {
		if ($rt['aid'] == $aid) {
			$calbum = $rt;
		}
		if ($ifadmin || $rt['memopen']==1) {
			$memopen = 1;
		} else {
			$memopen = 0;
		}
		if ($memopen == 1) {
			$options .= "<option value=\"$rt[aid]\"" . ($rt['aid'] == $selaid ? ' selected' : '') . ">$rt[aname]</option>";
		}
	}

	require_once PrintEot('thread_galbum');
	footer();

} elseif ($a == 'selphoto') {

	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	S::gp(array('aid', 'selaid', 'page'));
	$tmpUrlAdd .= '&aid='.$aid.'&a=selphoto&selaid='.$selaid;
	$album = $db->get_one("SELECT aname,ownerid,photonum FROM pw_cnalbum WHERE atype='0' AND aid=" . S::sqlEscape($aid));
	if (empty($album) || $album['ownerid'] != $winduid) {
		Showmsg('data_error');
	}

	if (empty($_POST['step'])) {

		$db_perpage = 10;
		list($pages,$limit) = pwLimitPages($album['photonum'],$page,"{$basename}&a=selphoto&aid=$aid&selaid=$selaid&");

		$options = '';
		$query = $db->query("SELECT aid,aname,memopen FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . ' ORDER BY aid DESC');
		while ($rt = $db->fetch_array($query)) {
			if ($ifadmin || $rt['memopen'] == 1) {
				$memopen = 1;
			} else {
				$memopen = 0;
			}
			if ($memopen == 1) {
				$options .= "<option value=\"$rt[aid]\"" . ($rt['aid'] == $selaid ? ' selected' : '') . ">$rt[aname]</option>";
			}
		}
		$cnpho = array();
		$query = $db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid) . " ORDER BY pid $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
			$cnpho[] = $rt;
		}
		require_once PrintEot('thread_galbum');
		footer();

	} else {

		S::gp(array('selid'));
		if (!$selid || !is_array($selid)) {
			Showmsg('colony_select_photo');
		}
		if (empty($selaid)) {
			Showmsg('colony_albumclass');
		}

		$selalbum = $db->get_one("SELECT aname,photonum,ownerid,lastphoto,memopen FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($selaid));
		if (empty($selalbum)) {
			Showmsg('undefined_action');
		} elseif ($cyid <> $selalbum['ownerid']) {
			Showmsg('colony_phototype');
		} elseif ($selalbum['memopen']==0&&!$ifadmin){
			Showmsg('colony_album_memopen');
		}

		$selalbum['photonum'] >= $groupRight['maxphotonum'] && Showmsg('colony_photofull');

		if ($selalbum['photonum'] + count($selid) > $groupRight['maxphotonum']) {
			$uploadlimit = $groupRight['maxphotonum'] - $selalbum['photonum'];
			$uploadlimit = $uploadlimit > 0 ? $uploadlimit : 0;
			Showmsg('uploadphoto_leave');
		}
		L::loadClass('upload', '', false);

		$savedir = 'photo/';
		if ($o_mkdir == '2') {
			$savedir .= 'Day_' . date('ymd') . '/';
		} elseif ($o_mkdir == '3') {
			$savedir .= 'Cyid_' . $selaid . '/';
		} else {
			$savedir .= 'Mon_'.date('ym') . '/';
		}
		$lastphoto = '';
		$i = 1;
		$photos = array();
		$query = $db->query("SELECT * FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid) . ' AND pid IN(' . S::sqlImplode($selid) . ')');
		while ($rt = $db->fetch_array($query)) {
			if (file_exists($attachdir . '/' . $rt['path'])) {
				$ext = strtolower(substr(strrchr($rt['path'],'.'),1));
				$prename  = randstr(4) . $timestamp . substr(md5($timestamp . ($i++) . randstr(8)),10,15);
				$filename = $selaid . "_$prename." . $ext;
				PwUpload::createFolder($attachdir . '/' . $savedir);
				$ifthumb = 0;
				if (!@copy($attachdir . '/' . $rt['path'], $attachdir . '/' . $savedir . $filename)) {
					continue;
				}
				if ($rt['ifthumb']) {
					$lastpos = strrpos($rt['path'],'/') + 1;
					$path = $attachdir . '/' . substr($rt['path'], 0, $lastpos) . 's_' . substr($rt['path'], $lastpos);
					if (copy($path, $attachdir . '/' . $savedir . 's_' . $filename)) {
						$ifthumb = 1;
					}
				}
				$path = $savedir . $filename;
			} else {
				$path = $rt['path'];
				$ifthumb = $rt['ifthumb'];
			}
			$photos[] = array(
				'aid'		=> $selaid,
				'pintro'	=> '',
				'path'		=> $path,
				'uploader'	=> $windid,
				'uptime'	=> $timestamp,
				'ifthumb'	=> $ifthumb
			);
			$lastphoto = $path;
		}

		if ($photos) {

			$db->update("INSERT INTO pw_cnphoto (aid,pintro,path,uploader,uptime,ifthumb) VALUES " . S::sqlMulti($photos));
			$pid = $db->insert_id();

			$photoNum = count($photos);
			$lastpid = getLastPid($selaid, 4);
			array_unshift($lastpid, $pid);

			$db->update("UPDATE pw_cnalbum SET photonum=photonum+" . S::sqlEscape($photoNum) . ",lasttime=" . S::sqlEscape($timestamp) . ',lastpid=' . S::sqlEscape(implode(',',$lastpid)) . (!$selalbum['lastphoto'] ? ',lastphoto='.S::sqlEscape($lastphoto) : '') . " WHERE aid=" . S::sqlEscape($selaid));
			countPosts("+$photoNum");
			require_once(R_P . 'require/functions.php');
			require_once(R_P.'require/credit.php');
			$creditset = getCreditset($o_groups_creditset['Uploadphoto']);
			$creditset = array_diff($creditset,array(0));
			if (!empty($creditset)) {
				$credit->sets($winduid,$creditset,true);
				updateMemberid($winduid);
				addLog($creditlog,$windid,$winduid,'groups_Uploadphoto');
			}
			if ($creditlog = $o_groups_creditlog) {
				addLog($creditlog['Post'],$windid,$winduid,'groups_Uploadphoto');
			}
			refreshto("{$basename}&a=view&pid=$pid",'operate_success');

		} else {
			refreshto("{$basename}&a=album&aid=$selaid",'operate_success');
		}
	}
} elseif ($a == 'edit') {

	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}

	banUser();
	S::gp(array('aid'), null, 2);
	empty($aid) && Showmsg('data_error');
	!$ifadmin && Showmsg('undefined_action');

	$rt = $db->get_one("SELECT aid,aname,aintro,private,memopen FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid) . " AND atype='1' AND ownerid=" . S::sqlEscape($cyid));
	if (empty($rt)) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		$tmpUrlAdd .= '&aid='.$aid.'&a=edit';
		$extra_url = '&a=album&aid=' . $aid;
		${'select_'.$rt['private']} = 'selected';
		${'select2_'.$rt['memopen']} = 'selected';

		require_once PrintEot('thread_galbum');
		footer();

	} else {

		S::gp(array('aname','aintro'),'P');
		S::gp(array('private','memopen'),'P',2);
		!$aname && Showmsg('colony_aname_empty');
		strlen($aname) > 24 && Showmsg('colony_aname_length');
		$aintro && strlen($aintro) > 255 && Showmsg('colony_aintro_length');

		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($aname)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($aintro)) !== false) {
			Showmsg('post_wordsfb');
		}

		$db->update("UPDATE pw_cnalbum SET " . S::sqlSingle(array('aname' => $aname, 'aintro' => $aintro, 'private' => $private ? 1 : 0,'memopen'=>$memopen)) . ' WHERE aid=' . S::sqlEscape($aid));

		refreshto("{$basename}&a=edit&aid=$aid",'operate_success');
	}
} elseif ($a == 'create') {

	!$ifadmin && Showmsg('undefined_action');
	banUser();
	S::gp(array('ajax','job'));
	if ($groupRight['albumnum'] > 0 && $groupRight['albumnum'] <= $colony['albumnum']) {
		Showmsg('colony_album_num2');
	}
	if (empty($_POST['step'])) {
		$tmpUrlAdd .= '&a=create';
		$maxphotonum = $groupRight['maxphotonum'];
		$rt = array();
		$extra_url = '';
		$select_0 = 'selected';
		if ($ajax == 1) {
			$a = 'createajax';
			require_once PrintEot('m_ajax');ajax_footer();
		} else{
			require_once PrintEot('thread_galbum');
			footer();
		}
	} else {

		require_once(R_P.'require/postfunc.php');
		S::gp(array('aname','aintro'),'P');
		S::gp(array('private','memopen'),'P',2);
		!$aname && Showmsg('colony_aname_empty');
		strlen($aname) > 24 && Showmsg('colony_aname_length');
		$aintro && strlen($aintro) > 255 && Showmsg('colony_aintro_length');

		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($aname)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($aintro)) !== false) {
			Showmsg('post_wordsfb');
		}

		require_once(R_P.'apps/groups/lib/group.class.php');
		$colony = getGroupByCyid($cyid);

		require_once(R_P.'require/credit.php');
		$o_groups_creditset['Createalbum'] = array_diff($o_groups_creditset['Createalbum'],array(0));
		if (!empty($o_groups_creditset['Createalbum'])) {
			foreach ($o_groups_creditset['Createalbum'] as $key => $value) {
				if ($value > 0) {
					$moneyname = $credit->cType[$key];
					$moneyvalue = $value;
					if ($value > $credit->get($winduid,$key)) {
						Showmsg('colony_moneylimit2');
					}
				}
			}
			//积分变动
			$creditset = getCreditset($o_groups_creditset['Createalbum'],false);
			$credit->sets($winduid,$creditset,true);
			updateMemberid($winduid);
		}

		if ($creditlog = $o_groups_creditlog) {
			addLog($creditlog['Createalbum'],$windid,$winduid,'groups_Createalbum');
		}
		$db->update("INSERT INTO pw_cnalbum SET " . S::sqlSingle(array(
				'aname'		=> $aname,			'aintro'	=> $aintro,
				'atype'		=> 1,				'private'	=> $private ? 1 : 0,
				'ownerid'	=> $cyid,			'owner'		=> $colony['cname'],
				'lasttime'	=> $timestamp,		'crtime'	=> $timestamp,
				'memopen'   => $memopen
		)));
		//* $db->update("UPDATE pw_colonys SET albumnum=albumnum+1 WHERE id=" . S::sqlEscape($cyid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET albumnum=albumnum+1 WHERE id=:id", array('pw_colonys', $cyid)));
		$aid = $db->insert_id();

		$colony['albumnum']++;
		updateGroupLevel($colony['id'], $colony);
		if ($ajax == 1) {
			echo "success\t$aid\t$aname\t$job";ajax_footer();
		} else {
			refreshto("thread.php?cyid=$cyid&showtype=galbum&a=upload&job=flash&aid=$aid",'operate_success');
		}
	}
} elseif ($a == 'getallowflash') {

	define('AJAX', 1);
	define('F_M',true);

	ob_end_clean();
	ObStart();
	S::gp(array('aid'));

	$aid = (int)$aid;
	if ($aid) {
		$photonums = $db->get_value("SELECT photonum FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($aid));
		$groupRight['maxphotonum'] && $photonums >= $groupRight['maxphotonum'] && Showmsg('colony_photofull');
		$allowmutinum = $groupRight['maxphotonum'] - $photonums;
	}
	if (empty($groupRight['maxphotonum'])) {
		echo "ok\tnotlimit";
	} else {
		echo "ok\t$allowmutinum";
	}
	ajax_footer();

} else {

	Showmsg('undefined_action');
}
?>
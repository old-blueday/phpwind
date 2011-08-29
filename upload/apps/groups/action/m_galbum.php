<?php
!defined('A_P') && exit('Forbidden');

//!$winduid && Showmsg('not_login');
!$db_groups_open && Showmsg('groups_close');
SCR == 'mode' && ObHeader('apps.php?' . $pwServer['QUERY_STRING']);

S::gp(array('a'));
S::gp(array('cyid'), null, 2);
$pwModeImg = "$imgpath/apps";
require_once(R_P . 'apps/groups/lib/colony.class.php');
$newColony = new PwColony($cyid);

if (!$colony =& $newColony->getInfo()) {
	Showmsg('data_error');
}
//当群组视图关闭状态下
if ($colony['viewtype'] == 1 && !in_array($a, array('editphoto', 'delphoto', 'delalbum', 'getallowflash', 'next', 'pre'))) {
	$newColony->jumpToBBS($q, $a, $cyid);
} elseif($colony['viewtype'] == '0') {
	$cnclass['fid'] = $db->get_value("SELECT fid FROM pw_cnclass WHERE fid=" . S::sqlEscape($colony['classid']). " AND ifopen=1");
}
$colony['albumnum'] = abs($colony['albumnum']);
require_once(R_P . 'require/bbscode.php');
$newColony->initBanner();
$groupRight =& $newColony->getRight();
$colony_name = $newColony->getNameStyle();
$descrip = convert($colony['descrip'], array());
$a_key = 'galbum';
$isGM = S::inArray($windid,$manager);
$ifadmin = $newColony->getIfadmin();
$favortitle = str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"), $colony['cname']);
$tmpActionUrl = 'thread.php?cyid=' . $cyid . '&showtype=galbum';

if (!$groupRight['modeset']['galbum']['ifopen']) {
	Showmsg('galbum_closed');
}

//SEO
require_once(R_P . 'apps/groups/lib/colonyseo.class.php');
$colonySeo = new Pw_ColonySEO($cyid);
$webPageTitle = $colonySeo->getPageTitle($groupRight['modeset']['galbum']['title'],$colony['cname']);
$metaDescription = $colonySeo->getPageMetadescrip($colony['descrip']);
$metaKeywords = $colonySeo->getPageMetakeyword($colony['cname']);

//是否有可上传的相册
$uploadAvaliable = false;
if (empty($a)) {

	$photonum = $db->get_value("SELECT SUM(photonum) AS photonum FROM pw_cnalbum WHERE ownerid=".S::sqlEscape($cyid));
	$photonum || $photonum = 0;
	S::gp(array('page'), null, 2);
	$db_perpage = 10;
	list($pages,$limit) = pwLimitPages($colony['albumnum'],$page,"apps.php?q=galbum&cyid=$cyid&");
	$album = array();
	$query = $db->query("SELECT aid,aname,photonum,lastphoto,private,lasttime,crtime,memopen FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . " ORDER BY aid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['sub_aname'] = substrs($rt['aname'],16);
		$rt['lasttime'] = get_date($rt['lasttime'],'Y-m-d');
		$rt['crtime'] = get_date($rt['crtime'],'Y-m-d');
		$rt['forbidden'] = ($rt['private'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1'));
		$rt['lastphoto'] = $rt['forbidden'] ? "$pwModeImg/n.gif" : getphotourl($rt['lastphoto']);
		$album[] = $rt;
		$rt['memopen'] == 1 && $colony['ifFullMember'] && $uploadAvaliable = true;
	}
	$ifadmin && $uploadAvaliable == false && $uploadAvaliable = true;
	list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

} elseif ($a == "photolist") {

	if (!$colony['albumopen'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	$photonum = $db->get_value("SELECT count(*) AS count FROM pw_cnphoto t LEFT JOIN pw_cnalbum m ON t.aid=m.aid WHERE atype='1' AND m.ownerid=".S::sqlEscape($cyid));
	$photonum || $photonum = 0;
	S::gp(array('page'), null, 2);
	$db_perpage = 21;
	list($pages,$limit) = pwLimitPages($photonum,$page,"{$basename}&a=$a&cyid=$cyid&");
	$photolist= array();
	$query = $db->query("SELECT t.*,m.memopen FROM pw_cnphoto t LEFT JOIN pw_cnalbum m ON t.aid=m.aid WHERE atype='1' AND m.ownerid=".S::sqlEscape($cyid) . " ORDER BY t.pid DESC $limit");
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
	$memoOpenedAlbums = $db->get_value("SELECT COUNT(*) FROM pw_cnalbum WHERE atype='1' AND memopen='1' AND ownerid=" . S::sqlEscape($cyid) . " ORDER BY aid DESC $limit");
	($ifadmin || $memoOpenedAlbums > 0 && $colony['ifFullMember']) && $uploadAvaliable = true;
	
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_galbum",true);

} elseif ($a == 'album') {

	S::gp(array('aid','page'), null, 2);
	$tmpActionUrl .= '&a=album&aid=' . $aid;

	$cnpho = array();
	$album = $db->get_one("SELECT aname,aintro,ownerid,photonum,private,photonum,memopen FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($aid));
	if (empty($album)) {
		Showmsg('data_error');
	}
	if ($album['private'] && !$ifadmin && !$colony['ifFullMember']) {
		Showmsg('colony_cnmenber');
	}

	$webPageTitle = $colonySeo->getPageTitle($album['aname'],$colony['cname']);
	$metaDescription = $colonySeo->getPageMetadescrip($album['aintro']);
	$metaKeywords = $colonySeo->getPageMetakeyword($album['aname'],$colony['cname']);

	$db_perpage = 21;
	list($pages,$limit) = pwLimitPages($album['photonum'],$page,"apps.php?q=galbum&a=album&cyid=$cyid&aid=$aid&");

	$query = $db->query("SELECT c.pid,c.path,c.ifthumb,c.pintro,c.uptime,c.uploader,m.groupid FROM pw_cnphoto c LEFT JOIN pw_members m ON c.uploader=m.username WHERE c.aid=" . S::sqlEscape($aid) . " ORDER BY c.pid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['path'] = $pwModeImg.'/banuser.gif';
		}
		$rt['uptime'] = get_date($rt['uptime']);
		$rt['sub_pintro'] = substrs($rt['pintro'],25);
		$cnpho[] = $rt;
	}
	list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

} elseif ($a == 'view') {

	require_once(R_P . 'require/showimg.php');
	require_once(R_P.'require/bbscode.php');
	S::gp(array('pid','page'), null, 2);
	$tmpActionUrl .= '&a=view&pid=' . $pid;

	$nearphoto = array();
	$register = array('db_shield'=>$db_shield,"groupid"=>$groupid,"pwModeImg"=>$pwModeImg);
	L::loadClass('showpicture', 'colony', false);
	$sp = new PW_ShowPicture($register);
	list($photo,$nearphoto,$prePid,$nextPid) = $sp->getGroupsPictures($pid,$aid);

	empty($photo) && Showmsg('data_error');
	if ($photo['private'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}

	$webPageTitle = $colonySeo->getPageTitle($photo['aname'],$colony['cname']);
	$metaDescription = $colonySeo->getPageMetadescrip($photo['aintro']);
	$metaKeywords = $colonySeo->getPageMetakeyword($photo['aname'],$colony['cname']);

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
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('groupphoto',$pid,$page,"apps.php?q=galbum&a=view&cyid=$cyid&pid=$pid&");
	$comment_type = 'groupphoto';
	$comment_typeid = $pid;

	list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

} elseif ($a == 'editphoto') {

	define('AJAX','1');
	banUser();
	S::gp(array('pid'), null, 2);

	$photo = $db->get_one("SELECT p.aid,p.pintro,p.uploader,a.ownerid,p.path,a.lastphoto FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid WHERE pid=" . S::sqlEscape($pid));
	if (empty($photo)) {
		Showmsg('data_error');
	}
	if (!$ifadmin && $photo['uploader'] != $windid) {
		Showmsg('colony_cnmenber');
	}
	if (empty($_POST['step'])) {

		$options = '';
		$query = $db->query("SELECT aid,aname FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . ' ORDER BY aid DESC');
		while ($rt = $db->fetch_array($query)) {
			$options .= "<option value=\"$rt[aid]\"" . (($rt['aid'] == $photo['aid']) ? ' selected' : '') . ">$rt[aname]</option>";
		}
		require_once PrintEot('m_ajax');ajax_footer();
		//list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

	} else {

		S::gp(array('pintro'),'P');
		S::gp(array('aid'), null, 2);
		!$aid && Showmsg('colony_albumclass');

		require_once(R_P.'require/postfunc.php');
		require_once(R_P.'require/bbscode.php');

		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($pintro)) !== false) {
			Showmsg('post_wordsfb');
		}
		$pwSQL = array('pintro' => $pintro);

		$ischage = false;

		if ($aid != $photo['aid'] && $cyid == $db->get_value("SELECT ownerid FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid)." AND atype='1'")) {
			$pwSQL['aid'] = $aid;
			$ischage = true;
		}
		$db->update("UPDATE pw_cnphoto SET " . S::sqlSingle($pwSQL) . ' WHERE pid=' . S::sqlEscape($pid));

		if ($ischage) {
			$phnum = array();
			$query = $db->query("SELECT aid,COUNT(*) AS sum FROM pw_cnphoto WHERE aid IN(" . S::sqlImplode(array($aid,$photo['aid'])) . ') GROUP BY aid');
			while ($rt = $db->fetch_array($query)) {
				$phnum[$rt['aid']] = $rt['sum'];
			}
			$db->update("UPDATE pw_cnalbum SET " . S::sqlSingle(array('photonum' => $phnum[$aid] ? $phnum[$aid] : 0, 'lastpid' => implode(',',getLastPid($aid)))) . ',lastphoto= '. S::sqlEscape($photo['path']) .' WHERE aid=' . S::sqlEscape($aid));
			if ($photo['lastphoto'] == $photo['path']){
				$updatelastphoto = $db->get_value("SELECT path FROM pw_cnphoto WHERE aid=" . S::sqlEscape($photo['aid']) ." ORDER BY uptime DESC LIMIT 1");
			} else {
				$updatelastphoto = $lastphoto_array[$photo['aid']];
			}
			$db->update("UPDATE pw_cnalbum SET " . S::sqlSingle(array('photonum' => $phnum[$photo['aid']] ? $phnum[$photo['aid']] : 0, 'lastpid' => implode(',',getLastPid($photo['aid'])))) . ',lastphoto= '. S::sqlEscape($updatelastphoto) .' WHERE aid=' . S::sqlEscape($photo['aid']));
		}
		Showmsg('operate_success_reload');
	}
} elseif ($a == 'upload') {

	if (!$ifadmin && !$colony['ifFullMember']) {
		Showmsg('colony_cnmenber');
	}
	banUser();
	S::gp(array('aid', 'job'));

	$tmpActionUrl .= '&a=upload' . ($job ? '&job=' . $job : '') . '&aid=' . $aid;

	if (empty($_POST['step'])) {

		$extra_url = $options = '';
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid));
		if (empty($count) && $ifadmin) {
			$db->update("INSERT INTO pw_cnalbum SET " . S::sqlSingle(array(
				'aname'		=> '默认相册',		'aintro'	=> '',
				'atype'		=> 1,				'private'	=> 0,
				'ownerid'	=> $cyid,			'owner'		=> $colony['cname'],
				'lasttime'	=> $timestamp,		'crtime'	=> $timestamp,
				'memopen'   => 1
			)));
			//* $db->update("UPDATE pw_colonys SET albumnum=albumnum+1 WHERE id=" . S::sqlEscape($cyid));
			pwQuery::update('pw_colonys', 'id=:id', array($cyid), null, array(PW_EXPR=>array('albumnum=albumnum+1')));
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
		!empty($options) && $uploadAvailable = true;
		//(empty($options)) && $options="<option value=\"38\">默认分类</option>";
		$aid && $extra_url = '&aid=' . $aid;
		list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

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
		$rt = $db->get_one("SELECT aname,photonum,ownerid,lastphoto,memopen,private FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($aid));
		if (empty($rt)) {
			Showmsg('undefined_action');
		} elseif ($cyid <> $rt['ownerid']) {
			Showmsg('colony_phototype');
		} elseif ($rt['memopen']==0 && !$ifadmin) {
			Showmsg('colony_album_memopen');
		}
		$groupRight['maxphotonum'] && $rt['photonum'] >= $groupRight['maxphotonum'] && Showmsg('colony_photofull');
		L::loadClass('photoupload', 'upload', false);
		if ($groupRight['maxphotonum'] && $rt['photonum'] + PwUpload::getUploadNum() > $groupRight['maxphotonum']) {
			$uploadlimit = $groupRight['maxphotonum'] - $rt['photonum'];
			$uploadlimit = $uploadlimit > 0 ? $uploadlimit : 0;
			Showmsg('uploadphoto_leave');
		}
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
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum+:photonum WHERE id=:id", array('pw_colonys', $photoNum, $cyid)));

		$colony['photonum']+=$photoNum;
		updateGroupLevel($colony['id'], $colony);
		if(!$rt['private']){
			$weiboPhotos = array();
			$tmpid = $pid;
			foreach ($photos as $value) {
				$value['pid'] = $tmpid;
				$tmpid++;
				$weiboPhotos[] = $value;
			}
			$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
			$objId = count($photos) > 1 ? 0 : $pid;
			$weiboExtra = array(
							'cyid' => $cyid,
							'aid'  => $aid,
							'photos'=> $weiboPhotos,
							'cname'	=> $colony['cname'],
							'aname' => $rt['aname'],
						);
			$weiboService->send($winduid,'','group_photos',$objId,$weiboExtra);
		}

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
		refreshto("{$basename}a=view&cyid=$cyid&pid=$pid",'operate_success');
	}
} elseif ($a == 'selalbum') {

	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	S::gp(array('page', 'selaid','aid'), null, 2);
	$tmpActionUrl .= '&aid='.$aid.'&a=selalbum';
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
	list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

} elseif ($a == 'selphoto') {

	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	S::gp(array('aid', 'selaid', 'page'));
	$tmpActionUrl .= '&aid='.$aid.'&a=selphoto&selaid='.$selaid;
	$album = $db->get_one("SELECT aname,ownerid,photonum FROM pw_cnalbum WHERE atype='0' AND aid=" . S::sqlEscape($aid));
	if (empty($album) || $album['ownerid'] != $winduid) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {

		$db_perpage = 10;
		list($pages,$limit) = pwLimitPages($album['photonum'],$page,"apps.php?q=galbum&a=selphoto&cyid=$cyid&aid=$aid&selaid=$selaid&");

		$options = '';
		$query = $db->query("SELECT aid,aname,memopen FROM pw_cnalbum WHERE atype='1' AND ownerid=" . S::sqlEscape($cyid) . ' ORDER BY aid DESC');
		while ($rt = $db->fetch_array($query)) {

			if ($ifadmin || $rt['memopen']==1) {
				$memopen = 1;
			} else {
				$memopen = 0;
			}
			if($memopen ==1) {
				$options .= "<option value=\"$rt[aid]\"" . ($rt['aid'] == $selaid ? ' selected' : '') . ">$rt[aname]</option>";
			}
		}

		$cnpho = array();
		$query = $db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid) . " ORDER BY pid $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
			$cnpho[] = $rt;
		}
		list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

	} else {

		S::gp(array('selid'));
		if (!$selid || !is_array($selid)) {
			Showmsg('colony_select_photo');
		}
		if (empty($selaid)) {
			Showmsg('colony_albumclass');
		}

		$selalbum = $db->get_one("SELECT aname,photonum,ownerid,lastphoto,memopen,private FROM pw_cnalbum WHERE atype='1' AND aid=" . S::sqlEscape($selaid));
		if (empty($selalbum)) {
			Showmsg('undefined_action');
		} elseif ($cyid <> $selalbum['ownerid']) {
			Showmsg('colony_phototype');
		} elseif ($selalbum['memopen']==0&&!$ifadmin){
			Showmsg('colony_album_memopen');
		}

		$groupRight['maxphotonum'] && $selalbum['photonum'] >= $groupRight['maxphotonum'] && Showmsg('colony_photofull');

		if ($groupRight['maxphotonum'] && $selalbum['photonum'] + count($selid) > $groupRight['maxphotonum']) {
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
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum+" . S::sqlEscape($photoNum) . " WHERE id=:id", array('pw_colonys', $cyid)));
			countPosts("+$photoNum");
			if(!$selalbum['private']){
				$weiboPhotos = array();
				$tmpid = $pid;
				foreach ($photos as $value) {
					$value['pid'] = $tmpid;
					$tmpid++;
					$weiboPhotos[] = $value;
				}
				$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
				$objId = count($photos) > 1 ? 0 : $pid;
				$weiboExtra = array(
								'cyid' => $cyid,
								'aid'  => $selaid,
								'photos'=> $weiboPhotos,
								'cname'	=> $colony['cname'],
								'aname' => $selalbum['aname'],

							);
				$weiboService->send($winduid,'','group_photos',$objId,$weiboExtra);
			}
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
			refreshto("{$basename}a=view&cyid=$cyid&pid=$pid",'operate_success');

		} else {
			refreshto("{$basename}a=album&cyid=$cyid&aid=$selaid",'operate_success');
		}
	}

} elseif ($a == 'delphoto') {

	define('AJAX','1');
	S::gp(array('pid'), null, 2);
	$photo = $db->get_one("SELECT cp.uploader,cp.path,ca.aid,ca.lastphoto,ca.lastpid,m.uid FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid LEFT JOIN pw_members m ON cp.uploader=m.username WHERE cp.pid=" . S::sqlEscape($pid) . " AND ca.atype='1' AND ca.ownerid=" . S::sqlEscape($cyid));
	if (empty($photo)) {
		Showmsg('data_error');
	}
	if (!$ifadmin && $photo['uploader'] != $windid) {
		Showmsg('colony_cnmenber');
	}

	$db->update("DELETE FROM pw_cnphoto WHERE pid=" . S::sqlEscape($pid));

	$pwSQL = array();
	if ($photo['path'] == $photo['lastphoto']) {
		$pwSQL['lastphoto'] = $db->get_value("SELECT path FROM pw_cnphoto WHERE aid=" . S::sqlEscape($photo['aid']) . " ORDER BY pid DESC LIMIT 1");
	}
	if (strpos(",$photo[lastpid],",",$pid,") !== false) {
		$pwSQL['lastpid'] = implode(',',getLastPid($photo['aid']));
	}
	$upsql = $pwSQL ? ',' . S::sqlSingle($pwSQL) : '';
	$db->update("UPDATE pw_cnalbum SET photonum=photonum-1{$upsql} WHERE aid=" . S::sqlEscape($photo['aid']));

	//* $db->update("UPDATE pw_colonys SET photonum=photonum-1 WHERE id=" . S::sqlEscape($cyid));
	$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum-1 WHERE id=:id", array('pw_colonys', $cyid)));

	$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
	$weibo = $weiboService->getWeibosByObjectIdsAndType($pid,'group_photos');
	if($weibo){
		$weiboService->deleteWeibos($weibo['mid']);
	}

	$colony['photonum']--;
	updateGroupLevel($colony['id'], $colony);

	pwDelatt($photo['path'], $db_ifftp);
	$lastpos = strrpos($photo['path'],'/') + 1;
	pwDelatt(substr($photo['path'], 0, $lastpos) . 's_' . substr($photo['path'], $lastpos), $db_ifftp);
	pwFtpClose($ftp);
	$affected_rows = delAppAction('photo',$pid)+1;
	countPosts("-$affected_rows");

	//积分变动
	require_once(R_P.'require/credit.php');
	if ($o_groups_creditset) {
		$creditset = getCreditset($o_groups_creditset['Deletephoto'],false);
		$creditset = array_diff($creditset,array(0));
		if (!empty($creditset)) {
			require_once(R_P.'require/postfunc.php');
			$credit->sets($photo['uid'],$creditset,true);
			updateMemberid($photo['uid'],false);
		}
	}
	if ($o_groups_creditlog) {
		$creditlog = $o_groups_creditlog;
		addLog($creditlog['Deletephoto'],$photo['uploader'],$photo['uid'],'groups_Deletephoto');
	}

	echo 'ok';ajax_footer();

} elseif ($a == 'delalbum') {

	define('AJAX', 1);
	define('F_M',true);
	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}

	S::gp(array('aid'), null, 2);
	S::gp(array('from'));

	!$ifadmin && Showmsg('undefined_action');
	$album = $db->get_one("SELECT * FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid) . " AND atype='1'");

	if (empty($album) || $album['ownerid'] != $cyid) {
		Showmsg('data_error');
	}

	if (empty($_POST['step'])) {
		require_once PrintEot('m_ajax');
		ajax_footer();

	} else {

		$query = $db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid));
		if (($num = $db->num_rows($query)) > 0) {
			$affected_rows = 0;
			while ($rt = $db->fetch_array($query)) {
				pwDelatt($rt['path'], $db_ifftp);
				if ($rt['ifthumb']) {
					$lastpos = strrpos($rt['path'],'/') + 1;
					pwDelatt(substr($rt['path'], 0, $lastpos) . 's_' . substr($rt['path'], $lastpos), $db_ifftp);
				}
				$affected_rows += delAppAction('photo',$rt['pid'])+1;
			}
			pwFtpClose($ftp);
			countPosts("-$affected_rows");
		}
		$db->update("DELETE FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid));
		$db->update("DELETE FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid));
		//* $db->update("UPDATE pw_colonys SET albumnum=albumnum-1,photonum=photonum-" . S::sqlEscape($album['photonum']) . " WHERE id=" . S::sqlEscape($cyid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET albumnum=albumnum-1,photonum=photonum-:photonum WHERE id=:id", array('pw_colonys', $album['photonum'], $cyid)));

		$colony['albumnum']--;
		$colony['photonum'] -= $album['photonum'];
		updateGroupLevel($colony['id'], $colony);

		$toUrl = ($from == 'bbs') ? "thread.php?cyid=$cyid&showtype=galbum" : "apps.php?q=galbum&cyid=$cyid";
		echo getLangInfo('msg','operate_success') . "\tjump\t$toUrl";
		ajax_footer();
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
		$tmpActionUrl .= '&a=edit&aid=' . $aid;
		$extra_url = '&a=album&aid=' . $aid;
		${'select_'.$rt['private']} = 'selected';
		${'select2_'.$rt['memopen']} = 'selected';

		list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);

	} else {

		S::gp(array('aname','aintro'),'P');
		S::gp(array('private','memopen'),'P',2);

		PostCheck(0,$o_photos_gdcheck,$o_photos_qcheck && $db_question);
		strlen($aname) > 24 && Showmsg('colony_aname_length');
		$aintro && strlen($aintro) > 255 && Showmsg('colony_aintro_length');
		!$aname && Showmsg('colony_aname_empty');
		//$checkGd && !GdConfirm($_POST['gdcode']) && Showmsg("你的验证码不正确或过期");
		//Qcheck($_POST['qanswer'], $_POST['qkey']);

		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($aname)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($aintro)) !== false) {
			Showmsg('post_wordsfb');
		}

		$db->update("UPDATE pw_cnalbum SET " . S::sqlSingle(array('aname' => $aname, 'aintro' => $aintro, 'private' => $private ? 1 : 0,'memopen'=>$memopen)) . ' WHERE aid=' . S::sqlEscape($aid));

		refreshto("{$basename}cyid=$cyid&a=edit&aid=$aid",'operate_success');
	}
} elseif ($a == 'create') {

	!$ifadmin && Showmsg('undefined_action');
	banUser();
	S::gp(array('ajax','job'));
	if ($groupRight['albumnum'] > 0 && $groupRight['albumnum'] <= $colony['albumnum']) {
		Showmsg('colony_album_num2');
	}
	$tmpActionUrl .= '&a=create';
	if (empty($_POST['step'])) {

		$maxphotonum = $groupRight['maxphotonum'];
		$rt = array();
		$extra_url = '';
		$select_0 = 'selected';
		if ($ajax == 1) {
			$a = 'createajax';
			require_once PrintEot('m_ajax');ajax_footer();
		}else{
			list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_galbum",true);
		}


	} else {
		PostCheck(0,$o_photos_gdcheck,$o_photos_qcheck && $db_question);
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
		!is_array($o_groups_creditset['Createalbum']) && $o_groups_creditset['Createalbum'] = array();
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
		$aid = $db->insert_id();
		//* $db->update("UPDATE pw_colonys SET albumnum=albumnum+1 WHERE id=" . S::sqlEscape($cyid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET albumnum=albumnum+1 WHERE id=:id", array('pw_colonys', $cyid)));

		$colony['albumnum']++;
		updateGroupLevel($colony['id'], $colony);
		if ($ajax == 1) {
			echo "success\t$aid\t$aname\t$job";ajax_footer();
		} else {
			refreshto("apps.php?q=galbum&a=upload&cyid=$cyid&job=flash&aid=$aid",'operate_success');
		}
	}
} elseif ($a == 'getallowflash') {

	define('AJAX', 1);
	define('F_M',true);
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
} elseif ($a == 'next') {
	define('AJAX',1);
	S::gp(array('pid','aid'), null, 2);
	if ($aid) {
		$next_photo = $db->get_one("SELECT c.pid,c.path,c.ifthumb,m.groupid FROM pw_cnphoto c LEFT JOIN pw_members m ON c.uploader=m.username WHERE c.pid>".S::sqlEscape($pid)." AND  c.aid=".S::sqlEscape($aid)." ORDER BY c.pid");
		if ($next_photo) {
			$next_photo['path'] = getphotourl($next_photo['path'],$next_photo['ifthumb']);
			if ($next_photo['groupid'] == 6 && $db_shield && $groupid != 3) {
				$next_photo['path'] = $pwModeImg.'/banuser.gif';
			}
			unset($next_photo['ifthumb']);
			$pid = pwJsonEncode($next_photo);
			echo "ok\t$pid";
		} else {
			echo "end";
		}
	} else {
		$pid = $db->get_value("SELECT MIN(b.pid) AS pid FROM pw_cnphoto a LEFT JOIN pw_cnphoto b ON a.aid=b.aid AND a.pid<b.pid WHERE a.pid=" . S::sqlEscape($pid));
		echo "ok\t$pid";
	}

	ajax_footer();
} elseif ($a == 'pre') {
	define('AJAX',1);
	S::gp(array('pid','aid'), null, 2);
	if ($aid) {
		$next_photo = $db->get_one("SELECT c.pid,c.path,c.ifthumb,m.groupid FROM pw_cnphoto c LEFT JOIN pw_members m ON c.uploader=m.username WHERE c.pid<".S::sqlEscape($pid)." AND  c.aid=".S::sqlEscape($aid)." ORDER BY c.pid DESC");
		if ($next_photo) {
			$next_photo['path'] = getphotourl($next_photo['path'],$next_photo['ifthumb']);
			if ($next_photo['groupid'] == 6 && $db_shield && $groupid != 3) {
				$next_photo['path'] = $pwModeImg.'/banuser.gif';
			}
			unset($next_photo['ifthumb']);
			$pid = pwJsonEncode($next_photo);
			echo "ok\t$pid";
		} else {
			echo "begin";
		}
	} else {
		$pid = $db->get_value("SELECT MAX(b.pid) AS pid FROM pw_cnphoto a LEFT JOIN pw_cnphoto b ON a.aid=b.aid AND a.pid>b.pid WHERE a.pid=" . S::sqlEscape($pid));
		echo "ok\t$pid";
	}
	ajax_footer();
}

require_once PrintEot('m_galbum');
pwOutPut();
?>
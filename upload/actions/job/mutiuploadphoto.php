<?php
!defined('P_W') && exit('Forbidden');
//* @include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
if (empty($_POST['step'])) {
	$filetype = '';
	$db_uploadfiletype = array();
	$db_uploadfiletype['gif'] = $db_uploadfiletype['jpg'] = $db_uploadfiletype['jpeg'] = $db_uploadfiletype['bmp'] = $db_uploadfiletype['png'] = $o_maxfilesize;
	
	foreach ($db_uploadfiletype as $key => $value) {
		$filetype .= ($filetype ? ',' : '') . $key . ':' . $value;
	}
	
	$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	$swfhash = GetVerify($winduid);
	
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><swf><filetype>{$filetype}</filetype><uid>{$winduid}</uid><windid>{$windid}</windid><step>2</step><verify>{$swfhash}</verify></swf>";

} else {
	//验证码
	//require_once(R_P.'require/postfunc.php');
	$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	require_once (R_P. 'u/require/core.php');
	S::gp(array(
		'uid',
		'verify',
		'desc',
	), 'P');
	S::gp(array(
		'aid',
		'filenames',
		'photoid'
	), 'G');
	$swfhash = GetVerify($uid);
	
	$pintro[0] = pwConvert($desc, $db_charset, 'utf-8');

	//$windid = pwConvert($windid,$db_charset,'utf-8');
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$winduid = $uid;
	$windid = $userService->getUserNameByUserId($uid);
	$filenames = pwConvert($filenames, $db_charset, 'utf-8');
	$filenames = addslashes($filenames);
	
	checkVerify('swfhash');
	
	$rt = $db->get_one("SELECT aname,photonum,ownerid,private,lastphoto,atype FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid));
	
		
	if (empty($rt)) {
		Showmsg('undefined_action');
	}
	if ($rt['atype'] == 1) {
		$colony = $db->get_one("SELECT c.*,cm.id AS ifcyer FROM pw_colonys c LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid AND cm.uid=" . S::sqlEscape($uid) . " WHERE c.id=" . S::sqlEscape($rt['ownerid']));
		$level = $colony['speciallevel'] ? $colony['speciallevel'] : $colony['commonlevel'];
		$o_maxphotonum = $db->get_value("SELECT maxphotonum FROM pw_cnlevel WHERE id=" . S::sqlEscape($level));
	} else {
		$uid != $rt['ownerid'] && Showmsg('colony_phototype');
	}
	$o_maxphotonum && $rt['photonum'] >= $o_maxphotonum && Showmsg('colony_photofull');
	
	foreach ($_FILES as $key => $value) {
		$_FILES[$key]['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
	}
	L::loadClass('photoupload', 'upload', false);
	$img = new PhotoUpload($aid,$rt['atype']);
	PwUpload::upload($img);
	pwFtpClose($ftp);
	
	if (!$photos = $img->getAttachs()) {
		Showmsg('colony_uploadnull');
	}
	$photoNum = count($photos);
	$pid = $img->getNewID();
	$photos[0]['pid'] = $pid;
	$lastpid = getLastPid($aid, 4);
	array_unshift($lastpid, $pid);
	
	if ($rt['atype'] == 1) {
		$cyid = $rt['ownerid'];
		//* $db->update("UPDATE pw_colonys SET photonum=photonum+" . S::sqlEscape($photoNum, false) . ' WHERE id=' . S::sqlEscape($cyid));		
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum+:photonum WHERE id=:id", array('pw_colonys', $photoNum, $cyid)));
		$colony['photonum']+= $photoNum;
		updateGroupLevel($colony['id'], $colony);
		if(!$rt['private']){
			sendToWeibo($uid, 'group_photos', $pid, array(
				'cyid'=>$colony['id'],
				'aid' => $aid,
				'aname' => $rt['aname'],
				'cname' => $colony['cname'],
				'photos' => $photos
			));
		}
	} elseif (!$rt['private']) {
		$ouserdataDB = L::loadDB('Ouserdata','sns');/* @var $ouserdataDB PW_OuserdataDB */ 
		$ouserData = $ouserdataDB->get($uid);
		$sendToWeiboPrivacy = $ouserData === false ? true : false;
		$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */ 
		$weiboPrivacy = $weiboService->checkUserSpacePrivacy($uid);
		if((!$weiboPrivacy['index'] && !$weiboPrivacy['photos']) || $sendToWeiboPrivacy){
			sendToWeibo($uid, 'photos', $pid, array(
				'aid' => $aid,
				'aname' => $rt['aname'],
				'photos' => $photos
			));
		}
		//会员资讯缓存
		$userCache = L::loadClass('Usercache', 'user');
		$userCache->delete($uid, 'cardphoto');
		$ouserDataService = L::loadClass('Ouserdata', 'sns');
		$myAppsData = $ouserDataService->get($winduid);
		if (!$myAppsData['photos_privacy']) {
			updateDatanalyse($pid,'picNew',$timestamp);
		}
	}
	
	if (!$rt['lastphoto']) {
		$db->update("UPDATE pw_cnalbum SET lastphoto=" . S::sqlEscape($img->getLastPhoto()) . " WHERE aid=" . S::sqlEscape($aid));
	}
	//$db->update("UPDATE pw_cnalbum SET photonum=photonum+" . S::sqlEscape($photoNum, false) . ",lasttime=" . S::sqlEscape($timestamp, false) . ',lastpid=' . S::sqlEscape(implode(',', $lastpid)) . (!$rt['lastphoto'] ? ',lastphoto=' . S::sqlEscape($img->getLastPhoto()) : '') . " WHERE aid=" . S::sqlEscape($aid));
	
	countPosts("+$photoNum");
	
	//积分变动
	require_once (R_P . 'require/credit.php');
	$o_photos_creditset = unserialize($o_photos_creditset);
	$creditset = getCreditset($o_photos_creditset['Uploadphoto'], true, $photoNum);
	$creditset = array_diff($creditset, array(
		0
	));
	if (!empty($creditset)) {
		$credit->sets($uid, $creditset, true);
		updateMemberid($uid,false);
	}
	if ($creditlog = unserialize($o_photos_creditlog)) {
		addLog($creditlog['Uploadphoto'], $windid, $uid, 'photos_Uploadphoto');
	}
	exit();
}

function sendToWeibo($uid, $type, $typeid, $extra) {
	global $db,$timestamp;
	$weiboService = L::loadClass('weibo','sns');
	if ($rt = $weiboService->getPrevWeiboByType($uid, $type)) {
		$rtExtra = unserialize($rt['extra']);
		if ($rtExtra['aid'] == $extra['aid'] && count($rtExtra['photos']) < 8) {
			$rtExtra['photos'] = array_merge($rtExtra['photos'], $extra['photos']);
			$weiboService->update(array('extra' => serialize($rtExtra), 'objectid' => 0), $rt['mid']);
			return true;
		}
	}
	$weiboService->send($uid, '分享照片', $type, $typeid, $extra);
}
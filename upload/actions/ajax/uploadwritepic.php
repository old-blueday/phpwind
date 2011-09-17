<?php
!defined('P_W') && exit('Forbidden');
require_once R_P . 'u/require/core.php';
//* require_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
$o_photos_creditset = unserialize($o_photos_creditset);
require_once(R_P.'require/postfunc.php');

banUser();

if (!$db_phopen) Showmsg('相册应用已关闭，新鲜事发图片功能不能使用');
if ($o_weibophoto != 1) Showmsg('新鲜事发图片功能已关闭');

$aid = $db->get_value("SELECT aid FROM pw_cnalbum WHERE atype='0' AND ownerid=" . S::sqlEscape($winduid) . " AND isdefault=1 " . S::sqlLimit(1));
if (!$aid) {
	/* //unlimit
	if ($o_albumnum2 > 0 && $o_albumnum2 <= $db->get_value("SELECT COUNT(*) AS count FROM pw_cnalbum WHERE atype='0' AND ownerid=" . S::sqlEscape($winduid))) {
		Showmsg('colony_album_num2');
	}
	
	require_once(R_P.'require/credit.php');
	$o_photos_creditset['Createalbum'] = @array_diff($o_photos_creditset['Createalbum'],array(0));
	if (!empty($o_photos_creditset['Createalbum'])) {
		foreach ($o_photos_creditset['Createalbum'] as $key => $value) {
			if ($value > 0) {
				$moneyname = $credit->cType[$key];
					if ($value > $credit->get($winduid,$key)) {
					Showmsg('colony_moneylimit2');
				}
			}
		}
		$creditset = getCreditset($o_photos_creditset['Createalbum'],false);
		$credit->sets($winduid,$creditset,true);
		updateMemberid($winduid);
	}
	
	if ($creditlog = unserialize($o_photos_creditlog)) {
		addLog($creditlog['Createalbum'],$windid,$winduid,'photos_Createalbum');
	}
	*/
	$db->update("INSERT INTO pw_cnalbum SET " . S::sqlSingle(array(
			'aname'		=> '默认相册',
			'aintro'	=> '默认相册',
			'atype'		=> 0,
			'private'	=> 0,
			'isdefault' => 1,
			'ownerid'	=> $winduid,
			'owner'		=> $windid,
			'lasttime'	=> $timestamp,
			'crtime'	=> $timestamp,
	)));
	$aid = $db->insert_id();
}
!$aid && Showmsg('找不到默认相册');


$rt = $db->get_one("SELECT aname,photonum,ownerid,private,lastphoto FROM pw_cnalbum WHERE atype='0' AND aid=" . S::sqlEscape($aid));
if (empty($rt)) {
	Showmsg('undefined_action');
} elseif ($winduid != $rt['ownerid']) {
	Showmsg('colony_phototype');
}
$uploadNum = 0;
foreach($_FILES as $k=>$v){
	(isset($v['name']) && $v['name'] != "") && $uploadNum++;
}

/* //unlimt
$o_maxphotonum && ($rt['photonum']+$uploadNum) > $o_maxphotonum && Showmsg('colony_photofull');
*/

L::loadClass('photoupload', 'upload', false);
$pintro = array($_FILES['writePic']['name']);
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
$photoInfo = $photos[0];

$db_bbsurl = $db_ifftp == 1 ? '' : $db_bbsurl.'/';
list($photo,$photoThumb) = array($db_bbsurl.getphotourl($photoInfo['path']),$db_bbsurl.getphotourl($photoInfo['path'], $photoInfo['ifthumb']));
$photoThumb = $photoThumb ? $photoThumb : $photo;
if (!$rt['private']) {
	//会员资讯缓存
	$userCache = L::loadClass('Usercache', 'user'); /* @var $userCache PW_Usercache */
	$userCache->delete($winduid, 'cardphoto');
	/*
	$usercache = L::loadDB('Usercache', 'user');
	$usercachedata = $usercache->get($winduid,'photos');
	$usercachedata = explode(',',$usercachedata['value']);
	is_array($usercachedata) || $usercachedata = array();
	if (count($usercachedata) >=4) array_pop($usercachedata);
	array_unshift($usercachedata,$pid);
	$usercachedata = implode(',',$usercachedata);
	$usercache->update($winduid,'photos',$pid,$usercachedata);
	*/
}
$db->update("UPDATE pw_cnalbum SET lasttime=" . S::sqlEscape($timestamp,false) . ',lastpid=' . S::sqlEscape(implode(',',$lastpid)) . (!$rt['lastphoto'] ? ',lastphoto=' . S::sqlEscape($img->getLastPhoto()) : '') . " WHERE aid=" . S::sqlEscape($aid));
countPosts("+$photoNum");

//积分变动
require_once(R_P.'require/credit.php');
$creditset = getCreditset($o_photos_creditset['Uploadphoto'],true,$photoNum);
$creditset = array_diff($creditset,array(0));
if (!empty($creditset)) {
	$credit->sets($winduid,$creditset,true);
	updateMemberid($winduid);
}
if ($creditlog = unserialize($o_photos_creditlog)) {
	addLog($creditlog['Uploadphoto'],$windid,$winduid,'photos_Uploadphoto');
}
updateUserAppNum($winduid,'photo','add',$photoNum);
echo "success\t" . $photoInfo['pintro'] . "\t" . $pid. "\t" .$photo. "\t". $photoThumb;
ajax_footer();
			

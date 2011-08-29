<?php
!defined('A_P') && exit('Forbidden');
$basename = 'apps.php?q='.$q.'&uid='.$uid.'&';

empty($space) && Showmsg('您访问的空间不存在!');
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
$whiteList = array(
	'own',
	'albumcheck',
	'editalbum',
	'album',
	'next',
	'pre',
	'view',
	'delphoto',
	'delalbum',
	'viewalbum',
	'getallowflash',
	'createajax'
);
if (!in_array($a,$whiteList)) {
	$a = 'own';
}
if ($a == 'own' && $indexRight) {
	list($count,$albumdb) = $photoService->getAlbumBrowseList();
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$pages = numofpage($count, $page,$pageCount, "{$basename}a=$a&uid=$uid&");

} elseif ($a == 'albumcheck') {

	S::gp(array('aid'), null, 2);
	S::gp(array('viewpwd'));
	$album = $photoService->getAlbumInfo($aid);
	if (empty($album)) {
			echo "data_error";
			ajax_footer();
	}
	if (!$viewpwd) {
		echo "empty";
		ajax_footer();
	}
	$viewpwd = md5($viewpwd);
	if ($album['albumpwd'] == $viewpwd) {
		Cookie('albumview_' . $album['aid'], PwdCode($viewpwd), time()+24*3600);
		echo "success";
	} else {
		echo "fail";
	}
	ajax_footer();
	
} elseif ($a == 'editalbum') {

	define('AJAX', 1);
	define('F_M',true);
	banUser();
	S::gp(array('aid'));
	empty($aid) && Showmsg('data_error');
	$albumInfo = $photoService->getAlbumInfo($aid);
	if (empty($albumInfo) || $albumInfo['atype'] <> 0 || ($albumInfo['ownerid'] <> $winduid && !$photoService->isPermission())) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		${'select_'.$albumInfo['private']} = 'selected';
		require_once PrintEot('m_ajax_photos');
		ajax_footer();

	} else {
		require_once(R_P.'require/postfunc.php');
		S::gp(array('aname','aintro','pwd','repwd'),'P');
		S::gp(array('private'),'P',2);
		!$aname && Showmsg('colony_aname_empty');
		if (strlen($aname)>24) Showmsg('colony_aname_toolang');
		if (strlen($aintro)>255) Showmsg('colony_aintro_toolang');
		if ($private == 3 && !$pwd && !$albumInfo['albumpwd']) {
			Showmsg('photo_password_add');
		}
		if ($pwd) {
			if (strlen($pwd) < 3 || strlen($pwd) > 15) {
				Showmsg('photo_password_minlimit');
			}
			$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%','?');
			if (str_replace($S_key,'',$pwd) != $pwd) {
				Showmsg('illegal_password');
			}
			if ($pwd != $repwd) {
				Showmsg('password_confirm');
			}
			$pwd = md5($pwd);
		}
		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($aname)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($aintro)) !== false) {
			Showmsg('content_wordsfb');
		}
		if ($private == 3 && !$pwd && $albumInfo['albumpwd']) {
			$pwd = $albumInfo['albumpwd'];
		}
		$data = array('aname' => $aname,
					  'aintro' => $aintro, 
					  'private' => $private, 
					  'albumpwd' => $pwd
				 );
		$photoService->updateAlbumInfo($aid,$data);
		refreshto("{$basename}a=own",'operate_success');
	}
} elseif ($a == 'album') {
	S::gp(array('aid'), null, 2);
	$cnpho = array();
	$result = $photoService->getPhotoListByAid($aid);
	if(!is_array($result)){
		$result == 'mode_o_photos_private_3' && refreshto($basename, 'mode_o_photos_private_3');
		Showmsg($result);
	}
	if($indexRight && !$photoRight ){
		Showmsg('该空间相册设置隐私，您没有权限查看!');
	}
	list($album,$cnpho) = $result;
	$count = $album['photonum'];
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$pages = numofpage($count, $page,$pageCount, "{$basename}a=$a&aid=$aid&uid=$uid&");
	
	$siteName = getSiteName('o');
	$uSeo = USeo::getInstance();
	$uSeo->set(
		$album['aname'] . ' - ' . $space['name'] . ' - ' . $siteName,
		'相册',
		$album['aname'] . ',' . $siteName
	);

	$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	$myOuserData = $ouserdataService->get($album['ownerid']);
	$weiboPriv = false;
	(!$myOuserData['index_privacy'] && !$myOuserData['photos_privacy'] && !$album['private']) && $weiboPriv = true;
} elseif ($a == 'view') {

	S::gp(array('pid'));
	$result = $photoService->viewPhoto($pid);
	if(!is_array($result)){
		$result == 'mode_o_photos_private_3' && refreshto($basename, 'mode_o_photos_private_3');
		Showmsg($result);
	}
    list($photo,$nearphoto,$prePid,$nextPid) = $result;
	$username = $photo['owner'];
	$aid = $photo['aid'];
	$album = $photoService->albumViewRight($aid);
	if(!is_array($album)){
		Showmsg($album);
	}
	$page = (int)S::getGP('page');
	$page < 1 && $page = 1;
	$url = $basename.'a=view&pid='.$pid.'&';
	require_once(R_P.'require/bbscode.php');
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('photo',$pid,$page,$url);
	$comment_type = 'photo';
	$comment_typeid = $pid;
	
	$siteName = getSiteName('o');
	$uSeo = USeo::getInstance();
	$uSeo->set(
		$photo['aname'] . ' - ' . $space['name'] . ' - ' . $siteName,
		'相册',
		$photo['aname'] . ',' . $siteName
	);

	$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	$myOuserData = $ouserdataService->get($photo['ownerid']);
	$weiboPriv = false;
	(!$myOuserData['index_privacy'] && !$myOuserData['photos_privacy'] && !$photo['private']) && $weiboPriv = true;
	
} elseif ($a == 'next') {
	define('AJAX',1);
	S::gp(array('pid'));
	$status = $photoService->getNextPhoto($pid);
	echo $status;
	ajax_footer();
} elseif ($a == 'pre') {
	define('AJAX',1);
	S::gp(array('pid'));
	$status = $photoService->getPrevPhoto($pid);
	echo $status;
	ajax_footer();
}elseif ($a == 'delphoto') {
	define('AJAX','1');
	S::gp(array('pid'));
	$photo = $photoService->delPhoto($pid);
	if(empty($photo)){
		Showmsg('data_error');
	}
	
	$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
	$weibo = $weiboService->getWeibosByObjectIdsAndType($pid,'photos');
	if($weibo){
		$weiboService->deleteWeibos($weibo['mid']);
	}
	
	$affected_rows = delAppAction('photo',$pid) + 1;
	countPosts("-$affected_rows");
	//积分变动
	require_once(R_P.'require/credit.php');
	$o_photos_creditset = unserialize($o_photos_creditset);
	$creditset = getCreditset($o_photos_creditset['Deletephoto'],false);
	$creditset = array_diff($creditset,array(0));
	if (!empty($creditset)) {
		require_once(R_P.'require/postfunc.php');
		$credit->sets($photo['uid'],$creditset,true);
		updateMemberid($photo['uid'],false);
	}
	if ($creditlog = unserialize($o_photos_creditlog)) {
		addLog($creditlog['Deletephoto'],$photo['uploader'],$photo['uid'],'photos_Deletephoto');
	}
	updateUserAppNum($photo['uid'],'photo','minus');
	echo 'ok'."\t".$photo['aid'];
	ajax_footer();

} elseif ($a == 'delalbum') {

	define('AJAX', 1);
	define('F_M',true);
	S::gp(array('aid'), null, 2);
	$album = $photoService->getAlbumInfo($aid);

	if (empty($album) || ($album['ownerid'] != $winduid && !$photoService->isDelRight())) {
		Showmsg('data_error');
	}

	if (empty($_POST['step'])) {

		require_once PrintEot('m_ajax_photos');
		ajax_footer();

	} else {
		$photoService->delAlbum($aid);
		updateUserAppNum($album['ownerid'],'photo','minus',$album['photonum']);
		echo getLangInfo('msg','operate_success') . "\tjump\t{$basename}";
		ajax_footer();
	}
} elseif ($a == 'viewalbum') {
	define('AJAX', 1);
	define('F_M',true);
	S::gp(array('aid'));
	$aid = (int)$aid;
	empty($aid) && Showmsg('data_error');
	require_once PrintEot('m_ajax_photos');
	ajax_footer();
} elseif ($a == 'createajax') {
	define('AJAX', 1);
	define('F_M',true);
	banUser();
	S::gp(array('job'));
	require_once PrintEot('m_ajax_photos');
	ajax_footer();
} elseif ($a == 'getallowflash') {
	define('AJAX', 1);
	define('F_M',true);
	S::gp(array('aid'));
	$aid = (int)$aid;
	if ($aid) {
		$photonums = $photoService->getAlbumInfo($aid);
		$o_maxphotonum && $photonums >= $o_maxphotonum && Showmsg('colony_photofull');
		if ($o_maxphotonum) {
			$allowmutinum = $o_maxphotonum - $photonums;
		} else {
			$allowmutinum = 'infinite';
		}
	}
	echo "ok\t$allowmutinum";
	ajax_footer();
}
require_once PrintEot('m_space_photos');
pwOutPut();
?>
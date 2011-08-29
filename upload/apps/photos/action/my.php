<?php
!defined('A_P') && exit('Forbidden');
$USCR = 'user_photo';

S::gp(array('ajax'));
if ($ajax == 1) define('AJAX', '1');

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
				'createajax',
				'friend',
				'setcover',
				'upload',
				'create',
				'editphoto'
			);
if(!in_array($a,$whiteList)){
	$a = 'own';
}

if($ifriend){
	$friendurl = '&ifriend=1';
}
if ($a == 'own') {
	list($count,$albumdb) = $photoService->getAlbumBrowseList();
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$pages = numofpage($count, $page,$pageCount, "{$basename}a=$a&");
} elseif ($a == 'friend') {
	list($count,$albumdb) = $photoService->getFriendAlbumsList();
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$friendurl = '&ifriend=1';
	$pages = numofpage($count, $page,$pageCount, "{$basename}a=$a&");
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_photos",true);

} elseif($a == 'albumcheck'){

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
		Cookie('albumview_'.$album['aid'], PwdCode($viewpwd), time()+24*3600);
		echo "success";
	} else {
		echo "fail";
	}
	ajax_footer();
	
}elseif ($a == 'album') {
	S::gp(array('aid'), null, 2);
	$cnpho = array();
	$result = $photoService->getPhotoListByAid($aid);	
	if(!is_array($result)){
		Showmsg($result);
	}
	// 删除积分提示
	require_once(R_P.'require/credit.php');
	$o_photos_creditset = unserialize($o_photos_creditset);
	$creditset = getCreditset($o_photos_creditset['Deletephoto'],false);
	$creditset = array_diff($creditset,array(0));
	foreach ($creditset as $key => $value) {
		$moneyName = $credit->cType[$key];
		$unit = $credit->cUnit[$key];
		$credit_pop .= $value.$unit.$moneyName.",";
	}
	$deletePhotoCredit = $creditset ? '删除照片会扣除积分：'.$credit_pop.'，继续吗？' : '是否确认删除?';
	
	list($album,$cnpho) = $result;
	$isown = $album['ownerid'] == $winduid ? '1' : '0';
	if (!$isown) {
		$url = $db_bbsurl."/apps.php?q=photos&uid=".$album['ownerid']."&a=album&aid=".$aid;
		ObHeader($url);
	}
	$count = $album['photonum'];
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$pages = numofpage($count, $page,$pageCount, "{$basename}a=$a&aid=$aid{$friendurl}&");
	$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	$myOuserData = $ouserdataService->get($album['ownerid']);
	$weiboPriv = false;
	(!$myOuserData['index_privacy'] && !$myOuserData['photos_privacy'] && !$album['private']) && $weiboPriv = true;
} elseif ($a == 'view') {
	S::gp(array('pid'));
	$result = $photoService->viewPhoto($pid);
	if(!is_array($result)){
		Showmsg($result);
	}
	// 删除积分提示
	require_once(R_P.'require/credit.php');
	$o_photos_creditset = unserialize($o_photos_creditset);
	$creditset = getCreditset($o_photos_creditset['Deletephoto'],false);
	$creditset = array_diff($creditset,array(0));
	foreach ($creditset as $key => $value) {
		$moneyName = $credit->cType[$key];
		$unit = $credit->cUnit[$key];
		$credit_pop .= $value.$unit.$moneyName.",";	
	}
	$deletePhotoCredit = $creditset ? '删除照片会扣除积分：'.$credit_pop.'继续吗？' : '是否确认删除?';
	
    list($photo,$nearphoto,$prePid,$nextPid) = $result;
	$isown = $photo['ownerid'] == $winduid ? '1' : '0';
	if (!$isown) {//转跳处理
		$url = $db_bbsurl."/apps.php?q=photos&a=view&pid=".$pid."&uid=".$photo['ownerid'];
		ObHeader($url);
	}
	$u = $photo['ownerid'];
	$username = $photo['owner'];
	$aid = $photo['aid'];
	$page = (int)S::getGP('page');
	$page < 1 && $page = 1;
	$url = $basename.'a=view&pid='.$pid;
	$url .= $ifriend == 1 ? '&ifriend='.$ifriend.'&' : '&';
	require_once(R_P.'require/bbscode.php');
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('photo',$pid,$page,$url);
	$comment_type = 'photo';
	$comment_typeid = $pid;

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
} elseif ($a == 'editphoto') {
	banUser();
	S::gp(array('pid','aid'));
	$photo = $photoService->getPhotoUnionInfo($pid);
	if (empty($photo) || (!$photoService->isPermission() && !$photoService->isSelf())) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		$options = '';
		$albumlist = $photoService->getAlbumList();
		foreach ($albumlist as $key => $value) {
			$options .= "<option value=\"$value[aid]\"" . (($value['aid'] == $photo['aid']) ? ' selected' : '') . ">$value[aname]</option>";
		}
	} else {
		require_once(R_P.'require/postfunc.php');
		S::gp(array('pintro'),'P');
		S::gp(array('aid'), null, 2);
		!$aid && Showmsg('colony_albumclass');
		if (strlen($pintro)>255) Showmsg('album_pintro_toolang');
		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($pintro)) !== false) {
			Showmsg('content_wordsfb');
		}
		$data = array('pintro' => $pintro);
		$photoService->updatePhoto($pid,$aid,$photo,$data);
		refreshto("{$basename}a=view&aid=$aid&pid=$pid",'operate_success');
	}
} elseif ($a == 'delphoto') {
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

} elseif ($a == 'setcover') {

	define('AJAX','1');
	S::gp(array('pid'), null, 2);
	$photo = $photoService->setCover($pid);
	if(empty($photo)){
		Showmsg('data_error');
	}
	echo 'ok'."\t".$photo['aid'];
	ajax_footer();

} elseif ($a == 'upload') {
	banUser();
	S::gp(array('aid'));
	S::gp(array('job'));
	if($job == 'flash') {
		if(!$photoService->getAlbumNumByUid()){
			$data = array(
						'aname'		=> getLangInfo('app','defaultalbum'),	
						'atype'		=> 0,
						'ownerid'	=> $uid,		
						'owner'		=> $windid,
						'lasttime'	=> $timestamp,	
						'crtime'	=> $timestamp,
						'isdefault'	=> 1,
					);	
			$photoService->createAlbum($data);
		}
	}
	$albumlist = $photoService->getAlbumList();
	if (empty($albumlist)) {
		$options = '<option value="">'.getLangInfo('app','defaultalbum').'</option>';
	}
	$albumlist && !$aid && $sort = array_pop($photoService->getSort($albumlist,'lasttime'));
	foreach($albumlist as $key => $value){
		$options .= "<option value=\"$value[aid]\"" . (($aid && $value['aid'] == $aid) ? ' selected' : ($sort && ($value['aid'] == $sort['aid'])) ? 'selected' : '') . ">$value[aname]</option>";
	}
	if ($aid > 0) {	
		$albumInfo = $photoService->getAlbumInfo($aid);
		$photonums = $albumInfo['photonum'];
		//if($o_maxphotonum && $photonums >= $o_maxphotonum) Showmsg('colony_photofull');
		$allowmutinum = $o_maxphotonum - $photonums;
	}
	if (empty($job)) {
		if (empty($_POST['step'])) {
			if(!$s) {
				list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_photos",true);
			} else {				
				list($isheader,$isfooter,$tplname,$isleft) = array(true,false,"m_photos",true);
			}
		} else {
			PostCheck(1,$o_photos_gdcheck,$o_photos_qcheck && $db_question);
			S::gp(array('pintro'),'P');
			empty($pintro) && $pintro = array();
			require_once(R_P.'require/bbscode.php');
			$wordsfb = L::loadClass('FilterUtil', 'filter');
			foreach ($pintro as $k => $v) {
				if (strlen($v)>255) {
					Showmsg('photo_pintro_toolong');
				}
				if (($banword = $wordsfb->comprise($v)) !== false) {
					Showmsg('content_wordsfb');
				}
			}
			$result = $photoService->uploadPhoto($aid);
			if(!is_array($result)){
				Showmsg($result);
			}
			list($albumInfo,$pid,$photoNum,$photos) = $result;
			countPosts("+$photoNum");
			$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */ 
			$weiboPrivacy = $weiboService->checkUserSpacePrivacy($winduid);
			if (!$albumInfo['private']) {
				if(!$weiboPrivacy['index'] && !$weiboPrivacy['photos']){
					$weiboPhotos = array();
					$tmpid = $pid;
					foreach ($photos as $value) {
						$value['pid'] = $tmpid;
						$tmpid++;
						$weiboPhotos[] = $value;
					}
					
					$objId = count($photos) > 1 ? 0 : $pid;
					$weiboExtra = array(
						'aid'=>$aid,
						'aname'=>$albumInfo['aname'],
						'photos'=> $weiboPhotos,
					);
					$weiboService->send($winduid, '分享照片', 'photos', $objId, $weiboExtra);
				}
				//会员资讯缓存
				$userCache = L::loadClass('Usercache', 'user');
				$userCache->delete($winduid, 'cardphoto');
				updateDatanalyse($pid,'picNew',$timestamp);
			}
			//积分变动
			require_once(R_P.'require/credit.php');
			$o_photos_creditset = unserialize($o_photos_creditset);
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
			refreshto("{$basename}a=view&aid=$aid&pid=$pid",'operate_success',2,true);
		}
	} 
} elseif ($a == 'create') {
	S::gp(array('step','tips','checkpwd'));
	banUser();
	// 用户组创建相册权限
	if ($groupid != 3 && $o_photos_groups && strpos($o_photos_groups,",$groupid,") === false) {
		createfail($checkpwd,'photos_group_right');
		Showmsg('photos_group_right');
	}
	require_once(R_P.'require/credit.php');
	$o_photos_creditset = unserialize($o_photos_creditset);
	$o_photos_creditset['Createalbum'] = @array_diff($o_photos_creditset['Createalbum'],array(0));
	foreach ($o_photos_creditset['Createalbum'] as $key => $value) {
		$moneyName = $credit->cType[$key];
		$unit = $credit->cUnit[$key];
		$credit_pop .= $value.$unit.$moneyName.",";
	}
	$createAlbumCredit = $o_photos_creditset['Createalbum'] ? '创建相册会扣除积分：'.$credit_pop.'继续吗？' : '是否确认创建?';
	$createAlbum = $o_photos_creditset['Createalbum'] ? $o_photos_creditset['Createalbum'] : '';
	if (empty($step)) {
		$rt = array();
		$select_0 = 'selected';
		list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_photos",true);	
	} else {
		require_once(R_P.'require/postfunc.php');
		PostCheck(1,$o_photos_gdcheck,$o_photos_qcheck && $db_question);
		S::gp(array('aname','aintro','pwd','repwd','private'));
		if(!$aname) {
			createfail($checkpwd,'colony_aname_empty');
			Showmsg('colony_aname_empty');
		}
		if (strlen($aname)>24) {
			createfail($checkpwd,'colony_aname_toolang');
			Showmsg('colony_aname_toolang');
		}
		if (strlen($aintro)>255) {
			createfail($checkpwd,'colony_aintro_toolang');
			Showmsg('colony_aintro_toolang');
		}
		$private = (int)$private;
		if ($private == 3 && !$pwd) {
			createfail($checkpwd,'photo_password_add');
			Showmsg('photo_password_add');
		}
		if ($private == 3 && $pwd) {
			if (strlen($pwd) < 3 || strlen($pwd) > 15) {
				createfail($checkpwd,'photo_password_minlimit');
				Showmsg('photo_password_minlimit');
			}
			$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%','?');
			if (str_replace($S_key,'',$pwd) != $pwd) {
				createfail($checkpwd,'illegal_password');
				Showmsg('illegal_password');
			}
			if ($pwd != $repwd) {
				createfail($checkpwd,'password_confirm');
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
		if ($o_albumnum2 > 0 && $o_albumnum2 <=$photoService->getAlbumNumByUid()) {
			createfail($checkpwd,$o_albumnum2,'limit_num');
			Showmsg('colony_album_num2');
		}

		if (!empty($o_photos_creditset['Createalbum'])) {
			foreach ($o_photos_creditset['Createalbum'] as $key => $value) {
				if ($value > 0) {
					$moneyname = $credit->cType[$key];
					if ($value > $credit->get($winduid,$key)) {
						createfail($checkpwd,'colony_moneylimit2');
						Showmsg('colony_moneylimit2');
					}
				}
			}
			//积分变动
			$creditset = getCreditset($o_photos_creditset['Createalbum'],false);
			$credit->sets($winduid,$creditset,true);
			updateMemberid($winduid);
		}
		if ($creditlog = unserialize($o_photos_creditlog)) {
			addLog($creditlog['Createalbum'],$windid,$winduid,'photos_Createalbum');
		}
		$data = array(
					'aname'		=> $aname,			'aintro'	=> $aintro,
					'atype'		=> 0,				'private'	=> $private,
					'ownerid'	=> $winduid,		'owner'		=> $windid,
					'lasttime'	=> $timestamp,		'crtime'	=> $timestamp,
					'albumpwd'	=> $pwd
				);
		$aid = $photoService->createAlbum($data);
		if ($checkpwd) {
			echo "success\t$aid";
			ajax_footer();
		}
		$url = "{$basename}a=own";
		$msg = defined('AJAX') ?  "success\t".$url : 'operate_success';
		refreshto("{$basename}a=own",$msg);
	}
} elseif ($a == 'delalbum') {
	define('AJAX', 1);
	define('F_M',true);
	S::gp(array('aid'), null, 2);
	$album = $photoService->getAlbumInfo($aid,array('ownerid','photonum'));
	if (empty($album) || ($album['ownerid'] != $winduid && !$photoService->isDelRight())) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {
		require_once PrintEot('m_ajax_photos');
		ajax_footer();

	} else {
		$photoService->delAlbum($aid);
		updateUserAppNum($album['ownerid'],'photo','minus',$album['photonum']);
		if($album['ownerid'] != $winduid){
			echo getLangInfo('msg','operate_success') . "\tjump\t{$basename}a=friend";
		} else {
			echo getLangInfo('msg','operate_success') . "\tjump\t{$basename}a=own";
		}
		ajax_footer();
	}
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
		$albumInfo = $photoService->getAlbumInfo($aid);
		$photonums = $albumInfo['photonum'];
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
if($s){ 
	require_once PrintEot('m_photos_bottom');
}else{
	require_once PrintEot('m_photos');
}
pwOutPut();
?>
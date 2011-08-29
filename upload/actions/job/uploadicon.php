<?php
!defined('P_W') && exit('Forbidden');

if (empty($_GET['step'])) {
	
	define('AJAX', 1);
	list($db_upload, $db_imglen, $db_imgwidth, $db_imgsize) = explode("\t", $db_upload);
	S::gp(array(
		'uid',
		'verify'
	));
	$swfhash = GetVerify($uid);
	checkVerify('swfhash');
	L::loadClass('faceupload', 'upload', false);
	$face = new FaceUpload($uid);
	PwUpload::upload($face);
	$uploaddb = $face->getAttachs();
	
	echo 'success';//ajax_footer();
	//echo $db_bbsurl . '/' . $attachpath . '/' . $uploaddb['fileuploadurl'] . '?' . $timestamp;
	//exit();

} else {

	L::loadClass('upload', '', false);
	$ext = strtolower(substr(strrchr($_GET['filename'], '.'), 1));
	$udir = str_pad(substr($winduid, -2), 2, '0', STR_PAD_LEFT);
	
	//$source = PwUpload::savePath(0, "{$winduid}_tmp.$ext", "upload/$udir/");
	if (!in_array(strtolower($ext), array(
		'gif',
		'jpg',
		'jpeg',
		'png',
		'bmp'
	))) {
		Showmsg('undefined_action');
	}
	/*if (!file_exists($source)) {
		Showmsg('头像保存失败，图片大小请不要超过2M!');
	}*/
	$data = $_SERVER['HTTP_RAW_POST_DATA'] ? $_SERVER['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
	
	if ($data) {
		S::gp(array('from'));
		require_once (R_P . 'require/showimg.php');
		
		$filename = "{$winduid}.$ext";
		//$normalDir = "upload/$udir/";
		$middleDir = "upload/middle/$udir/";
		$smallDir = "upload/small/$udir/";
		$img_w = $img_h = 0;
		
		$middleFile = PwUpload::savePath($db_ifftp, $filename, $middleDir);
		PwUpload::createFolder(dirname($middleFile));
		writeover($middleFile, $data);
		
		require_once (R_P . 'require/imgfunc.php');
		if (!$img_size = GetImgSize($middleFile)) {
			P_unlink($middleFile);
			Showmsg('upload_content_error');
		}
		
		/*$normalFile = PwUpload::savePath($db_ifftp, "{$winduid}.$ext", "$normalDir");
		PwUpload::createFolder(dirname($normalFile));
		list($w, $h) = explode("\t", $db_fthumbsize);
		if ($db_iffthumb && MakeThumb($source, $normalFile, $w, $h)) {
			P_unlink($source);
		} elseif (!PwUpload::movefile($source, $normalFile)) {
			Showmsg('undefined_action');
		}*/
		list($img_w, $img_h) = getimagesize($middleFile);
		$smallFile = PwUpload::savePath($db_ifftp, $filename, $smallDir);
		$s_ifthumb = 0;
		PwUpload::createFolder(dirname($smallFile));
		if ($ext == 'gif') {
			L::loadClass('gifdecoder', 'utility', false);
			L::loadClass('gif', 'utility', false);
			$gifDecoder = new GIFDecoder($data);
			$frames = $gifDecoder->GIFGetFrames();
			if (!empty($frames)) {
				foreach ($frames as $key => $value) {
					$frames[$key] = makeAvatarGifThumb($value, $img_w, $img_h, 48, 48);
				}
				$anime = new GIFEncoder($frames, $gifDecoder->GIFGetDelays(), $gifDecoder->GIFGetLoop(), $gifDecoder->GIFGetDisposal(), $gifDecoder->GIFGetTransparentR(), $gifDecoder->GIFGetTransparentG(), $gifDecoder->GIFGetTransparentB(), 'bin');
				$newGifData = $anime->getAnimation();
				PwUpload::createFolder(dirname($smallFile));
				pwCache::writeover($smallFile, $newGifData);
				$s_ifthumb = 1;
			}
		} elseif (MakeThumb($middleFile, $smallFile, 48, 48)) {
			$s_ifthumb = 1;
		}
		if ($db_ifftp) {
			//PwUpload::movetoftp($normalFile, $normalDir . "{$winduid}.$ext");
			PwUpload::movetoftp($middleFile, $middleDir . $filename);
			$s_ifthumb && PwUpload::movetoftp($smallFile, $smallDir . $filename);
		}
		pwFtpClose($GLOBALS['ftp']);
		
		$user_a = explode('|', $winddb['icon']);
		$user_a[2] = $img_w;
		$user_a[3] = $img_h;
		$usericon = setIcon("$udir/{$winduid}.$ext", 3, $user_a);
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array('icon'=>$usericon));
		//* $db->update("DELETE FROM pw_datastore WHERE skey=". S::sqlEscape("UID_".$winduid). " LIMIT 1");

		//job sign
		initJob($winduid, "doUpdateAvatar");
		if ($from != 'reg') refreshto('profile.php?action=modify&info_type=face', 'upload_icon_success');
		$jobService = L::loadclass('job', 'job');
		$jobs = $jobService->getJobByJobName('doUpdateAvatar');
		foreach ($jobs as $value) {
			if (!S::isArray($value)) continue;
			$jobService->jobGainController($winduid, $value['id']);
		}
		$verifyhash = GetVerify($winduid);
		refreshto("$db_registerfile?step=finish&option=2&verify=$verifyhash", 'upload_icon_success');
	
	} else {
		Showmsg('upload_icon_fail');
	}
}

<?php
!function_exists('readover') && exit('Forbidden');

if (empty($_GET['step'])) {

	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);
	S::gp(array('uid','verify'));
	$swfhash = GetVerify($uid);
	checkVerify('swfhash');
	$db_uploadfiletype = array();
	$db_uploadfiletype['gif'] = $db_uploadfiletype['jpg'] = $db_uploadfiletype['bmp'] = $db_uploadfiletype['png'] = $db_imgsize;

	L::loadClass('upload', '', false);
	$pwupload = new PwUpload(new FaceUpload());
	$pwupload->upload($uid);
	$uploaddb = $pwupload->getAttachs();

	echo $db_bbsurl.'/'.$attachpath.'/'.$uploaddb['fileuploadurl'].'?'.$timestamp;exit;

} else {
	require_once(R_P.'require/functions.php');
	L::loadClass('upload', '', false);
	$ext = strtolower(substr(strrchr($_GET['filename'],'.'),1));
	$udir = str_pad(substr($winduid,-2),2,'0',STR_PAD_LEFT);

	$source = PwUpload::savePath($db_ifftp, "{$winduid}_tmp.$ext", "upload/$udir/");
	if (!file_exists($source)) {
		Showmsg('undefined_action');
	}
	$data = $_SERVER['HTTP_RAW_POST_DATA'] ? $_SERVER['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');

	if ($data) {

		require_once(R_P . 'require/showimg.php');

		$filename  = "{$winduid}.jpg";
		$normalDir = "upload/$udir/";
		$middleDir = "upload/middle/$udir/";
		$smallDir  = "upload/small/$udir/";

		$middleFile = PwUpload::savePath($db_ifftp, $filename, "$middleDir");
		PwUpload::createFolder(dirname($middleFile));
		pwCache::writeover($middleFile, $data);

		require_once(R_P.'require/imgfunc.php');
		if (!$img_size = GetImgSize($middleFile,'jpg')) {
			P_unlink($middleFile);
			Showmsg('upload_content_error');
		}

		$normalFile = PwUpload::savePath($db_ifftp, "{$winduid}.$ext", "$normalDir");
		PwUpload::createFolder(dirname($normalFile));
		list($w, $h) = explode("\t", $db_fthumbsize);
		if ($db_iffthumb && MakeThumb($source, $normalFile, $w, $h)) {
			P_unlink($source);
		} elseif (!PwUpload::movefile($source, $normalFile)) {
			Showmsg('undefined_action');
		}

		$smallFile = PwUpload::savePath($db_ifftp, $filename, "$smallDir");
		$s_ifthumb = 0;
		PwUpload::createFolder(dirname($smallFile));
		if (MakeThumb($middleFile, $smallFile, 48, 48)) {
			$s_ifthumb = 1;
		}
		if ($db_ifftp) {
			PwUpload::movetoftp($normalFile, $normalDir . "{$winduid}.$ext");
			PwUpload::movetoftp($middleFile, $middleDir . $filename);
			$s_ifthumb && PwUpload::movetoftp($smallFile, $smallDir . $filename);
		}
		pwFtpClose($GLOBALS['ftp']);

		$user_a = explode('|',$winddb['icon']);
		$usericon = setIcon("$udir/{$winduid}.$ext", 3, $user_a);

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array('icon' => $usericon));

		refreshto('profile.php?info_type=face','upload_icon_success');

	} else {
		Showmsg('upload_icon_fail');
	}
}
?>